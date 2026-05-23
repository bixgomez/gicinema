<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Returns HTML for a list of screenings that exist in both the
 * custom table ({$wpdb->prefix}gi_screenings) and the ACF `screenings` field.
 *
 * Output example:
 *   <h4>Matching screenings:</h4>
 *   <ul><li>Aug 20, 2025 7:30 pm</li> ...</ul>
 *
 * If none are found, returns a short note that none match.
 */
function gicinema__render_matching_screenings($post_id) {
  global $wpdb;

  if (empty($post_id)) {
    return '';
  }

  // 1) Collect active screenings from custom table as normalized strings: Y-m-d H:i:s
  $table_matches = [];
  $table_name = $wpdb->prefix . 'gi_screenings';
  $table_exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name);
  if ($table_exists) {
    $rows = $wpdb->get_col($wpdb->prepare("SELECT screening FROM {$table_name} WHERE post_id = %d AND status = 1", $post_id));
    if (is_array($rows)) {
      foreach ($rows as $val) {
        // Expect values already in Y-m-d H:i:s; keep as-is.
        if (!empty($val)) {
          $table_matches[] = $val;
        }
      }
    }
  }

  // 2) Collect screenings from ACF `screenings` repeater as Y-m-d H:i:s
  $acf_screenings = [];
  if (function_exists('get_field')) {
    $acf_value = get_field('screenings', $post_id);
    if (is_array($acf_value)) {
      foreach ($acf_value as $row) {
        if (isset($row['screening']) && is_string($row['screening'])) {
          $label = $row['screening'];
          // If already normalized (Y-m-d H:i:s), keep as-is
          if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $label)) {
            $acf_screenings[] = $label;
          } else {
            // Normalize to Y-m-d H:i:s in WP timezone
            $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(get_option('timezone_string') ?: 'UTC');
            $dt = DateTime::createFromFormat('m/d/Y g:i a', $label, $tz);
            if ($dt instanceof DateTime) {
              $dt->setTimezone($tz);
              $acf_screenings[] = $dt->format('Y-m-d H:i:s');
            } else {
              $ts = strtotime($label);
              if ($ts) {
                $acf_screenings[] = date('Y-m-d H:i:s', $ts);
              }
            }
          }
        }
      }
    }
  } else {
    // If ACF unavailable, attempt to read a count but we can't derive values; return early.
    return '';
  }

  // 3) Compute intersection
  $table_set = array_values(array_unique($table_matches));
  $acf_set   = array_values(array_unique($acf_screenings));
  $matching  = array_values(array_intersect($table_set, $acf_set));

  // 4) Render HTML list
  $html = '';
  $html .= '<h4 style="margin:8px 0 6px;">Matching screenings:</h4>';
  $date_format = get_option('date_format') ?: 'M j, Y';
  $time_format = get_option('time_format') ?: 'g:i a';
  if (empty($matching)) {
    $html .= '<p style="margin:0; color:#777;">None</p>';
  } else {
    $html .= '<ul style="margin:0 0 2px 18px; list-style:disc;">';
    foreach ($matching as $val) {
      $ts = strtotime($val);
      if ($ts) {
        $label = date_i18n($date_format . ' ' . $time_format, $ts);
      } else {
        $label = $val; // fallback raw
      }
      $html .= '<li>' . esc_html($label) . '</li>';
    }
    $html .= '</ul>';
  }

  // 5) Analysis: count superfluous ACF screenings using the exact same logic as the
  // bulk tool (DRY). We invoke the per-film deleter in dry-run mode so the
  // calculation (normalization + timezone shadow guard) is identical.
  $superfluous_count = 0;
  if (function_exists('gicinema__delete_superfluous_acf_screenings')) {
    $res = gicinema__delete_superfluous_acf_screenings($post_id, true /* dry_run */);
    if (is_array($res) && isset($res['deleted'])) {
      $superfluous_count = (int) $res['deleted'];
    }
  }

  if ($superfluous_count > 0) {
    $html .= '<p style="margin:6px 0 0; color:#b32d2e;"><strong>' . esc_html($superfluous_count) . ' ' . esc_html(_n('Superfluous Screening', 'Superfluous Screenings', $superfluous_count, 'gicinema')) . '</strong></p>';
  } else {
    $html .= '<p style="margin:6px 0 0; color:#008a20;"><strong>' . esc_html__('No Superfluous Screenings', 'gicinema') . '</strong></p>';
  }

  return $html;
}

/**
 * Returns HTML for the list of active screenings from the custom table
 * ({$wpdb->prefix}gi_screenings) for a given post, formatted for display.
 */
function gicinema__render_table_screenings($post_id) {
  global $wpdb;
  if (empty($post_id)) {
    return '';
  }

  $table_name = $wpdb->prefix . 'gi_screenings';
  $table_exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name);
  $html = '';
  $html .= '<h4 style="margin:8px 0 6px;">Screenings (from custom table):</h4>';

  if (!$table_exists) {
    $html .= '<p style="margin:0; color:#777;">None</p>';
    return $html;
  }

  $rows = $wpdb->get_col($wpdb->prepare(
    "SELECT DISTINCT screening FROM {$table_name} WHERE post_id = %d AND status = 1 ORDER BY screening ASC",
    $post_id
  ));

  if (empty($rows)) {
    $html .= '<p style="margin:0; color:#777;">None</p>';
    return $html;
  }

  $date_format = get_option('date_format') ?: 'M j, Y';
  $time_format = get_option('time_format') ?: 'g:i a';
  $html .= '<ul style="margin:0 0 2px 18px; list-style:disc;">';
  foreach ($rows as $val) {
    $ts = strtotime($val);
    $label = $ts ? date_i18n($date_format . ' ' . $time_format, $ts) : $val;
    $html .= '<li>' . esc_html($label) . '</li>';
  }
  $html .= '</ul>';

  return $html;
}

/**
 * Compute the number of superfluous ACF screenings for a film
 * i.e., ACF `screenings` entries that are NOT present in the
 * custom table active set for this post.
 */
function gicinema__count_superfluous_screenings($post_id) {
  if (empty($post_id)) {
    return 0;
  }

  // DRY with the bulk tool: use the same logic by invoking the per-film
  // deleter in dry-run mode and returning the computed 'deleted' count.
  if (function_exists('gicinema__delete_superfluous_acf_screenings')) {
    $res = gicinema__delete_superfluous_acf_screenings($post_id, true /* dry_run */);
    if (is_array($res) && isset($res['deleted'])) {
      return (int) $res['deleted'];
    }
  }

  return 0;
}
