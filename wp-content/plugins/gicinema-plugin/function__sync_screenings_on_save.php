<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__sync_screenings_on_save($post_id, $agile_id, $acf_screenings_array) {

  // error_log('');
  // error_log('* * * * START * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *');
  // error_log('');
  // error_log('FUNCTION gicinema__sync_screenings_on_save');
  // error_log('We are working with post_id ' . $post_id);
  // error_log('');

  // error_log('(1) get the ACF screenings array (and simplify/convert it to the structure we neeed)');
  $acf_screenings_array = gicinema__simplify_screenings_array( $acf_screenings_array );

  // error_log(print_r($acf_screenings_array, true));

  // error_log('(2) update the custom table, setting the status to 0 for ALL the screenings for');
  // error_log('    this post_id');

  gicinema__disable_all_screenings($post_id);

  // error_log('(3) loop through the array of screenings, setting the status to 1 for all the');
  // error_log('    screenings in the custom table that match this post_id and screening');

  // error_log('(4) add any NEW screenings in the ACF screenings array to the custom table');

  foreach ($acf_screenings_array as $screening) {
    gicinema__update_screenings_table_row($screening, $post_id, $agile_id);
  }

  // error_log('');
  // error_log('* * * * END * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *');
  // error_log('');
}




function gicinema__get_saved_screenings_value($post_id) {
  // Attempt to retrieve the transient value.
  $incoming_screenings = get_transient('gicinema__screenings_value_' . $post_id);

  // Check if anything was returned.
  if ($incoming_screenings !== false) {
      // Transient was found and returned.
      return $incoming_screenings;
  } else {
      // Transient not found or expired, handle this case as needed.
      return null; // Or handle as required for your application logic.
  }
}





function gicinema__simplify_screenings_array($originalArray) {
  $simplifiedArray = [];
  foreach ($originalArray as $item) {
      $date = DateTime::createFromFormat('m/d/Y g:i a', $item['screening']);
      $simplifiedArray[] = $date->format('Y-m-d H:i:s');
  }
  return $simplifiedArray;
}




function gicinema__disable_all_screenings($post_id) {
  global $wpdb;
  $query = $wpdb->prepare("UPDATE wp_gi_screenings SET status = 0 WHERE post_id = %d", $post_id);
  $wpdb->query($query);
}



function gicinema__update_screenings_table_row($screening, $post_id, $agile_id) {
  // error_log($post_id);
  // error_log($agile_id);
  // error_log($screening);

  // Splitting screening into separate date and time strings
  list($screening_date, $screening_time) = explode(" ", $screening);

  // error_log($screening_date);
  // error_log($screening_time);

  global $wpdb;
  $table_name = $wpdb->prefix . 'gi_screenings';

  // Step 1: Check for a matching row
  $exists = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name WHERE post_id = %d AND film_id = %d AND screening = %s",
    $post_id,
    $agile_id,
    $screening
  ));

  // Step 2: If found, update the row
  if ($exists) {
    $wpdb->update(
        $table_name,
        ['status' => 1], // column to update
        [ // WHERE conditions
            'post_id' => $post_id,
            'film_id' => $agile_id,
            'screening' => $screening
        ],
        ['%d'], // format of the value to update
        ['%d', '%d', '%s'] // format of the conditions
    );
  } else {
    // Step 3: If not found, insert a new row
    $wpdb->insert(
        $table_name,
        [
            'post_id' => $post_id,
            'film_id' => $agile_id,
            'screening' => $screening,
            'screening_date' => $screening_date,
            'screening_time' => $screening_time,
            'status' => 1
        ],
        ['%d', '%d', '%s', '%s', '%s', '%d'] // format of each value
    );
  }
}




function gicinema__remove_screenings($to_delete, $post_id) {

  global $wpdb;

  // error_log('-- remove screenings function --------------');
  $to_delete_string = print_r($to_delete, true);
  // error_log('$to_delete: ' . $to_delete_string);
  // error_log('--------------------------------------------');

  foreach ($to_delete as $screening_date) {
    // Prepare the query to avoid SQL injection
    $query = $wpdb->prepare(
        "DELETE FROM wp_gi_screenings WHERE post_id = %d AND screening = %s",
        $post_id,
        $screening_date
    );

    // Execute the query
    $result = $wpdb->query($query);

    // Optional: Check result, could be useful for debugging or logging
    if($result === false) {
        // Handle error, query failed
        echo "Error in deleting screening date: $screening_date";
    } else {
        // Success, you can also check if $result > 0, meaning rows were deleted
        echo "Successfully deleted screening date: $screening_date";
    }
  }
}
