<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

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
  echo '<h4>Importing Screenings</h4>';

  echo '<div class="function-info">';

  $agile_screenings = [];

  foreach( $agile_array as $screening ) :
    $screening_datetime = date('Y-m-d H:i:s', strtotime($screening->StartDate));
    $agile_screenings[] = $screening_datetime;
  endforeach;

  echo '<i>$agile_screenings:</i><pre>';
  print_r($agile_screenings);
  echo '</pre>';

  // Get the time zone setting from WordPress options
  $wpTimeZone = get_option('timezone_string');
  
  // Set the time zone
  date_default_timezone_set($wpTimeZone);

  echo '<i>$wpTimeZone:</i> ' . $wpTimeZone . '<br>';

  // Now it is time to update the custom screenings table (which we still need).
  echo '<i>Now, check the custom table for screening data!</i>';

  global $wpdb;

  $table_name = $wpdb->prefix . 'gi_screenings';

  foreach ($agile_screenings as $screening) {

    echo '<div class="function-info">';
    echo '<i>Processing screening=' . $screening . ' for film_id=' . $agile_id . ' and post_id=' . $post_id . '</i>';

    // Splitting screening into separate date and time strings
    list($screening_date, $screening_time) = explode(" ", $screening);

    echo '<div>';
    echo '<b>Screening: </b>' . $screening . '<br>';
    echo '<b>Screening date: </b>' . $screening_date . '<br>';
    echo '<b>Screening time: </b>' . $screening_time;
    echo '</div>';

    // Single query replaces the SELECT + INSERT/UPDATE logic
    $result = $wpdb->query($wpdb->prepare(
      "INSERT INTO $table_name 
         (screening, screening_date, screening_time, film_id, post_id, status) 
         VALUES (%s, %s, %s, %d, %d, 1)
         ON DUPLICATE KEY UPDATE status = 1",
      $screening,
      $screening_date,
      $screening_time,
      $agile_id,
      $post_id
    ));

    echo '<i>Database operation completed.</i>';
    echo '</div>';
  }

  echo '</div>';

  echo '</div>';

}
