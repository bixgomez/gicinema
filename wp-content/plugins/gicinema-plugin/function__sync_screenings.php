<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__sync_screenings($post_id) {

  global $wpdb;

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__sync_screenings($post_id)</div>';
    
  $table_name = $wpdb->prefix . 'gi_screenings';

  echo '<div>' . $post_id . '</div>';

  $agile_id_from_post = gicinema__get_agile_id_from_post($post_id);

  $screenings_from_post = gicinema__get_screenings_from_post($post_id);

  echo '<div class="function-info">';
  echo '<div>Array of screenings from ACF repeater field:</div>';
  echo '<pre>';
  print_r($screenings_from_post);
  echo '</pre>';
  echo '</div>';

  $screenings_from_table = gicinema__get_screenings_from_table($post_id);

  echo '<div class="function-info">';
  echo '<div>Array of screenings from custom table:</div>';
  echo '<pre>';
  print_r($screenings_from_table);
  echo '</pre>';
  echo '</div>';

  $merged_screenings = gicinema__merge_screenings_arrays($screenings_from_post, $screenings_from_table);

  echo '<div class="function-info">';
  echo '<div>Array of merged screenings from both sources:</div>';
  echo '<pre>';
  print_r($merged_screenings);
  echo '</pre>';
  echo '</div>';

  gicinema__replace_all_screenings_in_post($merged_screenings, $post_id);
  gicinema__replace_all_screenings_in_table($merged_screenings, $post_id, $agile_id_from_post);

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
          
          // Attempt to convert the date
          $screeningDateTime = DateTime::createFromFormat('m/d/Y g:i a', $screeningString);
          
          // Check if the DateTime object was successfully created
          if ($screeningDateTime !== false) {
              // If successful, format the date
              $formattedScreening = $screeningDateTime->format('Y-m-d H:i:s');
          } else {
              // Handle the error, such as logging or using a default value
              error_log("Failed to convert screening date: " . $screeningString);
              $formattedScreening = 'Invalid date'; // Example error handling
          }
          
          // Add the formatted 'screening' data to the array, or an error message
          $screenings_data[] = $formattedScreening;
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
    "SELECT screening FROM {$table_name} WHERE post_id = %d AND status = 1",
    $post_id
  );

  // Execute the query and get the results.
  return $wpdb->get_col($query);
}





function gicinema__merge_screenings_arrays($array_1, $array_2) {
  // Merge the two arrays
  $merged_screenings = array_merge($array_1, $array_2);

  // Remove duplicates
  $unique_screenings = array_unique($merged_screenings);

  // Sort the dates
  sort($unique_screenings);

  // Output the result
  return $unique_screenings;
}





function gicinema__replace_all_screenings_in_post($new_screenings, $post_id) {

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__replace_all_screenings_in_post($new_screenings, $post_id)</div>';

  // Prepare the array to update the repeater field
  $screenings_to_update = [];
  foreach ($new_screenings as $date) {
      $screenings_to_update[] = ['screening' => $date];
  }

  // Update the repeater field with the new array of screenings
  // Replace 'screenings' with your actual repeater field name
  update_field('screenings', $screenings_to_update, $post_id);
  
  echo '</div>';
}





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
