<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Ensure the custom screenings table has a unique index on the normalized
 * screening string, and perform a one-time dedupe of historical rows.
 */
function gicinema__ensure_screenings_unique_index() {
  global $wpdb;

  $table = $wpdb->prefix . 'gi_screenings';
  // Verify table exists
  $exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table);
  if (!$exists) {
    return;
  }

  // Check whether our unique index exists
  $has_unique = false;
  $indexes = $wpdb->get_results("SHOW INDEX FROM `{$table}`", ARRAY_A);
  if (is_array($indexes)) {
    foreach ($indexes as $idx) {
      if (!empty($idx['Key_name']) && $idx['Key_name'] === 'unique_screening_str') {
        $has_unique = true;
        break;
      }
    }
  }

  // Add unique index if missing
  $added_index = false;
  if (!$has_unique) {
    // Use a prefix length of 19 to cover 'YYYY-MM-DD HH:MM:SS'
    $wpdb->query("ALTER TABLE `{$table}` ADD UNIQUE KEY unique_screening_str (film_id, post_id, screening(19))");
    $added_index = true;
  }

  // Run the dedupe at most once, or when index is newly added, unless forced.
  $ran_version = get_option('gicinema_screenings_schema_v1');
  $force = apply_filters('gicinema_force_schema_ensure', false) ? true : false;
  if ($force || $added_index || empty($ran_version)) {
    $dedupe_sql = "
      DELETE t1
      FROM {$table} AS t1
      LEFT JOIN (
        SELECT MIN(screening_id) AS min_id
        FROM {$table}
        GROUP BY screening, film_id, post_id
      ) AS t2 ON t1.screening_id = t2.min_id
      WHERE t2.min_id IS NULL
    ";
    $wpdb->query($dedupe_sql);
    update_option('gicinema_screenings_schema_v1', 'done');
  }
}

// Note: We intentionally do NOT hook this globally.
// The importer calls gicinema__ensure_screenings_unique_index() directly so it only runs
// when needed (manual or cron import).
