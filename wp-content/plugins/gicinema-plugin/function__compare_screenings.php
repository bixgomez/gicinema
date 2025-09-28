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
          // ACF stores e.g. m/d/Y g:i a (per existing code). Normalize to Y-m-d H:i:s
          $dt = DateTime::createFromFormat('m/d/Y g:i a', $row['screening']);
          if ($dt instanceof DateTime) {
            $acf_screenings[] = $dt->format('Y-m-d H:i:s');
          } else {
            // Fallback: try parsing via strtotime
            $ts = strtotime($row['screening']);
            if ($ts) {
              $acf_screenings[] = date('Y-m-d H:i:s', $ts);
            }
          }
        }
      }
    }
  } else {
    // If ACF unavailable, attempt to read a count but we can’t derive values; return early.
    return '';
  }

  // 3) Compute intersection
  $table_set = array_values(array_unique($table_matches));
  $acf_set   = array_values(array_unique($acf_screenings));
  $matching  = array_values(array_intersect($table_set, $acf_set));

  // 4) Render HTML list
  $html = '';
  $html .= '<h4 style="margin:8px 0 6px;">Matching screenings:</h4>';
  if (empty($matching)) {
    $html .= '<p style="margin:0; color:#777;">None</p>';
    return $html;
  }

  $date_format = get_option('date_format') ?: 'M j, Y';
  $time_format = get_option('time_format') ?: 'g:i a';
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
    "SELECT screening FROM {$table_name} WHERE post_id = %d AND status = 1 ORDER BY screening ASC",
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
