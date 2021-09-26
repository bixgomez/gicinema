<?php

function film_poster_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $poster = get_field('film_poster', $post_id);

  if( $poster ) {
    $size = 'medium'; // (thumbnail, medium, large, full or custom size)
    $output = '<div class="film-poster">' . wp_get_attachment_image( $poster, $size ) . '</div>';
  }

  return $output;
}

add_shortcode('film_poster', 'film_poster_function');
