<?php
/**
 * Integration tests for gicinema__update_agile_shows_array() (Flow 1).
 *
 * Flow 1 of the Agile import: fetch the feed over HTTP and cache it in the
 * agile_shows_array transient.  These tests boot a real (throwaway) WordPress
 * via the test suite, but they intercept the outbound HTTP request so nothing
 * ever contacts Agile's live server.
 *
 * Run only in the integration suite (requires the WordPress test library):
 *   GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration
 */

class UpdateAgileShowsArrayTest extends WP_UnitTestCase {

	/**
	 * The fake Agile JSON body our stubbed HTTP call returns.
	 *
	 * @var string
	 */
	private $fixture_json;

	/**
	 * The currently registered HTTP stub callback, if any.
	 *
	 * @var callable|null
	 */
	private $http_stub_callback;

	public function setUp(): void {
		parent::setUp();

		// Load the function under test.  ABSPATH is defined because WordPress
		// is loaded, so the file's security guard passes.
		require_once dirname( __DIR__, 2 ) . '/function__update_agile_shows_array.php';

		// Read the sample feed we will pretend Agile returned.
		$this->fixture_json = file_get_contents( __DIR__ . '/fixtures/agile-sample.json' );

		// Start each test from a clean cache.
		delete_transient( 'agile_shows_array' );
		delete_option( '_transient_timeout_agile_shows_array' );
		delete_option( 'gicinema_agile_shows_array_updated' );
		delete_option( 'gicinema_agile_shows_array_ttl' );
		delete_option( 'gicinema_update_feed_log' );
	}

	public function tearDown(): void {
		if ( $this->http_stub_callback ) {
			remove_filter( 'pre_http_request', $this->http_stub_callback, 10 );
			$this->http_stub_callback = null;
		}

		delete_transient( 'agile_shows_array' );
		delete_option( '_transient_timeout_agile_shows_array' );
		delete_option( 'gicinema_agile_shows_array_updated' );
		delete_option( 'gicinema_agile_shows_array_ttl' );
		delete_option( 'gicinema_update_feed_log' );

		parent::tearDown();
	}

	/**
	 * Build a WordPress HTTP API response array.
	 *
	 * @param int    $code Response code.
	 * @param string $body Response body.
	 * @return array
	 */
	private function http_response( $code, $body ) {
		return array(
			'headers'  => array( 'content-type' => 'application/json' ),
			'body'     => $body,
			'response' => array(
				'code'    => $code,
				'message' => 200 === $code ? 'OK' : 'Error',
			),
		);
	}

	/**
	 * Build a WordPress HTTP API error object.
	 *
	 * @return WP_Error
	 */
	private function http_error() {
		return new WP_Error( 'http_request_failed', 'stubbed network failure' );
	}

	/**
	 * Replace outbound HTTP requests with a sequence of canned responses.
	 *
	 * @param array $responses Response arrays or WP_Error objects.
	 */
	private function stub_http_sequence( array $responses ) {
		$queue = array_values( $responses );

		$this->http_stub_callback = function () use ( &$queue ) {
			if ( empty( $queue ) ) {
				return new WP_Error( 'unexpected_http_request', 'Unexpected HTTP request in test.' );
			}

			return array_shift( $queue );
		};

		add_filter(
			'pre_http_request',
			$this->http_stub_callback,
			10,
			3
		);
	}

	/**
	 * Replace any outbound HTTP request with a canned successful response.
	 *
	 * @param string $body The response body to return.
	 */
	private function stub_http_success( $body ) {
		$this->stub_http_sequence(
			array(
				$this->http_response( 200, $body ),
			)
		);
	}

	/**
	 * Run the updater while capturing its echoed admin output.
	 *
	 * @return string Captured output.
	 */
	private function run_update() {
		ob_start();
		try {
			gicinema__update_agile_shows_array();
		} finally {
			$output = ob_get_clean();
		}

		return $output;
	}

	/**
	 * Return the most recent update-feed log entry.
	 *
	 * @return array
	 */
	private function last_log_entry() {
		$log = get_option( 'gicinema_update_feed_log' );
		$this->assertIsArray( $log, 'Update feed log should be an array.' );
		$this->assertNotEmpty( $log, 'Update feed log should have at least one entry.' );
		return $log[ count( $log ) - 1 ];
	}

	/**
	 * Point 1: a valid feed gets stored in the transient.
	 */
	public function test_valid_feed_is_stored_in_transient() {
		$this->stub_http_success( $this->fixture_json );

		$this->run_update();

		$this->assertSame(
			$this->fixture_json,
			get_transient( 'agile_shows_array' ),
			'The fetched feed JSON should be stored verbatim in the agile_shows_array transient.'
		);
	}

	public function test_success_records_bookkeeping_and_log_entry() {
		$this->stub_http_success( $this->fixture_json );

		$before = time();
		$this->run_update();
		$after = time();

		$ttl = 12 * HOUR_IN_SECONDS;

		$this->assertSame(
			$ttl,
			(int) get_option( 'gicinema_agile_shows_array_ttl' ),
			'Successful fetch should store the expected 12-hour TTL value.'
		);

		$updated = (int) get_option( 'gicinema_agile_shows_array_updated' );
		$this->assertGreaterThanOrEqual( $before, $updated, 'Updated timestamp should be no earlier than the test start.' );
		$this->assertLessThanOrEqual( $after, $updated, 'Updated timestamp should be no later than the test end.' );

		$timeout = (int) get_option( '_transient_timeout_agile_shows_array' );
		$this->assertGreaterThanOrEqual( $before + $ttl, $timeout, 'Transient timeout should be at least 12 hours from test start.' );
		$this->assertLessThanOrEqual( $after + $ttl, $timeout, 'Transient timeout should be no more than 12 hours from test end.' );

		$entry = $this->last_log_entry();
		$this->assertSame( true, $entry['success'], 'Success log entry should mark the fetch successful.' );
		$this->assertSame( false, $entry['retried'], 'First-attempt success should not be marked as retried.' );
		$this->assertSame( 200, $entry['code'], 'Success log entry should record HTTP 200.' );
		$this->assertSame( strlen( $this->fixture_json ), $entry['bytes'], 'Success log entry should record the response byte count.' );
	}

	public function test_retry_success_stores_feed_and_logs_retry() {
		$this->stub_http_sequence(
			array(
				$this->http_response( 503, 'temporarily unavailable' ),
				$this->http_response( 200, $this->fixture_json ),
			)
		);

		$this->run_update();

		$this->assertSame(
			$this->fixture_json,
			get_transient( 'agile_shows_array' ),
			'Retry success should store the retry response body in the transient.'
		);

		$entry = $this->last_log_entry();
		$this->assertSame( true, $entry['success'], 'Retry success should be logged as successful.' );
		$this->assertSame( true, $entry['retried'], 'Retry success should be marked as retried.' );
		$this->assertSame( 200, $entry['code'], 'Retry success should log the retry response code.' );
	}

	public function test_double_bad_response_deletes_stale_transient() {
		set_transient( 'agile_shows_array', 'stale-feed', 12 * HOUR_IN_SECONDS );

		$this->stub_http_sequence(
			array(
				$this->http_response( 503, 'temporarily unavailable' ),
				$this->http_response( 503, 'still unavailable' ),
			)
		);

		$this->run_update();

		$this->assertFalse(
			get_transient( 'agile_shows_array' ),
			'Two non-error bad responses should delete the stale Agile feed transient.'
		);

		$entry = $this->last_log_entry();
		$this->assertSame( false, $entry['success'], 'Double-bad response should be logged as failed.' );
		$this->assertSame( true, $entry['retried'], 'Double-bad response should be marked as retried.' );
		$this->assertSame( 503, $entry['code'], 'Failure log should record the retry response code.' );
	}

	public function test_first_network_error_preserves_stale_transient() {
		set_transient( 'agile_shows_array', 'stale-feed', 12 * HOUR_IN_SECONDS );

		$this->stub_http_sequence(
			array(
				$this->http_error(),
			)
		);

		$this->run_update();

		$this->assertSame(
			'stale-feed',
			get_transient( 'agile_shows_array' ),
			'First-attempt WP_Error should preserve the stale Agile feed transient.'
		);

		$entry = $this->last_log_entry();
		$this->assertSame( false, $entry['success'], 'Network error should be logged as failed.' );
		$this->assertSame( false, $entry['retried'], 'First-attempt WP_Error should not be marked as retried.' );
		$this->assertSame( 0, $entry['code'], 'Network error should log response code 0.' );
	}

	public function test_retry_network_error_preserves_stale_transient() {
		set_transient( 'agile_shows_array', 'stale-feed', 12 * HOUR_IN_SECONDS );

		$this->stub_http_sequence(
			array(
				$this->http_response( 503, 'temporarily unavailable' ),
				$this->http_error(),
			)
		);

		$this->run_update();

		$this->assertSame(
			'stale-feed',
			get_transient( 'agile_shows_array' ),
			'Retry WP_Error should preserve the stale Agile feed transient.'
		);

		$entry = $this->last_log_entry();
		$this->assertSame( false, $entry['success'], 'Retry WP_Error should be logged as failed.' );
		$this->assertSame( true, $entry['retried'], 'Retry WP_Error should be marked as retried.' );
		$this->assertSame( 0, $entry['code'], 'Retry WP_Error should log response code 0.' );
	}
}
