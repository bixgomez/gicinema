<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__dedupe_screenings_table.php";
require_once "function__sync_screenings.php";

function gicinema__sync_all_screenings() {

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

function gicinema__sync_all_screenings__OLD() {

  echo '<div class="function-info">';
  echo '<div class="function-name">gicinema__sync_all_screenings()</div>';

  global $wpdb;
    
  $table_name = $wpdb->prefix . 'gi_screenings';
  echo '<div><b>$table_name</b> = ' . $table_name . '</div>';

  // SQL query to get column names
  $columns_query = "SHOW COLUMNS FROM $table_name";

  // Execute the query and get the results
  $columns = $wpdb->get_results($columns_query);

  // Extracting the column names
  $column_names = array();
  foreach ($columns as $column) {
      $column_names[] = $column->Field;
  }

  // Display the column names
  echo '<div>';
  echo '<b>$column_names</b>';
  echo '<ul>';
  foreach ($column_names as $name) {
      echo '<li>' . $name . '</li>';
  }
  echo '</ul>';
  echo '</div>';

  // Prepare and execute the query to select distinct Agile ID values.
  $query__agile_ids = "SELECT DISTINCT film_id FROM {$table_name} ORDER BY film_id ASC";
  $results__agile_ids = $wpdb->get_results($query__agile_ids);

  // Check if any results are returned
  if ( empty( $results__agile_ids ) ) :

    echo '<div class="failure">Found no films in the custom screenings table.</div>';

  else :  

    echo '<div class="success">Found ' . count($results__agile_ids) . ' unique films:</div>';
    
    echo '<div class="function-info">';
    
    foreach($results__agile_ids as $row) :

      $the_film_post = gicinema__get_post_from_agileid($row->film_id);

      if ($the_film_post === null) {
        continue;
      }

      $this_postID = $the_film_post['ID'];
      $this_agile_id = esc_html($row->film_id);
      $this_title = $the_film_post['title'];
    
      echo '<div class="function-info">';
      echo '<div><b>' . $this_title . '</b> (post id = ' . $this_postID . ', agile id = ' . $this_agile_id . ')</div>';

      echo '<div>Run a query for this post ID (' . $this_postID . ') in the custom screenings table.</div>';
      $query__screenings = "SELECT * FROM {$table_name} WHERE post_id = {$this_postID} AND status = 1 ORDER BY post_id ASC";
      $results__screenings = $wpdb->get_results($query__screenings);
      
      if(!empty($results__screenings)) :
      
        echo '<div class="function-info">';
      
        echo '<div>Loop through the screenings, updating the film_id (agile id) value if necessary</div>';
        foreach($results__screenings as $row) :
          echo '<div class="function-info">';
          echo '<pre>' . esc_html($row->screening_id) . ' | ' . 
                         esc_html($row->post_id) . ' | ' . 
                         esc_html($row->film_id) . ' | ' . 
                         esc_html($row->screening) . ' | ' . 
                         esc_html($row->screening_date) . ' | ' . 
                         esc_html($row->screening_time) . '</pre>';         
          if (empty($row->post_id)) {
            echo '<div><i>This one has no post ID!</i></div>';
            if (!empty($this_postID)) {
              gicinema__update_postID_for_filmID($row->film_id, $this_postID);
            }
          }
          echo '</div>';
        endforeach;

        gicinema__sync_screenings($this_postID);

        echo "</div>";

      else:
        
        echo "<p>No screenings found with this post id.</p>";
      
      endif;

      echo '</div>';
  
    endforeach;
        
    echo '</div>';

  endif;

  echo '</div>';
  
}











function gicinema__update_postID_for_filmID($film_id, $post_id) {

  echo '<div><b><i>Updating Agile ID (' . $film_id . ') for Post ID (' . $post_id . ')</i></b></div>';

  global $wpdb;
  $table_name = $wpdb->prefix . 'gi_screenings';

  // Update the post_id for the specified film_id.
  $result = $wpdb->update(
    $table_name,
    ['post_id' => $post_id], // Column to update
    ['film_id' => $film_id] // Condition
  );

  if ($result !== false) {
    echo "The row has been updated successfully.";
  } else {
    echo "There was an error updating the row: " . $wpdb->last_error;
  }

}











function gicinema__get_post_from_agileid($agile_id) {

  $args = array(
      'post_type' => 'film', 
      'posts_per_page' => 1,
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
    while ($query->have_posts()) {
      $query->the_post();
      $post_id = get_the_ID();

      $post_data = array(
          'ID' => $post_id,
          'title' => get_the_title()
      );
      // Return the post data array or do something with it
      return $post_data;
    }
  }

}
