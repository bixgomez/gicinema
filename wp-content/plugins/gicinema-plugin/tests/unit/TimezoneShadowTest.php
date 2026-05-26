<?php
/**
 * Unit tests for gicinema__is_timezone_shadow() function.
 *
 * Tests the timezone-shadow duplicate detection that identifies screenings
 * offset by exactly 7 or 8 hours (Pacific timezone offset from UTC).
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class TimezoneShadowTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Mock WordPress timezone functions
        Functions\when('wp_timezone')->justReturn(new DateTimeZone('America/Los_Angeles'));

        // Load the function file
        require_once GICINEMA_PLUGIN_DIR . '/function__parse_screening_datetime.php';
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test detection of 7-hour offset (DST period)
     */
    public function test_detects_7_hour_offset() {
        $dt1 = '2026-06-15 19:30:00';
        $dt2 = '2026-06-16 02:30:00'; // +7 hours

        $this->assertTrue(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    public function test_detects_7_hour_offset_reverse() {
        $dt1 = '2026-06-16 02:30:00';
        $dt2 = '2026-06-15 19:30:00'; // -7 hours

        $this->assertTrue(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    /**
     * Test detection of 8-hour offset (Standard Time period)
     */
    public function test_detects_8_hour_offset() {
        $dt1 = '2026-01-15 19:30:00';
        $dt2 = '2026-01-16 03:30:00'; // +8 hours

        $this->assertTrue(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    public function test_detects_8_hour_offset_reverse() {
        $dt1 = '2026-01-16 03:30:00';
        $dt2 = '2026-01-15 19:30:00'; // -8 hours

        $this->assertTrue(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    /**
     * Test non-shadow differences are not detected
     */
    public function test_rejects_1_hour_difference() {
        $dt1 = '2026-06-15 19:30:00';
        $dt2 = '2026-06-15 20:30:00'; // +1 hour

        $this->assertFalse(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    public function test_rejects_6_hour_difference() {
        $dt1 = '2026-06-15 19:30:00';
        $dt2 = '2026-06-16 01:30:00'; // +6 hours

        $this->assertFalse(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    public function test_rejects_9_hour_difference() {
        $dt1 = '2026-06-15 19:30:00';
        $dt2 = '2026-06-16 04:30:00'; // +9 hours

        $this->assertFalse(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    public function test_rejects_same_datetime() {
        $dt1 = '2026-06-15 19:30:00';
        $dt2 = '2026-06-15 19:30:00';

        $this->assertFalse(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    /**
     * Test edge cases
     */
    public function test_handles_empty_datetime1() {
        $this->assertFalse(gicinema__is_timezone_shadow('', '2026-06-15 19:30:00'));
    }

    public function test_handles_empty_datetime2() {
        $this->assertFalse(gicinema__is_timezone_shadow('2026-06-15 19:30:00', ''));
    }

    public function test_handles_both_empty() {
        $this->assertFalse(gicinema__is_timezone_shadow('', ''));
    }

    public function test_handles_null_datetime1() {
        $this->assertFalse(gicinema__is_timezone_shadow(null, '2026-06-15 19:30:00'));
    }

    public function test_handles_null_datetime2() {
        $this->assertFalse(gicinema__is_timezone_shadow('2026-06-15 19:30:00', null));
    }

    public function test_handles_invalid_datetime() {
        $this->assertFalse(gicinema__is_timezone_shadow('invalid', '2026-06-15 19:30:00'));
    }

    /**
     * Test across day boundaries
     */
    public function test_detects_shadow_across_midnight() {
        $dt1 = '2026-06-15 23:30:00';
        $dt2 = '2026-06-16 06:30:00'; // +7 hours, crosses midnight

        $this->assertTrue(gicinema__is_timezone_shadow($dt1, $dt2));
    }

    public function test_detects_shadow_across_month_boundary() {
        $dt1 = '2026-06-30 23:30:00';
        $dt2 = '2026-07-01 07:30:00'; // +8 hours, crosses month

        $this->assertTrue(gicinema__is_timezone_shadow($dt1, $dt2));
    }
}
