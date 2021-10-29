<?php

function film_poster_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $poster = get_field('poster_url', $post_id);

  if( $poster ) {
    $output = '<div class="film-poster"><img src="' . $poster . '"></div>';
  }

  return $output;
}

add_shortcode('film_poster', 'film_poster_function');
