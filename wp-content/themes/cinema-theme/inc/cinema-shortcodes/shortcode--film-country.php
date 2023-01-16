<?php

function film_country_function($atts = [], $tag = '') {

  $output = '';
  $post_id = get_the_ID();
  $countries = get_field('country', $post_id);

    if ($countries) {
        $output = '<span class="film-format">' . $countries . '</span>';
    }

  return $output;
}

add_shortcode('film_country', 'film_country_function');
