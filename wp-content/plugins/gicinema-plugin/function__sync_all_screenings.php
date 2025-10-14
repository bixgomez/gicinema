<?php

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

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__sync_all_screenings()</div>';

  // Options (allow override via $opts or POST)
  $two_way            = isset($opts['two_way'])            ? (bool)$opts['two_way']            : (!empty($_POST['two_way']));
  $dry_run            = isset($opts['dry_run'])            ? (bool)$opts['dry_run']            : (!empty($_POST['dry_run']));
  $require_clean_acf  = isset($opts['require_clean_acf'])  ? (bool)$opts['require_clean_acf']  : (!empty($_POST['require_clean_acf']));
  $deactivate_missing = isset($opts['deactivate_missing']) ? (bool)$opts['deactivate_missing'] : (!empty($_POST['deactivate_missing']));

  // Preflight: if two-way and require clean ACF, verify no superfluous ACF screenings remain
  if ($two_way && $require_clean_acf && function_exists('gicinema__delete_superfluous_acf_screenings')) {
    $pre_q = new WP_Query([
      'post_type'      => 'film',
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'orderby'        => 'date',
      'order'          => 'DESC',
    ]);
    $offenders = [];
    if ($pre_q->have_posts()) {
      foreach ($pre_q->posts as $pid) {
        $res = gicinema__delete_superfluous_acf_screenings((int)$pid, true /* dry run */);
        $del = is_array($res) && isset($res['deleted']) ? (int)$res['deleted'] : 0;
        if ($del > 0) {
          $offenders[] = [ 'post_id' => (int)$pid, 'title' => get_the_title($pid), 'superfluous' => $del, 'edit' => get_edit_post_link($pid, '') ];
        }
      }
      wp_reset_postdata();
    }
    if (!empty($offenders)) {
      echo "<div class='notice notice-warning'><p><strong>Two-way sync aborted.</strong> Found films with superfluous ACF screenings. Please delete superfluous screenings first, then re-run.</p>";
      echo "<ul style='margin:0 0 0 18px; list-style:disc;'>";
      foreach ($offenders as $o) {
        $label = esc_html($o['title'] ?: ('Film #' . $o['post_id']));
        $edit  = $o['edit'] ? "<a href='" . esc_url($o['edit']) . "' target='_blank' rel='noopener'>edit</a>" : '';
        echo "<li>" . $label . " — <strong>" . (int)$o['superfluous'] . "</strong> superfluous " . ($edit ? "(" . $edit . ")" : '') . "</li>";
      }
      echo "</ul></div>";
      // Continue with ACF-only sync so page still provides value
    }
  }

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

      // The Loop
      while ($the_query->have_posts()) {
        echo '<div class="function-info">';

          $the_query->the_post();
          $post_link = get_permalink();
          $post_id = get_the_ID();
          $agile_id = get_field('agile_film_id');

          echo '<div>';
          echo 'Post ID ' . $post_id . ': ';
          echo '<a href="' . esc_url($post_link) . '" target="_blank">' .  get_the_title() . '</a> ';
          echo '(Posted ' . get_the_date('Y-m-d') . ')';
          echo '</div>';

          // Optionally perform two-way sync against the custom table using the ACF set
          if ($two_way) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'gi_screenings';

            // Current canonical table set and current ACF set (normalized)
            $table_vals = gicinema__get_screenings_from_table($post_id);
            $acf_vals   = gicinema__get_screenings_from_post($post_id);
            $table_set = [];
            foreach ((array)$table_vals as $v) { if (is_string($v) && $v !== '') $table_set[$v] = true; }
            $acf_set = [];
            foreach ((array)$acf_vals as $v) { if (is_string($v) && $v !== '') $acf_set[$v] = true; }

            // Compute additions and (optionally) deactivations
            $to_add = [];
            foreach ($acf_set as $val => $_t) {
              if (!isset($table_set[$val])) { $to_add[] = $val; }
            }
            $to_deactivate = [];
            if ($deactivate_missing) {
              foreach ($table_set as $val => $_t) {
                if (!isset($acf_set[$val])) { $to_deactivate[] = $val; }
              }
            }

            // Report intent
            echo '<div class="function-info">';
            if ($dry_run) {
              echo '<div><em>[dry]</em> two-way sync: would add ' . count($to_add) . ' to table' . ($deactivate_missing ? ('; would deactivate ' . count($to_deactivate)) : '') . '.</div>';
            } else {
              echo '<div>two-way sync: adding ' . count($to_add) . ' to table' . ($deactivate_missing ? ('; deactivating ' . count($to_deactivate)) : '') . '.</div>';
            }

            // Perform upserts (safe; respect unique key)
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
            echo '</div>';
          }

          // Always update ACF from canonical merge (table + ACF)
          gicinema__sync_screenings($post_id);

        echo '</div>';
      }

      /* Restore original Post Data 
      * NB: Because we are using new WP_Query we aren't stomping on the 
      * original $wp_query and it does not need to be reset with 
      * wp_reset_query(). We just need to reset the post data with 
      * wp_reset_postdata().
      */
      wp_reset_postdata();

  } else {

      // No posts found
      echo '<p>No films found.</p>';

  }

  echo '</div>';


}
