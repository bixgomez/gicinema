<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__dedupe_screenings_table.php";
require_once "function__sync_screenings.php";

function function__manage_screenings() {

  function__dedupe_screenings_table();

  global $wpdb;

  echo '<div class="function-info">';
    
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

  echo '</div>';

  // Prepare and execute the query to select distinct Agile ID values.
  $query__agile_ids = "SELECT DISTINCT film_id FROM {$table_name} ORDER BY film_id ASC";
  $results__agile_ids = $wpdb->get_results($query__agile_ids);

  // Check if any results are returned
  if ( !empty( $results__agile_ids ) ) :

    echo 'Found ' . count($results__agile_ids) . ' unique films:';
    
    echo '<div class="function-info">';
    
    foreach($results__agile_ids as $row) :

      $the_film_post = gicinema__get_post_from_agileid($row->film_id);

      if ($the_film_post === null) {
        continue;
      }

      $this_postID = $the_film_post['ID'];
      $this_title = $the_film_post['title'];
    
      echo '<div class="function-info">';
      echo '<div>film_id (Agile): ' . esc_html($row->film_id) . '</div>';
      echo '<div><b>' . $this_title . '</b> (post_ID = ' . $this_postID . ')</div>';

      // Prepare and execute the query to select screenings for this film.
      $query__screenings = "SELECT * FROM {$table_name} WHERE film_id = {$row->film_id} ORDER BY post_id ASC";
      $results__screenings = $wpdb->get_results($query__screenings);

      // If there are any results in the custom screenings table.
      if(!empty($results__screenings)) :
      
        echo '<div class="function-info">';
      
        // Loop through the screenings, updating the post_id value if necessary
        foreach($results__screenings as $row) :
          echo '<div class="function-info">';
          echo '<pre>' . esc_html($row->screening) . ' | ' . esc_html($row->screening_date) . ' | ' . esc_html($row->screening_time) . '</pre>';
          echo '<div>post_id: ' . esc_html($row->post_id) . '</div>';          
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
        
        echo "<p>No screenings found.</p>";
      
      endif;

      echo '</div>';
  
    endforeach;
        
    echo '</div>';

  endif;
  
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











function gicinema__delete_all_screenings_for_film($post_id) {
  echo '<div>Deleting all screenings for ' . $post_id . '</div>';
  update_field('screenings', array(), $post_id);
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













function gicinema__get_postid_from_agileid($agile_id) {

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

function gicinema__add_screening_to_film($post_id, $screening) {

  echo '<div>Adding screening ('.$screening.') to ' . $post_id . '</div>';

  $field_key = 'field_617b2f8e4b8c4';

  $screening_array = array('screening' => $screening);

  // Add a row to the 'screenings' repeater field for the specified post
  $success = add_row($field_key, $screening_array, $post_id);

  if ($success) {
      echo 'Screening added successfully.';
  } else {
      echo 'Failed to add screening.';
  }

}