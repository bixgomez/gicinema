<?php
/**
 * Strict datetime parser for screening values.
 *
 * Replaces risky strtotime() + date() fallback patterns with explicit format
 * parsing that always uses WordPress timezone. This prevents timezone-shifted
 * duplicates caused by PHP's default timezone being used instead of the
 * WordPress site timezone.
 *
 * The parser accepts only known, unambiguous datetime formats and rejects
 * anything that cannot be reliably interpreted.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Parse a datetime string to normalized Y-m-d H:i:s format in WordPress timezone.
 *
 * Accepts these formats explicitly:
 * - ISO 8601 without timezone: 2026-06-08T19:30:00 (treats as WordPress local time)
 * - ISO 8601 with Z: 2026-06-08T19:30:00Z (converts from UTC)
 * - ISO 8601 with offset: 2026-06-08T19:30:00-07:00 (converts to WordPress timezone)
 * - Normalized storage: 2026-06-08 19:30:00 (treats as WordPress local time)
 * - ACF display format: 06/08/2026 7:30 pm (treats as WordPress local time)
 *
 * Rejects:
 * - Ambiguous strings ("next Tuesday", "tomorrow", etc.)
 * - Malformed datetime values
 * - Empty or null values
 *
 * @param string $value The datetime string to parse
 * @param string $context Optional context for error logging (e.g., 'agile_import', 'acf_read')
 * @return string|null Normalized datetime as Y-m-d H:i:s in WordPress timezone, or null on failure
 */
function gicinema__parse_screening_datetime($value, $context = '') {
  // Reject empty/null values immediately
  if (empty($value) || !is_string($value)) {
    return null;
  }

  $value = trim($value);
  if ($value === '') {
    return null;
  }

  // Get WordPress timezone for all parsing
  $tz = wp_timezone();

  // Pattern 1: Already normalized (Y-m-d H:i:s)
  // Example: 2026-06-08 19:30:00
  if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
    // Validate it's actually a valid datetime
    try {
      $dt = new DateTime($value, $tz);
      return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      gicinema__log_parse_error($value, 'Invalid Y-m-d H:i:s datetime', $context, $e);
      return null;
    }
  }

  // Pattern 2: ISO 8601 with UTC marker (Z)
  // Example: 2026-06-08T19:30:00Z
  if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $value)) {
    try {
      $dt = new DateTime($value, new DateTimeZone('UTC'));
      $dt->setTimezone($tz);
      return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      gicinema__log_parse_error($value, 'Invalid ISO 8601 with Z', $context, $e);
      return null;
    }
  }

  // Pattern 3: ISO 8601 with timezone offset
  // Example: 2026-06-08T19:30:00-07:00 or 2026-06-08T19:30:00+00:00
  if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $value)) {
    try {
      $dt = new DateTime($value); // PHP will parse the offset
      $dt->setTimezone($tz);
      return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      gicinema__log_parse_error($value, 'Invalid ISO 8601 with offset', $context, $e);
      return null;
    }
  }

  // Pattern 4: ISO 8601 without timezone (Agile's current format)
  // Example: 2026-06-08T19:30:00
  // Treat as WordPress local time since Agile doesn't include timezone info
  if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value)) {
    try {
      $dt = new DateTime($value, $tz);
      return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      gicinema__log_parse_error($value, 'Invalid ISO 8601 without timezone', $context, $e);
      return null;
    }
  }

  // Pattern 5: ACF display format (m/d/Y g:i a)
  // Example: 06/08/2026 7:30 pm
  if (preg_match('/^\d{2}\/\d{2}\/\d{4} \d{1,2}:\d{2} [ap]m$/i', $value)) {
    try {
      $dt = DateTime::createFromFormat('m/d/Y g:i a', $value, $tz);
      if ($dt === false) {
        gicinema__log_parse_error($value, 'Failed to parse ACF display format', $context);
        return null;
      }
      return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      gicinema__log_parse_error($value, 'Invalid ACF display format', $context, $e);
      return null;
    }
  }

  // Pattern 6: ISO 8601 with seconds and milliseconds (just in case Agile adds precision)
  // Example: 2026-06-08T19:30:00.000
  if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+$/', $value)) {
    try {
      // Remove the milliseconds part
      $value_clean = preg_replace('/\.\d+$/', '', $value);
      $dt = new DateTime($value_clean, $tz);
      return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      gicinema__log_parse_error($value, 'Invalid ISO 8601 with milliseconds', $context, $e);
      return null;
    }
  }

  // No pattern matched - reject the value
  gicinema__log_parse_error($value, 'Unrecognized datetime format', $context);
  return null;
}

/**
 * Log datetime parsing errors for debugging.
 *
 * Only logs in WP_DEBUG mode to avoid cluttering production logs.
 *
 * @param string $value The value that failed to parse
 * @param string $reason Why it failed
 * @param string $context Where the parsing was attempted
 * @param Exception|null $exception Optional exception details
 */
function gicinema__log_parse_error($value, $reason, $context = '', $exception = null) {
  if (!defined('WP_DEBUG') || !WP_DEBUG) {
    return;
  }

  $message = 'GI Cinema datetime parse error: ' . $reason;
  if ($context) {
    $message .= ' [context: ' . $context . ']';
  }
  $message .= ' [value: "' . $value . '"]';

  if ($exception) {
    $message .= ' [exception: ' . $exception->getMessage() . ']';
  }

  error_log($message);
}

/**
 * Helper: Check if a datetime value needs normalization.
 *
 * Returns true if the value is not already in Y-m-d H:i:s format.
 *
 * @param string $value The datetime string to check
 * @return bool True if normalization is needed, false if already normalized
 */
function gicinema__datetime_needs_normalization($value) {
  if (empty($value) || !is_string($value)) {
    return true;
  }

  return !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', trim($value));
}

/**
 * Helper: Detect if a datetime value might be a timezone shadow duplicate.
 *
 * Compares two datetime strings and returns true if they differ by exactly
 * ±7 or ±8 hours (the Pacific/UTC offset during DST and standard time).
 *
 * @param string $datetime1 First datetime (Y-m-d H:i:s format)
 * @param string $datetime2 Second datetime (Y-m-d H:i:s format)
 * @return bool True if the values appear to be timezone shadows
 */
function gicinema__is_timezone_shadow($datetime1, $datetime2) {
  if (empty($datetime1) || empty($datetime2)) {
    return false;
  }

  try {
    $tz = wp_timezone();
    $dt1 = new DateTime($datetime1, $tz);
    $dt2 = new DateTime($datetime2, $tz);

    $diff_seconds = abs($dt2->getTimestamp() - $dt1->getTimestamp());

    // Check for exactly 7 or 8 hour differences (Pacific timezone offsets)
    // Compare seconds directly to avoid floating-point precision issues
    return ($diff_seconds === 25200 || $diff_seconds === 28800); // 7*3600=25200, 8*3600=28800
  } catch (Exception $e) {
    return false;
  }
}
