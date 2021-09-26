<?php

// Custom post types.
include('cinema-posttypes/posttype--film.php');
include('cinema-posttypes/posttype--director.php');
include('cinema-posttypes/posttype--format.php');
include('cinema-posttypes/posttype--series.php');
include('cinema-posttypes/posttype--country.php');
include('cinema-posttypes/posttype--alerts.php');

// Shortcodes.
include('cinema-shortcodes/shortcode--film-director.php');
include('cinema-shortcodes/shortcode--film-length.php');
include('cinema-shortcodes/shortcode--film-year.php');
include('cinema-shortcodes/shortcode--film-format.php');
include('cinema-shortcodes/shortcode--film-country.php');
include('cinema-shortcodes/shortcode--film-showtimes.php');
include('cinema-shortcodes/shortcode--film-poster.php');
include('cinema-shortcodes/shortcode--film-trailer.php');
include('cinema-shortcodes/shortcode--film-teaser.php');
include('cinema-shortcodes/shortcode--film-teasers.php');

// Custom functions.
include('cinema-functions/function--max-screenings.php');
include('cinema-functions/function--last-screening.php');

/**
 * If more than one page exists, return TRUE.
 */
function is_paginated() {
  global $wp_query;
  if ( $wp_query->max_num_pages > 1 ) {
    return true;
  } else {
    return false;
  }
}

/**
 * If last post in query, return TRUE.
 */
function is_last_post($wp_query) {
  $post_current = $wp_query->current_post + 1;
  $post_count = $wp_query->post_count;
  if ( $post_current == $post_count ) {
    return true;
  } else {
    return false;
  }
}
