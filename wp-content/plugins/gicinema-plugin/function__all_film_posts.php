<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema__all_film_posts() {

  echo '<div class="function-info">';

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

          gicinema__add_film_to_table($post_id, $agile_id);

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
}





function gicinema__add_film_to_table($post_id, $agile_id) {

  echo '<div class="function-info">';
  echo '<div class="function-title">gicinema__add_film_to_table($post_id, $agile_id)</div>';

  $missing_post_id = 0;
  $missing_agile_id = 0;

  global $wpdb;
  $table_name = $wpdb->prefix . 'gi_screenings';

  if (!$agile_id) :
    echo '<div class="failure">There is no Agile ID for this film!</div>'; 
  else :  
    echo '<div>Looking for NULL post id for film_id ' . $agile_id . ' in the custom screenings table...</div>';

    $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id IS NULL AND film_id = %d", $agile_id );
    $results = $wpdb->get_results( $query );

    if ( !empty( $results ) ) {
      echo '<div class="failure">The post_id value is NULL for this film in the custom table.</div>';
      echo '<div><b>Recommended: </b>Add post_id to existing records in table.</div>';
    } else {
      echo '<div class="success">There are no NULL post_id values for this film in the custom table.</div>'; 
    }

  endif;

  echo '<div>Looking for post id ' . $post_id . ' in the custom screenings table...</div>';

  $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d", $post_id );
  $results = $wpdb->get_results( $query );

  if ( empty( $results ) ) {
    echo '<div class="failure">No matching record found in the custom table.</div>';
    $missing_post_id = 1;
  } else {   
    echo '<div class="success">Matching record found in the custom table.</div>';  
  }

  if ($agile_id) :
    echo '<div>Looking for agile id (' . $agile_id . ') in the custom screenings table...</div>';

    $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE film_id = %d", $agile_id );
    $results = $wpdb->get_results( $query );

    if ( empty( $results ) ) {
      echo '<div class="failure">No matching record found in the custom table.</div>';
      $missing_agile_id = 1;
    } else {   
      echo '<div class="success">Matching record found in the custom table.</div>';  
    }
  else :
    $missing_agile_id = 1;
  endif;  

  if($missing_post_id && $missing_agile_id) {
    echo '<div class="failure">Missing both post ID and Agile ID.</div>';
    echo '<div><b>Recommended:</b> Create a record for this film in the custom table, importing screenings from repeater field.</div>';
  }

  elseif ($missing_post_id) {
    echo '<div class="failure">Missing post ID.</div>';
  }

  elseif ($missing_agile_id) {
    echo '<div class="failure">Missing Agile ID.</div>';
  }

  else {
    echo '<div>All is well!</div>';
  }

  echo '</div>';
}
