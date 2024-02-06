<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__find_film_via_agile_id($agile_id) {

  $args = array(
      'post_type' => 'film', 
      'posts_per_page' => 1, 
      'fields' => 'ids', 
      'meta_query' => array(
          array(
              'key' => 'agile_film_id',
              'value' => $agile_id, 
              'compare' => '=',
          ),
      ),
  );

  // The Query
  $query = new WP_Query($args);

  // The Loop
  if ($query->have_posts()) {
    return $query->posts[0];
  }    
}
