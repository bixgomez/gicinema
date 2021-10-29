<?php

function film_format_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $formats = get_field('format', $post_id);

//    No longer relationship field, but might be in the future.
//  if ($formats) {
//    $output = '<span class="film-format">';
//    foreach ($formats as $key=>$thisformat) {
//      $output .= $thisformat->post_title;
//      if ( (count($formats) > 1) && ( (count($formats) - 1) > $key ) ) {
//        $output .= ', ';
//      }
//    }
//    $output .= '</span>';
//  }

    if ($formats) {
        $output = '<span class="film-format">' . $formats . '</span>';
    }

  return $output;
}

add_shortcode('film_format', 'film_format_function');
