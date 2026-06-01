<?php
/**
 * ACF screening cleanup for one film or a batch of films.
 *
 * Loaded by gicinema.php and by page__delete_all_superfluous_screenings.php.
 * The core routine compares a Film post's ACF "screenings" repeater against
 * active rows in the custom screenings table, keeps matching rows, and removes
 * stale ACF-only rows unless dry-run mode is requested. It also registers the
 * per-film admin-post handler and the batch AJAX handler used by the admin UI.
 * The AJAX response also includes date-range and rationale details for the
 * Delete Superfluous admin table.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Delete all screenings from the ACF `screenings` field that do not
 * match active screenings in the custom table for the given post.
 *
 * Returns an array with:
 * - 'original' => original count
 * - 'kept'     => kept count
 * - 'deleted'  => deleted count
 */
/**
 * Delete superfluous screenings from ACF repeater for a single film.
 *
 * DRY: This is the single source of truth used by both the per-film red button
 * and the global batch tool. To support "dry run" previews, pass $dry_run=true
 * to compute counts without updating ACF.
 */
function gicinema__delete_superfluous_acf_screenings($post_id, $dry_run = false) {
  $result = [
    'original' => 0,
    'kept' => 0,
    'deleted' => 0,
    'dry_run' => (bool) $dry_run,
    'screen_date_range' => '',
    'rationale' => 'No ACF screenings found.',
    'table_count' => 0,
    'unmatched' => 0,
    'unparseable' => 0,
  ];

  if (empty($post_id) || !function_exists('get_field') || !function_exists('update_field')) {
    return $result;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'gi_screenings';
  $table_exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name);
  if (!$table_exists) {
    return $result;
  }

  // Build a set of normalized table screenings for quick lookup
  $table_vals = $wpdb->get_col($wpdb->prepare(
    "SELECT screening FROM {$table_name} WHERE post_id = %d AND status = 1",
    $post_id
  ));
  $table_set = [];
  if (is_array($table_vals)) {
    foreach ($table_vals as $v) {
      if (!empty($v)) {
        $table_set[$v] = true; // Y-m-d H:i:s expected
      }
    }
  }
  $result['table_count'] = count($table_set);

  // Read ACF screenings
  $acf_rows = get_field('screenings', $post_id);
  if (!is_array($acf_rows)) {
    return $result;
  }

  $result['original'] = count($acf_rows);
  $acf_normalized = [];

  // Safety: If no active screenings exist in the custom table for this post,
  // do not delete anything from ACF. Treat everything as kept to avoid wiping
  // user-entered data when the canonical set is empty/stale.
  if (empty($table_set)) {
    $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(get_option('timezone_string') ?: 'UTC');
    foreach ($acf_rows as $row) {
      $label = isset($row['screening']) ? $row['screening'] : '';
      $normalized = gicinema__normalize_screening_for_cleanup($label, $tz);
      if ($normalized) {
        $acf_normalized[] = $normalized;
      }
    }

    $result['kept'] = $result['original'];
    $result['deleted'] = 0;
    $result['screen_date_range'] = gicinema__format_screening_date_range($acf_normalized);
    $result['rationale'] = 'No active custom-table screenings were found for this Film, so cleanup skipped deletion to avoid wiping ACF rows.';
    return $result;
  }

  $kept_rows = [];
  $kept_normalized = [];
  $unmatched_count = 0;
  $unparseable_count = 0;
  $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(get_option('timezone_string') ?: 'UTC');

  foreach ($acf_rows as $row) {
    $label = isset($row['screening']) ? $row['screening'] : '';
    $normalized = gicinema__normalize_screening_for_cleanup($label, $tz);

    if ($normalized) {
      $acf_normalized[] = $normalized;
    } else {
      $unparseable_count++;
    }

    $keep = false;
    if ($normalized && isset($table_set[$normalized])) {
      // Exact match to canonical table → keep
      $keep = true;
    } elseif ($normalized) {
      // Optional guard: treat timezone-shadow equivalents (± WP offset) as matches.
      // Default is DISABLED for delete operations so that phantom ±7/±8 hour twins
      // are removed. Can be re-enabled via constant or filter if needed.
      $enable_guard = false;
      if (defined('GICINEMA_TZ_SHADOW_GUARD') && GICINEMA_TZ_SHADOW_GUARD === true) {
        $enable_guard = true;
      }
      if (function_exists('apply_filters')) {
        $enable_guard = apply_filters('gicinema_enable_tz_shadow_guard', $enable_guard);
        // Specific hook for delete path, takes precedence if provided.
        $enable_guard = apply_filters('gicinema_enable_tz_shadow_guard_delete', $enable_guard);
      }
      if ($enable_guard) {
        $ts = strtotime($normalized);
        if ($ts) {
          try {
            $dt = new DateTime($normalized, $tz);
            $offset = $tz->getOffset($dt); // seconds (e.g., 25200/28800)
          } catch (Exception $e) {
            $offset = 0;
          }
          if ($offset) {
            $plus  = date('Y-m-d H:i:s', $ts + $offset);
            $minus = date('Y-m-d H:i:s', $ts - $offset);
            if (isset($table_set[$plus]) || isset($table_set[$minus])) {
              $keep = true;
            }
          }
        }
      }
    }

    if ($keep) {
      $kept_rows[] = $row; // keep only matching screenings
      if ($normalized) {
        $kept_normalized[] = $normalized;
      }
    } else {
      if ($normalized) {
        $unmatched_count++;
      }
    }
  }

  // Update ACF with the kept rows unless this is a dry run
  if (!$dry_run) {
    update_field('screenings', $kept_rows, $post_id);
  }

  $result['kept'] = count($kept_rows);
  $result['deleted'] = $result['original'] - $result['kept'];
  $result['screen_date_range'] = gicinema__format_screening_date_range($kept_normalized ?: $acf_normalized);
  $result['unmatched'] = $unmatched_count;
  $result['unparseable'] = $unparseable_count;
  $result['rationale'] = gicinema__build_superfluous_cleanup_rationale($result);
  return $result;
}





function gicinema__normalize_screening_for_cleanup($label, $tz) {
  if (!is_string($label) || $label === '') {
    return '';
  }

  // Use strict parser for all screening values (always uses wp_timezone)
  $normalized = gicinema__parse_screening_datetime($label, 'superfluous_cleanup');
  return $normalized ?: '';
}





function gicinema__format_screening_date_range($screenings) {
  $dates = [];

  foreach ((array) $screenings as $screening) {
    if (!is_string($screening) || $screening === '') {
      continue;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $screening)) {
      continue;
    }

    $dates[] = substr($screening, 0, 10);
  }

  $dates = array_values(array_unique($dates));
  sort($dates);

  if (empty($dates)) {
    return '';
  }

  $first = gicinema__format_screening_date_for_admin($dates[0]);
  $last = gicinema__format_screening_date_for_admin($dates[count($dates) - 1]);

  if ($first === $last) {
    return $first;
  }

  return $first . ' - ' . $last;
}





function gicinema__format_screening_date_for_admin($date) {
  // Normalize the date value using strict parser
  $normalized = gicinema__parse_screening_datetime($date, 'admin_date_format');
  if (!$normalized) {
    return ''; // Return empty if parsing failed
  }

  $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(get_option('timezone_string') ?: 'UTC');
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $normalized, $tz);

  if ($dt instanceof DateTime) {
    return $dt->format('n/j/Y');
  }

  return '';
}





function gicinema__build_superfluous_cleanup_rationale($result) {
  $original = isset($result['original']) ? (int) $result['original'] : 0;
  $kept = isset($result['kept']) ? (int) $result['kept'] : 0;
  $deleted = isset($result['deleted']) ? (int) $result['deleted'] : 0;
  $table_count = isset($result['table_count']) ? (int) $result['table_count'] : 0;
  $unmatched = isset($result['unmatched']) ? (int) $result['unmatched'] : 0;
  $unparseable = isset($result['unparseable']) ? (int) $result['unparseable'] : 0;

  if ($original === 0) {
    return 'No ACF screenings found.';
  }

  if ($table_count === 0) {
    return 'No active custom-table screenings were found for this Film, so cleanup skipped deletion to avoid wiping ACF rows.';
  }

  if ($deleted === 0) {
    return 'All ACF screenings matched active custom-table screenings after normalization.';
  }

  $parts = [];
  if ($unmatched > 0) {
    $parts[] = $unmatched . ' ACF ' . _n('row did', 'rows did', $unmatched, 'gicinema') . ' not match any active custom-table screening after normalization.';
  }
  if ($unparseable > 0) {
    $parts[] = $unparseable . ' ACF ' . _n('row could', 'rows could', $unparseable, 'gicinema') . ' not be parsed as a screening date.';
  }
  $parts[] = $kept . ' ' . _n('row matched', 'rows matched', $kept, 'gicinema') . ' active custom-table screenings and will be kept.';

  return implode(' ', $parts);
}

/**
 * Handle POST from the admin button to delete superfluous screenings.
 */
function gicinema_handle_delete_superfluous_screenings() {
  if (!current_user_can('edit_posts')) {
    wp_die(__('Insufficient permissions', 'gicinema'));
  }

  // Support both GET (link) and POST (form) submissions
  $post_id = isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;
  if (!$post_id || !current_user_can('edit_post', $post_id)) {
    wp_die(__('Invalid post', 'gicinema'));
  }

  check_admin_referer('gicinema_delete_superfluous_screenings', 'gicinema_nonce');

  $res = gicinema__delete_superfluous_acf_screenings($post_id);
  $msg = sprintf(
    'Deleted %d superfluous screening(s); kept %d of %d.',
    isset($res['deleted']) ? $res['deleted'] : 0,
    isset($res['kept']) ? $res['kept'] : 0,
    isset($res['original']) ? $res['original'] : 0
  );

  // Reuse an admin notice transient already used elsewhere
  set_transient('film_saved_admin_notice', esc_html($msg), 60);

  // Redirect back to the edit screen
  $redirect = get_edit_post_link($post_id, '');
  if (!$redirect) {
    $redirect = admin_url('post.php?post=' . $post_id . '&action=edit');
  }
  wp_safe_redirect($redirect);
  exit;
}

add_action('admin_post_gicinema_delete_superfluous_screenings', 'gicinema_handle_delete_superfluous_screenings');

/**
 * AJAX handler: delete superfluous screenings for a single film (by post_id).
 * Returns JSON with counts and basic film info for UI logging.
 */
function gicinema_ajax_delete_superfluous_batch() {
  if ( ! current_user_can('manage_options') ) {
    wp_send_json_error(['message' => 'forbidden'], 403);
  }
  check_ajax_referer('gicinema_delete_all_superfluous');

  $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
  if (!$post_id) {
    wp_send_json_error(['message' => 'invalid post_id'], 400);
  }

  $dry_run = !empty($_POST['dry_run']);
  $res = gicinema__delete_superfluous_acf_screenings($post_id, $dry_run);
  $title = html_entity_decode(
    wp_strip_all_tags(get_the_title($post_id)),
    ENT_QUOTES | ENT_HTML5,
    get_option('blog_charset') ?: 'UTF-8'
  );
  $edit  = get_edit_post_link($post_id, '');

  wp_send_json_success([
    'post_id' => $post_id,
    'title'   => $title,
    'edit_link' => $edit,
    'original' => isset($res['original']) ? (int)$res['original'] : 0,
    'kept'     => isset($res['kept']) ? (int)$res['kept'] : 0,
    'deleted'  => isset($res['deleted']) ? (int)$res['deleted'] : 0,
    'screen_date_range' => isset($res['screen_date_range']) ? $res['screen_date_range'] : '',
    'rationale' => isset($res['rationale']) ? $res['rationale'] : '',
    'dry_run'  => !empty($res['dry_run']),
  ]);
}
add_action('wp_ajax_gicinema_delete_superfluous_batch', 'gicinema_ajax_delete_superfluous_batch');
