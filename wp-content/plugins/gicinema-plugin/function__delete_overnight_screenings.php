<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__delete_overnight_screenings() {
  // CSRF Protection - always required since this only runs from forms
  if (!isset($_POST['delete_overnight_nonce']) || !wp_verify_nonce($_POST['delete_overnight_nonce'], 'delete_overnight_action')) {
    return "Security check failed - unauthorized request";
  }

  global $wpdb; // Ensure global $wpdb is accessible

  // Initialize the counter
  $total_deleted = 0;

  // Delete screenings between 00:00:00 and 10:00:00
  $deleted = $wpdb->query(
    "DELETE FROM {$wpdb->prefix}gi_screenings 
        WHERE screening_time >= '00:00:00' AND screening_time <= '10:00:00'"
  );
  $total_deleted += $deleted; // Add to total

  // Delete screenings between 22:00:00 and 23:59:59
  $deleted = $wpdb->query(
    "DELETE FROM {$wpdb->prefix}gi_screenings 
        WHERE screening_time >= '22:00:00' AND screening_time <= '23:59:59'"
  );
  $total_deleted += $deleted; // Add to total

  // Delete secret matinees 
  $deleted = $wpdb->query(
    "DELETE FROM {$wpdb->prefix}gi_screenings 
        WHERE post_id = 4704 AND screening_time >= '20:00:00'"
  );
  $total_deleted += $deleted; // Add to total

  // Delete screenings with 'Invalid date'
  $deleted = $wpdb->query(
    "DELETE FROM {$wpdb->prefix}gi_screenings 
        WHERE screening = 'Invalid date'"
  );
  $total_deleted += $deleted; // Add to total

  // Return total deleted records
  return "All 'overnight' screenings have been deleted. Total records deleted: $total_deleted.";
}
