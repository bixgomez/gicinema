<?php 

function add_screening_to_existing_film($post_id, $screening) {

  echo 'FUNCTION add_screening_to_existing_film()<br>';
  echo '$post_id = ' . $post_id . '<br>';
  echo '$screening = ' . $screening . '<br>';

  // Get all the rows from the 'screenings' repeater field.
  $screenings = get_field('screenings', $post_id);

  // Check if there are any screenings.
  if ($screenings) {
    // Calculate the total number of rows in the repeater field.
    $total_rows = count($screenings);

    // Loop through each screening in reverse order.
    for ($index = $total_rows; $index > 0; $index--) {
        // Access the screening row at the current index.
        // Note: ACF uses 1-based indexing for rows, so no need to adjust $index.
        $screening_row = $screenings[$index - 1]; // Adjust for 0-based index of the array.

        // Check if this row's 'screening' sub field matches the $screening value.
        if ($screening_row['screening'] === $screening) {
            // Delete the matching row.
            delete_row('screenings', $index, $post_id);
        }
    }
  }

  // Prepare the new row to be added.
  $new_row = array('screening' => $screening);

  // Add the new row to the 'screenings' repeater field for the specified post.
  if (function_exists('add_row')) {
      // The add_row function returns the row number on success, false on failure.
      $success = add_row('screenings', $new_row, $post_id);
      if ($success) {
          echo 'New screening added successfully.';
      } else {
          echo 'Failed to add new screening.';
      }
  } else {
      echo 'ACF functions not available. Make sure ACF is installed and activated.';
  }

  echo '<br>';
}