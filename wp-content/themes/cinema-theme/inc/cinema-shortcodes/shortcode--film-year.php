<?php

function film_year_function($atts = [], $tag = '') {
  $output = '';
  $post_id = get_the_ID();
  $year = get_field('film_year', $post_id);

  if ($year) {
    $output = '<span class="film-year">' . $year . '</span>';
  }

  return $output;
}

add_shortcode('film_year', 'film_year_function');
