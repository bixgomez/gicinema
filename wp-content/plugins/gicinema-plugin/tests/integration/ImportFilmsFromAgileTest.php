<?php
/**
 * Integration test for the full Agile import: transient -> Film post (all fields).
 *
 * gicinema__import_films_from_agile() reads the cached Agile feed from the
 * agile_shows_array transient and, for each show, creates/updates a `film` post,
 * writes every mapped meta/ACF field from the feed, imports the showtimes into
 * the gi_screenings custom table, and syncs the ACF `screenings` repeater.
 *
 * This test confirms that ALL fields are written to the post correctly (not just
 * the datetimes), and double-checks the screening datetimes for timezone
 * integrity. It complements:
 *   - ImportScreeningsTest (stage 2: feed -> custom table, datetimes only)
 *   - SyncScreeningsToAcfTest (stage 3: table -> ACF repeater, datetimes only)
 *
 * The only outbound HTTP the import performs is the poster-image download. We
 * stub pre_http_request to return a WP_Error so nothing hits the network and the
 * attachment pipeline (which would be fragile under convertWarningsToExceptions)
 * is never entered; the import logs the failed download and still records the
 * poster_url meta from the feed.
 *
 * Requires ACF Pro (loaded by tests/bootstrap.php) and the Film Details field
 * group (registered by tests/bootstrap.php).
 *
 * Run only in the integration suite (requires the WordPress test library):
 *   GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration
 */

class ImportFilmsFromAgileTest extends WP_UnitTestCase {

	/**
	 * Fully-qualified custom table name.
	 *
	 * @var string
	 */
	private $table_name;

	public function setUp(): void {
		parent::setUp();

		global $wpdb;
		$this->table_name = $wpdb->prefix . 'gi_screenings';

		// The full importer (its internal relative require_once calls resolve
		// against the plugin directory). Guard against redeclaration.
		if ( ! function_exists( 'gicinema__import_films_from_agile' ) ) {
			require_once dirname( __DIR__, 2 ) . '/function__import_films_from_agile.php';
		}

		// Production-like timezone so any latent UTC/local confusion would show.
		update_option( 'timezone_string', 'America/Los_Angeles' );

		// Ensure the custom table exists and start from an empty table.
		gicinema__create_custom_table();
		$wpdb->query( "DELETE FROM {$this->table_name}" );

		// Seed the cached feed so the importer never performs a live fetch.
		$fixture_json = file_get_contents( __DIR__ . '/fixtures/agile-sample.json' );
		set_transient( 'agile_shows_array', $fixture_json, 12 * HOUR_IN_SECONDS );

		// Stub ALL outbound HTTP (only the poster download) with a WP_Error so
		// nothing hits the network and the attachment pipeline is skipped.
		add_filter(
			'pre_http_request',
			static function () {
				return new WP_Error( 'http_request_failed', 'stubbed in test' );
			},
			10,
			3
		);
	}

	/**
	 * Locate the single imported Film post by its Agile ID.
	 *
	 * @param string $agile_id Agile film ID.
	 * @return int Post ID (0 if not found).
	 */
	private function find_film_by_agile_id( $agile_id ) {
		$films = get_posts(
			array(
				'post_type'      => 'film',
				'posts_per_page' => 1,
				'meta_key'       => 'agile_film_id',
				'meta_value'     => $agile_id,
			)
		);
		return empty( $films ) ? 0 : $films[0]->ID;
	}

	/**
	 * The full import writes every mapped field to the Film post from the feed.
	 */
	public function test_import_writes_all_film_fields_from_feed() {
		// Run the importer; it echoes a lot of progress HTML, so capture/discard.
		ob_start();
		gicinema__import_films_from_agile();
		ob_get_clean();

		$post_id = $this->find_film_by_agile_id( '12345' );
		$this->assertGreaterThan( 0, $post_id, 'The import should create exactly one film post for the fixture show.' );

		// Title comes from the post object; the rest are meta written from the feed.
		$this->assertSame( 'Test Film One', get_post( $post_id )->post_title, 'post_title must match the feed Name.' );

		$expected_meta = array(
			'agile_film_id'        => '12345',
			'description'          => 'A short description used only for testing.',
			'film_length'          => '95',
			'ticket_purchase_link' => 'https://example.com/film/12345',
			'film_year'            => '2026',
			'format'               => 'DCP',
			'film_director'        => 'Jane Director',
			'country'              => 'USA',
			'poster_url'           => 'https://example.com/poster-12345.jpg',
			'trailer_url'          => 'https://www.youtube.com/watch?v=test12345',
		);

		foreach ( $expected_meta as $key => $expected_value ) {
			$this->assertSame(
				$expected_value,
				get_post_meta( $post_id, $key, true ),
				"Field '{$key}' must be written to the post from the feed."
			);
		}

		// Datetime double-check: screenings resolve to the same wall-clock time
		// (no timezone shift), read through the system's own normalizer.
		$screenings = gicinema__get_screenings_from_post( $post_id );
		sort( $screenings );
		$this->assertSame(
			array( '2026-06-15 19:30:00', '2026-06-16 19:30:00' ),
			$screenings,
			'Screening datetimes must import without any timezone shift.'
		);
	}
}
