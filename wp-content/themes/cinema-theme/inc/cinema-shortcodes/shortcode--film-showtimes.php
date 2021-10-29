<?php

function showtimes_function($atts = [], $tag = '') {

  $output = '';
  $output .= '<h4 class="screenings--title">Showtimes</h4>';

  $post_id = get_the_ID();

  $screenings = get_field('film_screenings', $post_id);

  if ($screenings) {
    $output .= '<div class="screenings">' . $screenings . '</table>';
  }

  else {
    $output .= '<p>TBA</p>';
  }

  return $output;
}

add_shortcode('film_showtimes', 'showtimes_function');
