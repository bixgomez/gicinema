<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Define the function that imports screenings from agile for each film.
function gicinema__import_screenings_from_agile (
  $agile_array = null,
  $repeater_field_key = null,
  $repeater_field_name = null,
  $repeater_subfield_name = null,
  $post_id = 0,
  $agile_id = 0 ) {

  if ( $agile_array===null || $repeater_field_name === null || $repeater_field_key === null || $post_id===0) {
    return;
  }

  echo '<div class="function-info scrolly">';
  echo '<h4>Importing Screenings</b></h4>';

  echo '<div class="function-info">';
  echo '<div>';
  echo '<i>$repeater_field_key:</i> ' . $repeater_field_key . '<br>';
  echo '<i>$repeater_field_name:</i> ' . $repeater_field_name . '<br>';
  echo '<i>$repeater_subfield_name:</i> ' . $repeater_subfield_name . '<br>';
  echo '<i>$post_id:</i> ' . $post_id;
  echo '</div>';
  echo '</div>';

  $new_screenings = [];
  $existing_screenings = [];
  $all_screenings = [];

  // Check if the "screenings" repeater field has rows of data.
  echo '<i>Checking if the "screenings" ACF repeater field has rows of data</i>';
  if (have_rows($repeater_field_name, $post_id)) :
    while (have_rows($repeater_field_name, $post_id)) : the_row();    
      // Use get_sub_field with the second parameter as false to avoid formatting
      $screening_value = get_sub_field($repeater_subfield_name, false);
      $existing_screenings[] = $screening_value;
      echo $screening_value . '<br>';  
    endwhile;  
  endif;

  foreach( $agile_array as $screening ) :
    $screening_datetime = date('Y-m-d H:i:s', strtotime($screening->StartDate));
    $new_screenings[] = $screening_datetime;
  endforeach;

  echo '<i>$existing_screenings:</i><pre>';
  print_r($existing_screenings);
  echo '</pre>';

  echo '<i>$new_screenings:</i><pre>';
  print_r($new_screenings);
  echo '</pre>';

  // Get the time zone setting from WordPress options
  $wpTimeZone = get_option('timezone_string');
  
  // Set the time zone
  date_default_timezone_set($wpTimeZone);

  // Get the current date and time
  $currentTime = time();

  echo '<i>$currentTime:</i> ' . $currentTime . '<br>';
  echo "Current time: " . date('Y-m-d H:i:s', $currentTime).'<br><br>';

  // Sort the array by date
  usort($existing_screenings, 'compareDates');

  // Remove duplicate values
  $existing_screenings_unique = array_unique($existing_screenings);

  // Filter out dates greater than the present date & time
  $existing_screenings_filtered = array_filter($existing_screenings_unique, function ($date) use ($currentTime) {
    return strtotime($date) <= $currentTime;
  });

  // Reset array keys to maintain sequential indexing
  $existing_screenings_filtered = array_values($existing_screenings_filtered);

  // Output the result
  echo '<i>$existing_screenings_filtered:</i><pre>';
  print_r($existing_screenings_filtered);
  echo '</pre>';

  $all_screenings = array_merge($existing_screenings_filtered, $new_screenings);

  echo '<i>$all_screenings:</i><pre>';
  print_r($all_screenings);
  echo '</pre>';

  // Check if the ACF function exists
  if (function_exists('update_field')) {
    // Clear existing values in the repeater field
    delete_field('screenings', $post_id);

    // Loop through the date/time array and add values to the repeater field
    foreach ($all_screenings as $date_time) {
        add_row('screenings', array('screening' => $date_time), $post_id);
    }
  }

  // Now it is time to update the custom screenings table (which we still need).
  echo '<i>Now, check the custom table for screening data!</i>';

  global $wpdb;

  $table_name = $wpdb->prefix . 'gi_screenings';

  foreach ($all_screenings as $screening) {

    echo '<div class="function-info">';

    // Prepare the SQL query to check if the row exists
    echo '<i>Checking the custom table for screening=' . $screening . ' and film_id($agile_id)=' . $agile_id . ' and post_id=' . $post_id . '</i>';
    
    // Splitting screening into separate date and time strings
    list($screening_date, $screening_time) = explode(" ", $screening);

    echo '<div>';
    echo '<b>Screening: </b>' . $screening . '<br>';
    echo '<b>Screening date: </b>' . $screening_date . '<br>';
    echo '<b>Screening time: </b>' . $screening_time;
    echo '</div>';
    
    $query = $wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $table_name 
         WHERE screening = %s
         AND screening_date = %s
         AND screening_time = %s
         AND film_id = %d 
         AND post_id = %d",
        $screening,
        $screening_date,
        $screening_time,
        $agile_id,
        $post_id
    );

    // Execute the query
    $exists = $wpdb->get_var($query);

    // If the row doesn't exist, insert it
    if ($exists == 0) {
        echo '<i>The row doesn\'t exist; insert it.</i>';
        $wpdb->insert(
            $table_name,
            array(
              'screening' => $screening,
              'screening_date' => $screening_date,
              'screening_time' => $screening_time,
              'film_id' => $agile_id,
              'post_id' => $post_id
            ),
            array(
              '%s', // placeholder for 'screening' field
              '%s', // placeholder for 'screening_date' field
              '%s', // placeholder for 'screening_time' field
              '%d', // placeholder for 'film_id' field
              '%d'  // placeholder for 'post_id' field
            )
        );
    } else {
      echo '<i>The row exists; ignoring.</i>';
    }

    echo '</div>';
  }

  echo '</div>';

}

// Custom comparison function for sorting by date
function compareDates($a, $b) {
  return strtotime($a) - strtotime($b);
}