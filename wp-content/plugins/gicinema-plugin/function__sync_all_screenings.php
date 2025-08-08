<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__dedupe_screenings_table.php";
require_once "function__sync_screenings.php";

function gicinema__sync_all_screenings() {
  // CSRF Protection - only when called via admin form
  if (isset($_POST['confirm_import'])) {
    if (!isset($_POST['sync_nonce']) || !wp_verify_nonce($_POST['sync_nonce'], 'sync_screenings_action')) {
      echo '<div class="notice notice-error"><p>Security check failed</p></div>';
      return;
    }
  }

  gicinema__dedupe_screenings_table();

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__sync_all_screenings()</div>';

  // Arguments for the query
  $args = array(
    'post_type' => 'film', // Your custom post type name
    'posts_per_page' => -1, // Retrieve all posts
    'orderby' => 'date', // Order by date
    'order' => 'DESC' // Descending order
  );

  // The Query
  $the_query = new WP_Query($args);

  // Check if the Query returns any posts
  if ($the_query->have_posts()) {

      // The Loop
      while ($the_query->have_posts()) {
        echo '<div class="function-info">';

          $the_query->the_post();
          $post_link = get_permalink();
          $post_id = get_the_ID();
          $agile_id = get_field('agile_film_id');

          echo '<div>';
          echo 'Post ID ' . $post_id . ': ';
          echo '<a href="' . esc_url($post_link) . '" target="_blank">' .  get_the_title() . '</a> ';
          echo '(Posted ' . get_the_date('Y-m-d') . ')';
          echo '</div>';

          gicinema__sync_screenings($post_id);

        echo '</div>';
      }

      /* Restore original Post Data 
      * NB: Because we are using new WP_Query we aren't stomping on the 
      * original $wp_query and it does not need to be reset with 
      * wp_reset_query(). We just need to reset the post data with 
      * wp_reset_postdata().
      */
      wp_reset_postdata();

  } else {

      // No posts found
      echo '<p>No films found.</p>';

  }

  echo '</div>';

  gicinema__dedupe_screenings_table();

}
