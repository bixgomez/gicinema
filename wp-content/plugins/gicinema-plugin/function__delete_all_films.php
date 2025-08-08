<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  function delete_all_film_posts() {
    // CSRF Protection - always required since this only runs from forms
    if (!isset($_POST['delete_films_nonce']) || !wp_verify_nonce($_POST['delete_films_nonce'], 'delete_all_films_action')) {
      return "Security check failed - unauthorized request";
    }
    // WP_Query arguments to fetch all 'film' post types
    $args = array(
      'post_type'      => 'film',
      'posts_per_page' => -1, // Retrieve all posts
      'fields'         => 'ids', // Only get post IDs to improve performance
    );

    // The Query
    $query = new WP_Query($args);

    // Check if there are any posts to delete
    if ($query->have_posts()) {
      // Loop through the posts and delete them
      foreach ($query->posts as $post_id) {
        wp_delete_post($post_id, true); // Set to true to bypass trash and permanently delete
      }
      return "All 'film' posts have been deleted.";
    } else {
      return "No 'film' posts found to delete.";
    }
  }
}
