<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema__delete_overnight_screenings() {
  // CSRF Protection - always required since this only runs from forms
  if (!isset($_POST['delete_overnight_nonce']) || !wp_verify_nonce($_POST['delete_overnight_nonce'], 'delete_overnight_action')) {
    return "Security check failed - unauthorized request";
  }

  // Deprecated by default: short-circuit with message unless explicitly re-enabled via filter.
  $enabled = false;
  if (function_exists('apply_filters')) {
    $enabled = apply_filters('gicinema_enable_overnight_tool', false);
  }
  if (! $enabled) {
    // Provide a non-destructive preview by default to avoid data loss.
    $_POST['dry_run'] = 1;
  }

  $dry_run = !empty($_POST['dry_run']);

  global $wpdb; // Ensure global $wpdb is accessible
  $table = $wpdb->prefix . 'gi_screenings';

  // Build the selection of candidate rows based on the existing rules.
  $sql = $wpdb->prepare(
    "SELECT s.screening_id, s.post_id, s.screening, s.screening_date, s.screening_time, p.post_title
     FROM {$table} AS s
     LEFT JOIN {$wpdb->posts} AS p ON p.ID = s.post_id
     WHERE ( (s.screening_time >= %s AND s.screening_time <= %s)
          OR (s.screening_time >= %s AND s.screening_time <= %s)
          OR (s.post_id = %d AND s.screening_time >= %s)
          OR (s.screening = %s) )
     ORDER BY s.screening ASC",
    '00:00:00', '10:00:00',
    '22:00:00', '23:59:59',
    4704, '20:00:00',
    'Invalid date'
  );

  $rows = $wpdb->get_results($sql, ARRAY_A);
  $count = is_array($rows) ? count($rows) : 0;

  if ($dry_run) {
    // Render a human-readable preview of what would be deleted.
    $out  = '<div class="function-info">';
    $out .= '<h4>Dry run — would delete ' . intval($count) . ' screening row' . ($count === 1 ? '' : 's') . '</h4>';
    if ($count > 0) {
      $out .= '<ul style="margin:6px 0 0 18px; list-style:disc;">';
      foreach ($rows as $r) {
        $title = isset($r['post_title']) && $r['post_title'] !== '' ? $r['post_title'] : ('Film #' . $r['post_id']);
        $label = trim(($r['screening_date'] ?: '') . ' ' . ($r['screening_time'] ?: ''));
        if ($label === '') { $label = $r['screening']; }
        $out .= '<li>' . esc_html($title) . ' — ' . esc_html($label) . ' (ID ' . intval($r['screening_id']) . ')</li>';
      }
      $out .= '</ul>';
    }
    $out .= '</div>';

    // Show a proceed form if there are candidates.
    if ($count > 0) {
      $out .= '<form method="post" style="margin-top:10px;">';
      $out .= wp_nonce_field('delete_overnight_action', 'delete_overnight_nonce', true, false);
      $out .= '<input type="hidden" name="confirm_delete" value="yes">';
      $out .= '<input type="submit" class="button button-primary" value="Proceed to delete these ' . intval($count) . ' screening(s)">';
      $out .= '</form>';
    }
    return $out;
  }

  // If not a dry run: delete exactly the selected rows by ID for precision.
  if ($count === 0) {
    return "No overnight screenings found to delete.";
  }

  // Chunk deletions to avoid overly long IN() lists.
  $ids = array_map('intval', wp_list_pluck($rows, 'screening_id'));
  $deleted_total = 0;
  $chunks = array_chunk($ids, 500);
  foreach ($chunks as $chunk) {
    $placeholders = implode(',', array_fill(0, count($chunk), '%d'));
    $del_sql = $wpdb->prepare("DELETE FROM {$table} WHERE screening_id IN ({$placeholders})", $chunk);
    $deleted = $wpdb->query($del_sql);
    if ($deleted !== false) {
      $deleted_total += (int) $deleted;
    }
  }

  return "Deleted {$deleted_total} overnight screening row" . ($deleted_total === 1 ? '' : 's') . ".";
}
