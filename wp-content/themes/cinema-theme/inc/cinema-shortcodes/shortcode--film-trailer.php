<?php

function film_trailer_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $trailer = get_field('trailer_link', $post_id);

  if( $trailer ) {
    $output = '<a class="film-trailer" href="' . $trailer . '" target="_blank">View Trailer</a>';
  }

  return $output;
}

add_shortcode('film_trailer', 'film_trailer_function');
