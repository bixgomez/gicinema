<?php
/**
 * Local-development destructive screenings-table truncation routine.
 *
 * Loaded by page__truncate_screenings_table.php, but only defines its function
 * when WP_LOCAL_DEV is true. This tool will only be available on the local dev
 * server, not on production. When run from the local-only admin
 * confirmation form, it checks the security token, counts current rows, and
 * empties the gi_screenings table.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  // Function to delete all screenings data
  function gicinema__truncate_screenings_table() {
    // CSRF Protection - always required since this only runs from forms
    if (!isset($_POST['truncate_nonce']) || !wp_verify_nonce($_POST['truncate_nonce'], 'truncate_table_action')) {
      return "Security check failed - unauthorized request";
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'gi_screenings';
    // Count rows before truncation for reporting
    $before = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table_name`");

    // Truncate the table
    $sql = "TRUNCATE TABLE `$table_name`";
    $wpdb->query($sql);

    // Optional: Check if the operation was successful
    if ($wpdb->last_error !== '') {
      return "An error occurred: " . $wpdb->last_error;
    } else {
      return "Truncated table '$table_name'; removed {$before} row" . ($before === 1 ? '' : 's') . ".";
    }
  }
}
