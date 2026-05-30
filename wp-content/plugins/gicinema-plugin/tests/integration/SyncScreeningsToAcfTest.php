<?php
/**
 * Integration test for Stage 3 of the pipeline: custom table -> Film post (ACF).
 *
 * During an Agile import the screening rows are written to the gi_screenings
 * custom table and then mirrored into the Film post's ACF `screenings` repeater
 * by gicinema__sync_screenings(). This test isolates that step: it seeds the
 * custom table with known screening datetimes, runs the sync, and asserts the
 * ACF repeater ends up resolving to exactly the same datetimes (no timezone
 * shift).
 *
 * The `screening` sub-field is an ACF date_time_picker whose return_format is
 * "m/d/Y g:i a", so reading it raw would return display format. "Unchanged" is
 * therefore asserted through the plugin's own reader
 * gicinema__get_screenings_from_post(), which normalizes back to canonical
 * "Y-m-d H:i:s" exactly as the rest of the system does.
 *
 * Requires ACF Pro (loaded by tests/bootstrap.php) and the Film Details field
 * group (registered by tests/bootstrap.php) so the `screenings` repeater works.
 *
 * Run only in the integration suite (requires the WordPress test library):
 *   GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration
 */

class SyncScreeningsToAcfTest extends WP_UnitTestCase {

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

		// The sync + reader helpers (idempotent require_once).
		require_once dirname( __DIR__, 2 ) . '/function__sync_screenings.php';

		// Production-like timezone so any latent UTC/local confusion would show.
		update_option( 'timezone_string', 'America/Los_Angeles' );

		// Ensure the custom table exists and start from an empty table.
		gicinema__create_custom_table();
		$wpdb->query( "DELETE FROM {$this->table_name}" );
	}

	/**
	 * Insert an active screening row into the custom table.
	 *
	 * @param int    $post_id   Film post ID.
	 * @param int    $film_id   Agile film ID.
	 * @param string $screening Canonical "Y-m-d H:i:s" datetime string.
	 */
	private function insert_table_row( $post_id, $film_id, $screening ) {
		global $wpdb;
		$parts = explode( ' ', $screening );
		$wpdb->insert(
			$this->table_name,
			array(
				'post_id'        => $post_id,
				'film_id'        => $film_id,
				'screening'      => $screening,
				'screening_date' => isset( $parts[0] ) ? $parts[0] : '',
				'screening_time' => isset( $parts[1] ) ? $parts[1] : '',
				'status'         => 1,
			),
			array( '%d', '%d', '%s', '%s', '%s', '%d' )
		);
	}

	/**
	 * Stage 3: custom-table screenings are written to the ACF repeater unchanged.
	 */
	public function test_table_screenings_sync_into_acf_without_datetime_change() {
		$expected = array( '2026-06-15 19:30:00', '2026-06-16 19:30:00' );

		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'film',
				'post_title'  => 'Test Film',
				'post_status' => 'publish',
			)
		);
		$agile_id = 12345;
		update_field( 'agile_film_id', $agile_id, $post_id );

		// Seed the custom table with known datetimes; ACF starts empty.
		foreach ( $expected as $screening ) {
			$this->insert_table_row( $post_id, $agile_id, $screening );
		}

		// Run the sync (commit, not dry run; suppress echoed admin output).
		gicinema__sync_screenings( $post_id, false, false );

		// The ACF repeater, read through the same normalizer the system uses,
		// must now resolve to exactly the seeded datetimes.
		$acf_normalized = gicinema__get_screenings_from_post( $post_id );
		sort( $acf_normalized );

		$this->assertSame(
			$expected,
			$acf_normalized,
			'Screening datetimes must move from the custom table into the ACF repeater without any change (no timezone shift).'
		);
	}
}
