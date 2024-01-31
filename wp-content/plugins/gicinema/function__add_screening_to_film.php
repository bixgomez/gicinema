<?php

function gicinema__add_screening_to_film($post_id, $screening) {

  echo '<div>Adding screening ('.$screening.') to ' . $post_id . '</div>';

  $field_key = 'field_617b2f8e4b8c4';

  $screening_array = array('screening' => $screening);

  // Add a row to the 'screenings' repeater field for the specified post
  $success = add_row($field_key, $screening_array, $post_id);

  if ($success) {
      echo 'Screening added successfully.';
  } else {
      echo 'Failed to add screening.';
  }
  
}
