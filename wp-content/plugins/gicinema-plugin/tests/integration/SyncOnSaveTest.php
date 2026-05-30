<?php
/**
 * Integration test for Stage 4 of the pipeline: editing/saving a Film post.
 *
 * When a Film post is saved in the editor, gicinema__update_film_on_save() reads
 * the ACF `screenings` repeater (a date_time_picker whose return_format is
 * "m/d/Y g:i a"), and hands it to gicinema__sync_screenings_on_save(), which
 * normalizes each value through the strict datetime parser and mirrors the
 * result into the gi_screenings custom table.
 *
 * Property under test: editing/saving a Film post does NOT change the screening
 * dates/times (no timezone shift, no drift). Because ACF returns the
 * date_time_picker value in display format, "unchanged" is asserted through the
 * plugin's own reader gicinema__get_screenings_from_post(), which normalizes
 * back to canonical "Y-m-d H:i:s" exactly as the rest of the system does.
 *
 * Requires ACF Pro (loaded by tests/bootstrap.php) and the Film Details field
 * group (registered by tests/bootstrap.php) so the `screenings` repeater works.
 *
 * Run only in the integration suite (requires the WordPress test library):
 *   GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration
 */

class SyncOnSaveTest extends WP_UnitTestCase {

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

		// The save path, plus the reader helpers used for assertions.
		require_once dirname( __DIR__, 2 ) . '/function__update_film_on_save.php';
		require_once dirname( __DIR__, 2 ) . '/function__sync_screenings.php';

		// Production-like timezone so any latent UTC/local confusion would show.
		update_option( 'timezone_string', 'America/Los_Angeles' );

		// Ensure the custom table exists and start from an empty table.
		gicinema__create_custom_table();
		$wpdb->query( "DELETE FROM {$this->table_name}" );

		// NOTE: $_POST['acf'] is deliberately NOT set here. It is the guard that
		// lets gicinema__update_film_on_save() proceed, and that function also
		// runs on the save_post hook. If it were set during the factory post
		// creation below, the hook would fire on a screening-less post and
		// foreach(null) would error inside the sync. We set it in the test only
		// after the film and its screenings exist, right before the save we are
		// actually exercising.
	}

	public function tearDown(): void {
		unset( $_POST['acf'] );
		parent::tearDown();
	}

	/**
	 * Create a published Film post with the given ACF screening datetimes.
	 *
	 * @param int      $agile_id   Agile film ID to store in ACF.
	 * @param string[] $screenings Canonical "Y-m-d H:i:s" datetime strings.
	 * @return int The new post ID.
	 */
	private function make_film_with_screenings( $agile_id, array $screenings ) {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'film',
				'post_title'  => 'Test Film',
				'post_status' => 'publish',
			)
		);

		update_field( 'agile_film_id', $agile_id, $post_id );

		$rows = array();
		foreach ( $screenings as $value ) {
			$rows[] = array(
				'screening' => $value,
				'status'    => 1,
			);
		}
		update_field( 'screenings', $rows, $post_id );

		return $post_id;
	}

	/**
	 * Return the active screening strings stored in the custom table for a post.
	 *
	 * @param int $post_id Film post ID.
	 * @return string[] Screening datetime strings, ascending.
	 */
	private function table_screenings( $post_id ) {
		global $wpdb;
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT screening FROM {$this->table_name}
				  WHERE post_id = %d AND status = 1
				  ORDER BY screening ASC",
				$post_id
			)
		);
	}

	/**
	 * Stage 4: saving a Film post preserves the screening datetimes.
	 *
	 * The custom table (written by the save) must hold the same canonical
	 * datetimes, and the ACF field (read back through the plugin's normalizer)
	 * must still resolve to the same canonical datetimes.
	 */
	public function test_saving_film_preserves_screening_datetimes() {
		$expected = array( '2026-06-15 19:30:00', '2026-06-16 19:30:00' );

		$post_id = $this->make_film_with_screenings( 12345, $expected );

		// Now that the film and its screenings exist, set the save guard and
		// simulate the editor save. The function reads values via get_field(),
		// not from $_POST, so a placeholder array is enough to pass the guard.
		$_POST['acf'] = array( 'placeholder' => 1 );

		// Simulate the editor save (the function echoes nothing, but guard anyway).
		ob_start();
		gicinema__update_film_on_save( $post_id );
		ob_get_clean();

		// The save mirrors the screenings into the custom table, unchanged.
		$this->assertSame(
			$expected,
			$this->table_screenings( $post_id ),
			'Saving a Film post must mirror the screening datetimes into the custom table without any change (no timezone shift).'
		);

		// The ACF field, read through the same normalizer the rest of the system
		// uses, must still resolve to the same canonical datetimes.
		$acf_normalized = gicinema__get_screenings_from_post( $post_id );
		sort( $acf_normalized );
		$this->assertSame(
			$expected,
			$acf_normalized,
			'The ACF screenings, normalized as the system reads them, must be unchanged by the save.'
		);
	}
}
