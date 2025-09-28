<?php

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
function gicinema__delete_superfluous_acf_screenings($post_id) {
  $result = ['original' => 0, 'kept' => 0, 'deleted' => 0];

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

  // Read ACF screenings
  $acf_rows = get_field('screenings', $post_id);
  if (!is_array($acf_rows)) {
    return $result;
  }

  $result['original'] = count($acf_rows);
  $kept_rows = [];

  foreach ($acf_rows as $row) {
    $label = isset($row['screening']) ? $row['screening'] : '';
    $normalized = '';
    if (is_string($label) && $label !== '') {
      $dt = DateTime::createFromFormat('m/d/Y g:i a', $label);
      if ($dt instanceof DateTime) {
        $normalized = $dt->format('Y-m-d H:i:s');
      } else {
        $ts = strtotime($label);
        if ($ts) {
          $normalized = date('Y-m-d H:i:s', $ts);
        }
      }
    }

    if ($normalized && isset($table_set[$normalized])) {
      $kept_rows[] = $row; // keep only matching screenings
    }
  }

  // Update ACF with the kept rows
  update_field('screenings', $kept_rows, $post_id);

  $result['kept'] = count($kept_rows);
  $result['deleted'] = $result['original'] - $result['kept'];
  return $result;
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
