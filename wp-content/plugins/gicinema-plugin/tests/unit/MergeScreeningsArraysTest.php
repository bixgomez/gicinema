<?php
/**
 * Unit tests for gicinema__merge_screenings_arrays().
 *
 * These tests cover the production merge path used by gicinema__sync_screenings(),
 * including timezone-shadow duplicate filtering.
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class MergeScreeningsArraysTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        Functions\when('wp_timezone')->justReturn(new DateTimeZone('America/Los_Angeles'));

        require_once GICINEMA_PLUGIN_DIR . '/function__parse_screening_datetime.php';
        require_once GICINEMA_PLUGIN_DIR . '/function__sync_screenings.php';
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_keeps_exact_matches_without_duplicates() {
        $acf_screenings = array('2026-06-15 19:30:00');
        $table_screenings = array('2026-06-15 19:30:00');

        $this->assertSame(
            array('2026-06-15 19:30:00'),
            gicinema__merge_screenings_arrays($acf_screenings, $table_screenings)
        );
    }

    public function test_keeps_acf_only_non_shadow_screenings() {
        $acf_screenings = array('2026-06-15 20:30:00');
        $table_screenings = array('2026-06-15 19:30:00');

        $this->assertSame(
            array('2026-06-15 19:30:00', '2026-06-15 20:30:00'),
            gicinema__merge_screenings_arrays($acf_screenings, $table_screenings)
        );
    }

    public function test_skips_acf_7_hour_timezone_shadow_of_table_screening() {
        $acf_screenings = array('2026-06-16 02:30:00');
        $table_screenings = array('2026-06-15 19:30:00');

        $this->assertSame(
            array('2026-06-15 19:30:00'),
            gicinema__merge_screenings_arrays($acf_screenings, $table_screenings)
        );
    }

    public function test_skips_acf_8_hour_timezone_shadow_of_table_screening() {
        $acf_screenings = array('2026-01-16 03:30:00');
        $table_screenings = array('2026-01-15 19:30:00');

        $this->assertSame(
            array('2026-01-15 19:30:00'),
            gicinema__merge_screenings_arrays($acf_screenings, $table_screenings)
        );
    }

    public function test_filter_can_disable_timezone_shadow_guard() {
        Functions\when('apply_filters')->alias(
            function ($hook, $value) {
                if ($hook === 'gicinema_enable_tz_shadow_guard') {
                    return false;
                }

                return $value;
            }
        );

        $acf_screenings = array('2026-06-16 02:30:00');
        $table_screenings = array('2026-06-15 19:30:00');

        $this->assertSame(
            array('2026-06-15 19:30:00', '2026-06-16 02:30:00'),
            gicinema__merge_screenings_arrays($acf_screenings, $table_screenings)
        );
    }
}
