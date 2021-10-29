<?php

function film_country_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $countries = get_field('country', $post_id);

//  No longer a relationship, but might be in the future.
//  if ($countries) {
//    $output = '<span class="film-country">';
//    foreach ($countries as $key=>$thiscountry) {
//      $output .= $thiscountry->post_title;
//      if ( (count($countries) > 1) && ( (count($countries) - 1) > $key ) ) {
//        $output .= '-';
//      }
//    }
//    $output .= '</span>';
//  }

    if ($countries) {
        $output = '<span class="film-format">' . $countries . '</span>';
    }

  return $output;
}

add_shortcode('film_country', 'film_country_function');
