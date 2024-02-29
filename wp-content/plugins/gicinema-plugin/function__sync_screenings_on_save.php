<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__sync_screenings_on_save($post_id) {

  error_log('running gicinema__sync_screenings_on_save('.$post_id.')');

  global $wpdb;

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__sync_screenings_on_save($post_id)</div>';
    
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

  echo '<div class="function-info">';  
  echo '<div>This is on save, so let us prepare the incoming screenings value.</div>';
  $incoming_screenings = gicinema__get_saved_screenings_value($post_id);
  if (isset($incoming_screenings) && !empty($incoming_screenings) && is_array($incoming_screenings)) {
    echo "<div>The variable exists, has a value, and is an array.</div>";
    echo "<div><pre>";
    $incoming_screenings = gicinema__simplify_screenings_array($incoming_screenings);
    print_r($incoming_screenings);
    echo "</pre></div>";

    $screenings_to_delete = gicinema__compare_screening_arrays($incoming_screenings, $screenings_from_post);

    $arrayString_todelete = print_r($screenings_to_delete, true);
    error_log('$screenings_to_delete: ' . $arrayString_todelete);

    gicinema__remove_screenings($screenings_to_delete, $post_id );

    // TODO: Still must deal with the current film post, which just got that value re-inserted!

  } else {
    echo "<div>The variable does not meet all the conditions.</div>";
  }
  echo '</div>';

  echo '</div>';
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





function gicinema__compare_screening_arrays($array_before, $array_after) {
  $array_diff = array_values(array_diff($array_before, $array_after));
  error_log('running gicinema__compare_screening_arrays($array_1, $array_2)');
  $arrayString_before = print_r($array_before, true);
  $arrayString_after = print_r($array_after, true);
  $arrayString_diff = print_r($array_diff, true);  
  error_log('$array_before: ' . $arrayString_before);
  error_log('$array_after: ' . $arrayString_after);
  error_log('$array_diff: ' . $arrayString_diff);
  if (count($array_after) < count($array_before)) {
    return $array_diff;
  } else {
    return [];
  }
}





function gicinema__remove_screenings($to_delete, $post_id) {

  global $wpdb;

  error_log('-- remove screenings function --------------');
  $to_delete_string = print_r($to_delete, true);
  error_log('$to_delete: ' . $to_delete_string);
  error_log('--------------------------------------------');

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
