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
      echo '<i>The row exists; set status to active (1).</i>';
      $wpdb->update(
        $table_name,
        array(
            'status' => 1,
        ),
        array( 
            'screening' => $screening,
            'post_id' => $post_id,
        ),
        array(
            '%d',
        ),
        array(
            '%s',
            '%d'
        )
      );
    }

    echo '</div>';
  }

  echo '</div>';

  echo '</div>';

}
