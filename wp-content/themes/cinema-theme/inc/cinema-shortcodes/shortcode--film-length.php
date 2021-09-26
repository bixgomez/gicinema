<?php

function film_length_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $length = get_field('film_length', $post_id);

  if ($length) {
    $output = '<span class="film-length">' . $length . '</span>';
  }

  return $output;
}

add_shortcode('film_length', 'film_length_function');
