<?php
/**
 * Integration test for Stage 2 of the pipeline: Agile feed -> custom table.
 *
 * Verifies that screening datetimes survive the conversion from the Agile feed
 * (CurrentShowings[].StartDate) into the gi_screenings custom table WITHOUT any
 * timezone shift. The strict parser normalizes "2026-06-15T19:30:00" (ISO with
 * no timezone, the Agile format) to "2026-06-15 19:30:00", preserving the
 * wall-clock time. This test asserts the stored value is exactly that, plus the
 * derived date and time parts.
 *
 * Stage 2 is isolated here by calling gicinema__import_screenings_from_agile()
 * directly (feed showtimes -> table). The fuller import path
 * (gicinema__import_films_from_agile) also writes ACF and downloads posters;
 * those legs are covered by later stage 3/4 tests.
 *
 * Run only in the integration suite (requires the WordPress test library):
 *   GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration
 */

class ImportScreeningsTest extends WP_UnitTestCase {

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

		// The function under test. require_once is idempotent, so this is safe
		// even if the plugin already loaded the file.
		require_once dirname( __DIR__, 2 ) . '/function__import_screenings_from_agile.php';

		// Run the pipeline under a production-like timezone (Pacific). For
		// ISO-without-timezone input the parser preserves the wall clock, so the
		// expected strings below hold regardless, but this keeps the environment
		// faithful and consistent with later stage 3/4 tests.
		update_option( 'timezone_string', 'America/Los_Angeles' );

		// Ensure the custom table exists, then start each test from an empty table.
		gicinema__create_custom_table();
		$wpdb->query( "DELETE FROM {$this->table_name}" );
	}

	/**
	 * Read the CurrentShowings list from the bundled Agile fixture.
	 *
	 * @return array List of showing arrays, each with a StartDate.
	 */
	private function fixture_showings() {
		$json    = file_get_contents( __DIR__ . '/fixtures/agile-sample.json' );
		$decoded = json_decode( $json, true );
		return $decoded['ArrayOfShows'][0]['CurrentShowings'];
	}

	/**
	 * Stage 2: Agile showtimes land in gi_screenings with no datetime change.
	 */
	public function test_agile_showtimes_import_into_table_without_datetime_change() {
		global $wpdb;

		$showings = $this->fixture_showings();
		$post_id  = 999;
		$agile_id = 12345;

		// The function echoes progress HTML; capture and discard it so it does
		// not clutter the test output.
		ob_start();
		gicinema__import_screenings_from_agile(
			$showings,
			'field_screenings',
			'screenings',
			'screening',
			$post_id,
			$agile_id
		);
		ob_get_clean();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT screening, screening_date, screening_time
				   FROM {$this->table_name}
				  WHERE post_id = %d
				  ORDER BY screening ASC",
				$post_id
			),
			ARRAY_A
		);

		$this->assertCount(
			2,
			$rows,
			'Both fixture showtimes should produce exactly one table row each.'
		);

		// First showtime: 2026-06-15T19:30:00 -> 2026-06-15 19:30:00 (unchanged wall clock).
		$this->assertSame( '2026-06-15 19:30:00', $rows[0]['screening'], 'First screening datetime must be stored verbatim (no timezone shift).' );
		$this->assertSame( '2026-06-15', $rows[0]['screening_date'], 'First screening date part must match.' );
		$this->assertSame( '19:30:00', $rows[0]['screening_time'], 'First screening time part must match.' );

		// Second showtime: 2026-06-16T19:30:00 -> 2026-06-16 19:30:00.
		$this->assertSame( '2026-06-16 19:30:00', $rows[1]['screening'], 'Second screening datetime must be stored verbatim (no timezone shift).' );
		$this->assertSame( '2026-06-16', $rows[1]['screening_date'], 'Second screening date part must match.' );
		$this->assertSame( '19:30:00', $rows[1]['screening_time'], 'Second screening time part must match.' );
	}
}
