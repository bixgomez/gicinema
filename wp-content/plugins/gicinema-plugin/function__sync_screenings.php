<?php
/**
 * Per-film ACF and custom-table screening synchronization.
 *
 * Loaded by function__sync_all_screenings.php and called for each Film during
 * manual all-film sync. It reads active screenings from gi_screenings, reads
 * the Film post's ACF "screenings" repeater, merges and normalizes the values,
 * applies timezone-shadow duplicate guards, and writes the resulting list back
 * to the ACF repeater field unless dry-run mode is requested.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema__sync_screenings($post_id, $dry_run = false, $echo_output = true) {
  // $agile_id_from_post = gicinema__get_agile_id_from_post($post_id);

  $screenings_from_table = gicinema__get_screenings_from_table($post_id);
  $screenings_from_post = gicinema__get_screenings_from_post($post_id);
  $merged_screenings = gicinema__merge_screenings_arrays($screenings_from_post, $screenings_from_table);

  $current_acf_values = gicinema__normalize_screening_list_for_comparison($screenings_from_post);
  $merged_values = gicinema__normalize_screening_list_for_comparison($merged_screenings);
  $added_to_acf = array_values(array_diff($merged_values, $current_acf_values));
  $removed_from_acf = array_values(array_diff($current_acf_values, $merged_values));
  $acf_changed = ($current_acf_values !== $merged_values);

  $summary = [
    'post_id' => $post_id,
    'dry_run' => $dry_run,
    'table_screenings' => array_values((array) $screenings_from_table),
    'acf_screenings' => array_values((array) $screenings_from_post),
    'merged_screenings' => array_values((array) $merged_screenings),
    'added_to_acf' => $added_to_acf,
    'removed_from_acf' => $removed_from_acf,
    'acf_changed' => $acf_changed,
    'acf_written' => false,
  ];

  if (!$dry_run) {
    gicinema__replace_all_screenings_in_post($merged_screenings, $post_id);
    $summary['acf_written'] = true;
  }

  if ($echo_output) {
    gicinema__render_sync_screenings_summary($summary);
  }

  return $summary;
}

function gicinema__normalize_screening_list_for_comparison($screenings) {
  $values = [];

  foreach ((array) $screenings as $screening) {
    if (is_string($screening) && $screening !== '') {
      $values[] = $screening;
    }
  }

  $values = array_values(array_unique($values));
  sort($values);

  return $values;
}

function gicinema__format_screening_for_admin($screening) {
  if (!is_string($screening) || $screening === '') {
    return '';
  }

  // Normalize the screening value using strict parser
  $normalized = gicinema__parse_screening_datetime($screening, 'admin_display');
  if (!$normalized) {
    return $screening; // Return raw value if parsing failed
  }

  $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(get_option('timezone_string') ?: 'UTC');
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $normalized, $tz);

  if ($dt instanceof DateTime) {
    return wp_date('D, M j, Y g:i a', $dt->getTimestamp(), $tz);
  }

  return $screening;
}

function gicinema__render_screening_details($screenings) {
  $screenings = array_values((array) $screenings);
  $count = count($screenings);

  if ($count === 0) {
    echo '0';
    return;
  }

  $summary = $count === 1 ? '1 screening' : $count . ' screenings';

  echo '<details>';
  echo '<summary>' . esc_html($summary) . '</summary>';
  echo '<ul class="ul-disc">';
  foreach ($screenings as $screening) {
    echo '<li><code>' . esc_html($screening) . '</code><br><span class="description">' . esc_html(gicinema__format_screening_for_admin($screening)) . '</span></li>';
  }
  echo '</ul>';
  echo '</details>';
}

function gicinema__get_sync_screenings_action_label($summary) {
  $changed = !empty($summary['acf_changed']);
  $dry_run = !empty($summary['dry_run']);
  $added = isset($summary['added_to_acf']) ? count($summary['added_to_acf']) : 0;
  $removed = isset($summary['removed_from_acf']) ? count($summary['removed_from_acf']) : 0;

  if ($dry_run) {
    $label = $changed ? 'Would rewrite ACF' : 'No ACF changes needed';
  } else {
    $label = $changed ? 'Rewrote ACF' : 'Rewrote ACF; values already matched';
  }

  if ($added || $removed) {
    $label .= ' (' . $added . ' added, ' . $removed . ' removed)';
  }

  return $label;
}

function gicinema__render_sync_screenings_summary($summary) {
  echo '<div class="notice notice-info inline">';
  echo '<p><strong>ACF screenings:</strong> ' . esc_html(gicinema__get_sync_screenings_action_label($summary)) . '.</p>';
  echo '<ul class="ul-disc">';
  echo '<li>Active custom-table rows: ' . count($summary['table_screenings']) . '</li>';
  echo '<li>Current ACF rows: ' . count($summary['acf_screenings']) . '</li>';
  echo '<li>Resulting ACF rows: ' . count($summary['merged_screenings']) . '</li>';
  echo '</ul>';
  echo '</div>';
}





function gicinema__get_agile_id_from_post($post_id) {
  $args = array(
    'post_type' => 'film',
    'posts_per_page' => 1,
    'p' => $post_id,
  );

  $query = new WP_Query($args);

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $agile_id = get_field('agile_film_id', $post_id);
      return $agile_id;
    }
  }
}





function gicinema__get_screenings_from_post($post_id) {

  $args = array(
    'post_type' => 'film',
    'posts_per_page' => 1,
    'p' => $post_id,
  );

  // The Query
  $query = new WP_Query($args);

  // The Loop
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
    
      // Initialize an array to hold the screenings data
      $screenings_data = array();

      if(have_rows('screenings', $post_id)) {
        
        // Loop through each row
        while(have_rows('screenings', $post_id)) {
          the_row();
          
          // Directly access sub-field values
          $screeningString = get_sub_field('screening');

          // Normalize to Y-m-d H:i:s in the WP timezone using strict parser
          $formatted = gicinema__parse_screening_datetime($screeningString, 'acf_read');

          if ($formatted) {
            $screenings_data[] = $formatted;
          }
        }
      }

      // Return the post data array
      return $screenings_data;
    }
  }
}





function gicinema__get_screenings_from_table($post_id) {
  global $wpdb;    
  $table_name = $wpdb->prefix . 'gi_screenings';

  // Prepare the SQL query to get all screening values for a given post ID.
  $query = $wpdb->prepare(
    "SELECT DISTINCT screening FROM {$table_name} WHERE post_id = %d AND status = 1 ORDER BY screening ASC",
    $post_id
  );

  // Execute the query and get the results.
  return $wpdb->get_col($query);
}





function gicinema__merge_screenings_arrays($array_1, $array_2) {
  // array_1: ACF screenings (normalized strings 'Y-m-d H:i:s' in WP timezone)
  // array_2: Custom table screenings (normalized strings 'Y-m-d H:i:s' in WP timezone)

  // Build a lookup set for the canonical (table) screenings.
  $table_set = [];
  foreach ((array) $array_2 as $val) {
    if (is_string($val) && $val !== '') {
      $table_set[$val] = true;
    }
  }

  // Timezone-shadow guard (defense-in-depth):
  // If an ACF screening equals a table screening plus/minus the WP timezone
  // offset at that date/time (e.g., +7h PDT or +8h PST), treat it as a
  // timezone artifact and skip it. This prevents the recurring “phantom twin”
  // showtimes at +/- 7/8 hours from being merged in repeatedly.
  //
  // Toggle: Set define('GICINEMA_TZ_SHADOW_GUARD', false) in wp-config.php to disable,
  // or use filter add_filter('gicinema_enable_tz_shadow_guard', '__return_false').
  $enable_guard = true;
  if (defined('GICINEMA_TZ_SHADOW_GUARD') && GICINEMA_TZ_SHADOW_GUARD === false) {
    $enable_guard = false;
  }
  if (function_exists('apply_filters')) {
    $enable_guard = apply_filters('gicinema_enable_tz_shadow_guard', $enable_guard);
  }

  $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(get_option('timezone_string') ?: 'UTC');

  $accepted_acf = [];
  foreach ((array) $array_1 as $v) {
    if (!is_string($v) || $v === '') {
      continue;
    }

    // If exact match exists in table, keep it (no need to guard).
    if (isset($table_set[$v])) {
      $accepted_acf[] = $v;
      continue;
    }

    // Guard against timezone-shadow duplicates only if enabled.
    if ($enable_guard) {
      $ts = strtotime($v);
      if ($ts) {
        try {
          $dt = new DateTime($v, $tz);
          $offset = $tz->getOffset($dt); // seconds (e.g., 25200 for PDT, 28800 for PST)
        } catch (Exception $e) {
          $offset = 0;
        }
        if ($offset) {
          $plus  = date('Y-m-d H:i:s', $ts + $offset);
          $minus = date('Y-m-d H:i:s', $ts - $offset);
          if (isset($table_set[$plus]) || isset($table_set[$minus])) {
            // Skip ACF value that is merely a timezone-shifted duplicate of a table value.
            // To disable this behavior, see toggle notes above.
            continue;
          }
        }
      }
    }

    // Otherwise accept this ACF value.
    $accepted_acf[] = $v;
  }

  // Merge canonical table values with accepted ACF values, unique, sort.
  $merged = array_merge($accepted_acf, array_keys($table_set));
  $unique = array_values(array_unique($merged));
  sort($unique);
  return $unique;
}





function gicinema__replace_all_screenings_in_post($new_screenings, $post_id) {
  // Prepare the array to update the repeater field
  $screenings_to_update = [];
  foreach ($new_screenings as $date) {
      $screenings_to_update[] = ['screening' => $date];
  }

  // Update the repeater field with the new array of screenings
  // Replace 'screenings' with your actual repeater field name
  update_field('screenings', $screenings_to_update, $post_id);
}



/* We are no longer doing this. To be deleted in time. */
/*
function gicinema__replace_all_screenings_in_table($new_screenings, $post_id, $agile_id) {

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__replace_all_screenings_in_table($new_screenings, $post_id, $agile_id)</div>';
  echo '<div>Replacing all screenings in custom screenings table</div>';

  global $wpdb;    
  $table_name = $wpdb->prefix . 'gi_screenings';

  foreach ($new_screenings as $screening) {

    $screening = sanitize_text_field($screening);

    // Splitting screening into separate date and time strings
    list($screening_date, $screening_time) = explode(" ", $screening);

    $screening_date = sanitize_text_field($screening_date);
    $screening_time = sanitize_text_field($screening_time);

    echo '<div class="function-info">';
    echo '<div><pre>' . $screening . ' | ' . $screening_date . ' | ' . $screening_time . '</pre></div>';
    echo '</div>';
    
    // Query to check if the row exists
    $query = $wpdb->prepare(
      "SELECT * FROM {$table_name} WHERE film_id = %d AND post_id = %d AND screening = %s AND screening_date = %s AND screening_time = %s AND status = 1 LIMIT 1",
      $agile_id, $post_id, $screening, $screening_date, $screening_time
    );
    
    // Execute the query
    $row_exists = $wpdb->get_row($query);

    // Check if row exists
    echo '<div>If row does not exist, create it.</div>';
    if (is_null($row_exists)) {
      echo '<div class="failure">This record does not exist in the custom table; inserting new row.</div>';
      $wpdb->insert(
          $table_name,
          array(
              'film_id' => $agile_id,
              'post_id' => $post_id,
              'screening' => $screening,
              'screening_date' => $screening_date,
              'screening_time' => $screening_time,
          ),
          array('%d', '%d', '%s', '%s', '%s') // Specify the format of each column value
      );
    } else {
      echo '<div class="success">This record already exists in the custom table skipping.</div>';
    }
  }

  echo '</div>';
}
*/
