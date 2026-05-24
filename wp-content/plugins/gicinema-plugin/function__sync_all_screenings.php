<?php
/**
 * All-film screening synchronization routine.
 *
 * Loaded by page__sync_all_screenings.php. It runs from the manual Sync All
 * Screenings admin form and loops through Film posts newest-first, calling the
 * per-film sync helper for each one. Dry run is the default and does not write
 * to ACF or the custom table. Commit mode performs the ACF rewrite and can also
 * perform optional custom-table repair actions.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__dedupe_screenings_table.php";
require_once "function__sync_screenings.php";
require_once "function__delete_superfluous_screenings.php";

function gicinema__sync_all_screenings($opts = []) {
  // CSRF Protection - only when called via admin form
  if (isset($_POST['confirm_import'])) {
    if (!isset($_POST['sync_nonce']) || !wp_verify_nonce($_POST['sync_nonce'], 'sync_screenings_action')) {
      echo '<div class="notice notice-error"><p>Security check failed</p></div>';
      return;
    }
  }

  // Options (allow override via $opts or POST)
  $copy_acf_to_table  = isset($opts['two_way'])            ? (bool)$opts['two_way']            : (!empty($_POST['two_way']));
  if (isset($opts['dry_run'])) {
    $dry_run = (bool) $opts['dry_run'];
  } elseif (isset($_POST['sync_mode'])) {
    $dry_run = ($_POST['sync_mode'] !== 'commit');
  } elseif (!empty($_POST['dry_run'])) {
    $dry_run = true;
  } else {
    $dry_run = true;
  }
  $deactivate_missing = isset($opts['deactivate_missing']) ? (bool)$opts['deactivate_missing'] : (!empty($_POST['deactivate_missing']));
  $repair_table       = $copy_acf_to_table || $deactivate_missing;

  echo '<div class="notice notice-info inline">';
  echo '<p><strong>Mode:</strong> ' . ($dry_run ? 'Dry run. No ACF or custom-table changes will be written.' : 'Commit changes. ACF changes and selected custom-table repairs will be written.') . '</p>';
  echo '</div>';

  // Arguments for the query
  $args = array(
    'post_type' => 'film', // Your custom post type name
    'posts_per_page' => -1, // Retrieve all posts
    'orderby' => 'date', // Order by date
    'order' => 'DESC' // Descending order
  );

  // The Query
  $the_query = new WP_Query($args);

  // Check if the Query returns any posts
  if ($the_query->have_posts()) {

      echo '<div class="gicinema-sync-results-scroll">';
      echo '<table class="widefat striped gicinema-sync-results">';
      echo '<thead>';
      echo '<tr>';
      echo '<th scope="col">Film</th>';
      echo '<th scope="col">Active table rows</th>';
      echo '<th scope="col">Current ACF rows</th>';
      echo '<th scope="col">Resulting ACF rows</th>';
      echo '<th scope="col">ACF action</th>';
      echo '<th scope="col">Custom-table action</th>';
      echo '</tr>';
      echo '</thead>';
      echo '<tbody>';

      while ($the_query->have_posts()) {

          $the_query->the_post();
          $post_link = get_permalink();
          $post_id = get_the_ID();
          $post_title = get_the_title();
          $posted_date = get_the_date('Y-m-d');
          $agile_id = get_field('agile_film_id');
          $repair_summary = [
            'enabled' => $repair_table,
            'dry_run' => $dry_run,
            'to_add' => [],
            'to_deactivate' => [],
          ];

          // Optionally repair the custom table using the ACF set.
          if ($repair_table) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'gi_screenings';

            // Current canonical table set and current ACF set (normalized)
            $table_vals = gicinema__get_screenings_from_table($post_id);
            $acf_vals   = gicinema__get_screenings_from_post($post_id);
            $table_set = [];
            foreach ((array)$table_vals as $v) { if (is_string($v) && $v !== '') $table_set[$v] = true; }
            $acf_set = [];
            foreach ((array)$acf_vals as $v) { if (is_string($v) && $v !== '') $acf_set[$v] = true; }

            // Compute additions and deactivations for the selected table repair actions.
            $to_add = [];
            if ($copy_acf_to_table) {
              foreach ($acf_set as $val => $_t) {
                if (!isset($table_set[$val])) { $to_add[] = $val; }
              }
            }
            $to_deactivate = [];
            if ($deactivate_missing) {
              foreach ($table_set as $val => $_t) {
                if (!isset($acf_set[$val])) { $to_deactivate[] = $val; }
              }
            }

            $repair_summary['to_add'] = $to_add;
            $repair_summary['to_deactivate'] = $to_deactivate;

            // Insert missing rows or reactivate existing rows, respecting the unique key.
            if (!$dry_run && !empty($to_add)) {
              foreach ($to_add as $screening) {
                $screening = sanitize_text_field($screening);
                // Split into date/time components
                $parts = explode(' ', $screening);
                $screening_date = isset($parts[0]) ? $parts[0] : '';
                $screening_time = isset($parts[1]) ? $parts[1] : '';
                // If time part contains seconds, keep HH:MM:SS; otherwise best-effort
                if (isset($parts[2])) { $screening_time = $parts[1] . ' ' . $parts[2]; }

                $wpdb->query($wpdb->prepare(
                  "INSERT INTO {$table_name} (screening, screening_date, screening_time, film_id, post_id, status)
                   VALUES (%s, %s, %s, %d, %d, 1)
                   ON DUPLICATE KEY UPDATE status = 1",
                  $screening,
                  $screening_date,
                  $screening_time,
                  (int)$agile_id,
                  (int)$post_id
                ));
              }
            }

            // Deactivate rows not present in ACF (optional strict mode)
            if (!$dry_run && $deactivate_missing && !empty($to_deactivate)) {
              // Chunk to avoid overly long IN lists
              $chunks = array_chunk($to_deactivate, 50);
              foreach ($chunks as $chunk) {
                $placeholders = implode(',', array_fill(0, count($chunk), '%s'));
                $sql = $wpdb->prepare(
                  "UPDATE {$table_name} SET status = 0 WHERE post_id = %d AND screening IN ($placeholders)",
                  array_merge([(int)$post_id], $chunk)
                );
                $wpdb->query($sql);
              }
            }
          }

          // Always preview or update ACF from canonical merge (table + ACF).
          $sync_summary = gicinema__sync_screenings($post_id, $dry_run, false);
          gicinema__render_sync_all_screenings_row($post_id, $post_title, $post_link, $posted_date, $sync_summary, $repair_summary);
      }

      echo '</tbody>';
      echo '</table>';
      echo '</div>';

      /* Restore original Post Data 
      * NB: Because we are using new WP_Query we aren't stomping on the 
      * original $wp_query and it does not need to be reset with 
      * wp_reset_query(). We just need to reset the post data with 
      * wp_reset_postdata().
      */
      wp_reset_postdata();

  } else {

      // No posts found
      echo '<div class="notice notice-warning inline"><p>No films found.</p></div>';

  }
}

function gicinema__render_sync_all_screenings_row($post_id, $post_title, $post_link, $posted_date, $sync_summary, $repair_summary) {
  $decoded_title = wp_specialchars_decode($post_title, ENT_QUOTES);

  echo '<tr>';

  echo '<th scope="row">';
  echo '<a href="' . esc_url($post_link) . '" target="_blank" rel="noopener">' . esc_html($decoded_title) . '</a>';
  echo '<br><span class="description">Post ID ' . esc_html($post_id) . '; posted ' . esc_html($posted_date) . '</span>';
  echo '</th>';

  echo '<td>';
  gicinema__render_screening_details($sync_summary['table_screenings']);
  echo '</td>';

  echo '<td>';
  gicinema__render_screening_details($sync_summary['acf_screenings']);
  echo '</td>';

  echo '<td>';
  gicinema__render_screening_details($sync_summary['merged_screenings']);
  echo '</td>';

  echo '<td>' . esc_html(gicinema__get_sync_screenings_action_label($sync_summary)) . '</td>';

  echo '<td>';
  gicinema__render_sync_all_table_repair_summary($repair_summary);
  echo '</td>';

  echo '</tr>';
}

function gicinema__render_sync_all_table_repair_summary($repair_summary) {
  if (empty($repair_summary['enabled'])) {
    echo '<span class="description">Not selected</span>';
    return;
  }

  $to_add = isset($repair_summary['to_add']) ? (array) $repair_summary['to_add'] : [];
  $to_deactivate = isset($repair_summary['to_deactivate']) ? (array) $repair_summary['to_deactivate'] : [];
  $dry_run = !empty($repair_summary['dry_run']);
  $add_count = count($to_add);
  $deactivate_count = count($to_deactivate);

  if ($dry_run) {
    echo 'Would add/reactivate ' . esc_html($add_count) . '; would mark inactive ' . esc_html($deactivate_count) . '.';
  } else {
    echo 'Added/reactivated ' . esc_html($add_count) . '; marked inactive ' . esc_html($deactivate_count) . '.';
  }

  if ($add_count || $deactivate_count) {
    echo '<details>';
    echo '<summary>Show affected table rows</summary>';

    if ($add_count) {
      echo '<p><strong>Add/reactivate</strong></p>';
      gicinema__render_screening_details($to_add);
    }

    if ($deactivate_count) {
      echo '<p><strong>Mark inactive</strong></p>';
      gicinema__render_screening_details($to_deactivate);
    }

    echo '</details>';
  }
}
