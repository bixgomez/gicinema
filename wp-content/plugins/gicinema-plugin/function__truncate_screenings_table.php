<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  // Function to delete all screenings data
  function gicinema__truncate_screenings_table() {
    // CSRF Protection - always required since this only runs from forms
    if (!isset($_POST['truncate_nonce']) || !wp_verify_nonce($_POST['truncate_nonce'], 'truncate_table_action')) {
      return "Security check failed - unauthorized request";
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'gi_screenings';

    $sql = "TRUNCATE TABLE `$table_name`";

    $wpdb->query($sql);

    // Optional: Check if the operation was successful
    if ($wpdb->last_error !== '') {
      return "An error occurred: " . $wpdb->last_error;
    } else {
      return "Table '$table_name' has been successfully truncated.";
    }
  }
}
