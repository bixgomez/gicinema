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

	public function setUp(): void {
		parent::setUp();

		// Load the function under test.  ABSPATH is defined because WordPress
		// is loaded, so the file's security guard passes.
		require_once dirname( __DIR__, 2 ) . '/function__update_agile_shows_array.php';

		// Read the sample feed we will pretend Agile returned.
		$this->fixture_json = file_get_contents( __DIR__ . '/fixtures/agile-sample.json' );

		// Start each test from a clean cache.
		delete_transient( 'agile_shows_array' );
	}

	/**
	 * Replace any outbound HTTP request with a canned successful response, so
	 * the test never touches Agile's live server.
	 *
	 * @param string $body The response body to return.
	 */
	private function stub_http_success( $body ) {
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( $body ) {
				return array(
					'headers'  => array( 'content-type' => 'application/json' ),
					'body'     => $body,
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
				);
			},
			10,
			3
		);
	}

	/**
	 * Point 1: a valid feed gets stored in the transient.
	 */
	public function test_valid_feed_is_stored_in_transient() {
		$this->stub_http_success( $this->fixture_json );

		// The function echoes progress HTML; capture and discard it so it does
		// not clutter the test output.
		ob_start();
		gicinema__update_agile_shows_array();
		ob_get_clean();

		$this->assertSame(
			$this->fixture_json,
			get_transient( 'agile_shows_array' ),
			'The fetched feed JSON should be stored verbatim in the agile_shows_array transient.'
		);
	}
}
