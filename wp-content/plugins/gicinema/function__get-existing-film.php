<?php 

function get_existing_film($agile_id) {

  $args = array(
    'post_type' => 'film', // Set post type to 'film'
    'posts_per_page' => -1, // Retrieve all matching posts
    'meta_query' => array(
        array(
            'key' => 'agile_film_id', // The ACF field to check
            'value' => $agile_id, // The value to match
            'compare' => '=', // Looking for an exact match
            'type' => 'NUMERIC', // The type of the value
        ),
    ),
  );

  // Create a new instance of WP_Query with the specified arguments
  $query = new WP_Query($args);

  // Check if the query has posts
  if ($query->have_posts()) {
    // Loop through the posts
    while ($query->have_posts()) {
        $query->the_post();

        // Get the post ID
        $post_id = get_the_ID();
    }
  } else {
    // No posts found
    echo 'No films found with the specified agile_id.';
  }

  // Reset post data to the main query
  wp_reset_postdata();

  return $post_id;
}