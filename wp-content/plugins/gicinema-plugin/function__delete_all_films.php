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

    $total = is_array($query->posts) ? count($query->posts) : 0;
    if ($total === 0) {
      return "No 'film' posts found to delete.";
    }

    $deleted = 0;
    foreach ($query->posts as $post_id) {
      $res = wp_delete_post($post_id, true); // true: bypass trash
      if ($res) { $deleted++; }
    }
    $failed = $total - $deleted;
    return "Deleted {$deleted} of {$total} 'film' post" . ($total === 1 ? '' : 's') . ($failed > 0 ? "; {$failed} failed" : '') . ".";
  }
}
