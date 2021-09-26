<?php

function film_director_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $directors = get_field('film_director', $post_id);

  if ($directors) {
    $output = '<span class="film-director">';
    foreach ($directors as $key=>$thisDirector) {
      $output .= $thisDirector->post_title;
    }
    $output .= '</span>';
  }

  return $output;
}

add_shortcode('film_director', 'film_director_function');
