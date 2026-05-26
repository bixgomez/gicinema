<?php
/**
 * Unit tests for gicinema__parse_screening_datetime() function.
 *
 * Tests the strict datetime parser that ensures all datetime values are
 * normalized to Y-m-d H:i:s format in the WordPress timezone.
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class ParseScreeningDatetimeTest extends TestCase {

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
     * Test Pattern 1: Already normalized (Y-m-d H:i:s)
     */
    public function test_already_normalized_format() {
        $input = '2026-06-15 19:30:00';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 19:30:00', $result);
    }

    /**
     * Test Pattern 2: ISO 8601 with UTC marker (Z)
     */
    public function test_iso8601_with_utc_marker() {
        // 19:30 UTC = 12:30 PDT (UTC-7 during DST)
        $input = '2026-06-15T19:30:00Z';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 12:30:00', $result);
    }

    /**
     * Test Pattern 3: ISO 8601 with timezone offset
     */
    public function test_iso8601_with_timezone_offset() {
        // 19:30-07:00 (explicit PDT) should stay 19:30 in Pacific
        $input = '2026-06-15T19:30:00-07:00';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 19:30:00', $result);
    }

    /**
     * Test Pattern 4: ISO 8601 without timezone (Agile's current format)
     * This is the most common pattern from Agile API
     */
    public function test_iso8601_without_timezone() {
        $input = '2026-06-15T19:30:00';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 19:30:00', $result);
    }

    /**
     * Test Pattern 5: ACF display format (m/d/Y g:i a)
     */
    public function test_acf_display_format_pm() {
        $input = '06/15/2026 7:30 pm';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 19:30:00', $result);
    }

    public function test_acf_display_format_am() {
        $input = '06/15/2026 10:00 am';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 10:00:00', $result);
    }

    public function test_acf_display_format_single_digit_hour() {
        $input = '06/15/2026 7:00 pm';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 19:00:00', $result);
    }

    /**
     * Test Pattern 6: ISO 8601 with milliseconds
     */
    public function test_iso8601_with_milliseconds() {
        $input = '2026-06-15T19:30:00.123';
        $result = gicinema__parse_screening_datetime($input, 'test');

        $this->assertEquals('2026-06-15 19:30:00', $result);
    }

    /**
     * Test rejection of invalid formats
     */
    public function test_rejects_empty_string() {
        $result = gicinema__parse_screening_datetime('', 'test');
        $this->assertNull($result);
    }

    public function test_rejects_null() {
        $result = gicinema__parse_screening_datetime(null, 'test');
        $this->assertNull($result);
    }

    public function test_rejects_non_string() {
        $result = gicinema__parse_screening_datetime(12345, 'test');
        $this->assertNull($result);
    }

    public function test_rejects_invalid_date() {
        $result = gicinema__parse_screening_datetime('2026-13-45 25:99:99', 'test');
        $this->assertNull($result);
    }

    public function test_rejects_unrecognized_format() {
        $result = gicinema__parse_screening_datetime('June 15, 2026 7:30pm', 'test');
        $this->assertNull($result);
    }

    public function test_rejects_ambiguous_format() {
        // This format is ambiguous (US vs European date format)
        $result = gicinema__parse_screening_datetime('06-15-2026 19:30:00', 'test');
        $this->assertNull($result);
    }

    /**
     * Test DST vs Standard Time handling
     */
    public function test_summer_dst_offset() {
        // June 15 is during DST (Pacific is UTC-7)
        $input_utc = '2026-06-15T02:30:00Z';
        $result = gicinema__parse_screening_datetime($input_utc, 'test');

        // 02:30 UTC = 19:30 PDT (previous day)
        $this->assertEquals('2026-06-14 19:30:00', $result);
    }

    public function test_winter_standard_time_offset() {
        // January 15 is during standard time (Pacific is UTC-8)
        $input_utc = '2026-01-15T03:30:00Z';
        $result = gicinema__parse_screening_datetime($input_utc, 'test');

        // 03:30 UTC = 19:30 PST (previous day)
        $this->assertEquals('2026-01-14 19:30:00', $result);
    }

    /**
     * Test context parameter is passed (for error logging)
     */
    public function test_context_parameter_accepted() {
        // Should not throw errors with various context strings
        $input = '2026-06-15 19:30:00';

        $this->assertEquals('2026-06-15 19:30:00', gicinema__parse_screening_datetime($input, 'agile_import'));
        $this->assertEquals('2026-06-15 19:30:00', gicinema__parse_screening_datetime($input, 'acf_save'));
        $this->assertEquals('2026-06-15 19:30:00', gicinema__parse_screening_datetime($input, 'acf_read'));
        $this->assertEquals('2026-06-15 19:30:00', gicinema__parse_screening_datetime($input, ''));
    }
}
