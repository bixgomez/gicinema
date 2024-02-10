<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__delete_all_screenings_for_film($post_id) {

  echo '<div>Deleting all screenings for ' . $post_id . '</div>';
  update_field('screenings', array(), $post_id);
  
}
