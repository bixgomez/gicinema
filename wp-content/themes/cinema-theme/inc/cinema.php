<?php

// Custom post types.
include('posttypes/posttype--film.php');
include('posttypes/posttype--director.php');
include('posttypes/posttype--format.php');
include('posttypes/posttype--series.php');
include('posttypes/posttype--country.php');
include('posttypes/posttype--alerts.php');

// Shortcodes.
include('shortcodes/shortcode--film-teaser.php');

// Custom functions.
include('functions/function__max_screenings.php');
include('functions/function__last_screening.php');

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
