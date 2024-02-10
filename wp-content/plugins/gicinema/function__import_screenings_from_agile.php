<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__dedupe_screenings_table.php";

function import_screenings_from_agile(
  $agile_array = null,
  $repeater_field_key = null,
  $repeater_field_name = null,
  $repeater_subfield_name = null,
  $post_id = 0,
  $agile_id = 0 ) {

  if ( $agile_array===null || $repeater_field_name === null || $repeater_field_key === null || $post_id===0) {
    return;
  }

  echo '<div style="background-color:#fefefe;padding:10px;margin: 0 0 12px;max-height:150px;overflow-y:scroll;"><b>Importing Screenings</b><br><br>';
  echo '<i>$repeater_field_key:</i> ' . $repeater_field_key . '<br>';
  echo '<i>$repeater_field_name:</i> ' . $repeater_field_name . '<br>';
  echo '<i>$repeater_subfield_name:</i> ' . $repeater_subfield_name . '<br>';
  echo '<i>$post_id:</i> ' . $post_id . '<br><br>';

  $new_screenings = [];
  $existing_screenings = [];
  $all_screenings = [];

  // Check if the "screenings" repeater field has rows of data.
  echo '<i>Checking if the "screenings" repeater field has rows of data</i><br><br>';
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

  echo '<i>Now, check the custom table for screening data!</i><br><br>';

  global $wpdb;

  $table_name = $wpdb->prefix . 'gi_screenings';

  foreach ($all_screenings as $screening) {

    // Prepare the SQL query to check if the row exists
    echo '<i>Checking the custom table for screening=' . $screening . ' and film_id($agile_id)=' . $agile_id . ' and post_id=' . $post_id . '</i><br>';
    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE screening = %s AND film_id = %d AND post_id = %d",
        $screening,
        $agile_id,
        $post_id
    );

    // Execute the query
    $exists = $wpdb->get_var($query);

    // If the row doesn't exist, insert it
    if ($exists == 0) {
        echo '<i>The row doesn\'t exist; insert it.</i><br><br>';
        $wpdb->insert(
            $table_name,
            array(
                'screening' => $screening,
                'film_id' => $agile_id,
                'post_id' => $post_id
            ),
            array(
                '%s', // placeholder for 'screening' field
                '%d', // placeholder for 'film_id' field
                '%d'  // placeholder for 'post_id' field
            )
        );
    } else {
      echo '<i>The row exists; ignoring.</i><br><br>';
    }

    echo '<i>Run the custom table deduper.</i><br><br>';
    function__dedupe_screenings_table();
  }

  echo '</div>';

}

// Custom comparison function for sorting by date
function compareDates($a, $b) {
  return strtotime($a) - strtotime($b);
}