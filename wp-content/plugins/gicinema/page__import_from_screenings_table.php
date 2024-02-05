<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__find_film_via_agile_id.php";
require_once "function__delete_all_screenings_for_film.php";
require_once "function__add_screening_to_film.php";

function gicinema_page_add__import_from_screenings_table() {
  // Main menu page is added here

  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Import From Screenings Table', // The text to be displayed in the title tags of the page when the menu is selected
      'Import From Screenings Table', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--import-from-screenings-table', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__import_from_screenings_table' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__import_from_screenings_table');

function gicinema_page_display__import_from_screenings_table() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Screenings Table</h2>';

  global $wpdb;
    
  // The table name
  $table_name = $wpdb->prefix . 'gi_screenings';
  echo '<b>$table_name</b> = ' . $table_name . '<br><br>';

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
  echo '<b>$column_names</b><br>';
  foreach ($column_names as $name) {
      echo $name . '<br>';
  }
  echo '<br>';

  // Prepare and execute the query to select distinct Agile ID values.
  $query__distinct_ids = "SELECT DISTINCT film_id FROM {$table_name} ORDER BY film_id ASC";
  $results__distinct_ids = $wpdb->get_results($query__distinct_ids);

  // Check if any results are returned
  if(!empty($results__distinct_ids)) {

    echo '<div style="display:flex; flex-direction:column; grid-gap:8px; background:#ede; padding:8px;">';

    // Loop through each result
    foreach($results__distinct_ids as $row) {
      echo '<div style="background:#fef; padding:8px;">';
      echo '<div>Agile ID: ' . esc_html($row->film_id) . '</div>';
      
      $film_post_id = gicinema__find_film_via_agile_id($row->film_id);

      if ($film_post_id) {
        echo '<div>Post ID: ' . $film_post_id . '</div>';

        gicinema__delete_all_screenings_for_film($film_post_id);

        // Prepare and execute the query to select screenings for this film.
        $query__screenings = "SELECT screening FROM {$table_name} WHERE film_id = $row->film_id ORDER BY screening ASC";
        $results__screenings = $wpdb->get_results($query__screenings);

        foreach($results__screenings as $result__screening) {
          echo '<div>Screening: ' . $result__screening->screening . '</div>';
          gicinema__add_screening_to_film($film_post_id, $result__screening->screening);
        }
      }

      echo '</div>';
    }

    echo "</div>";

  } else {
      echo "<p>No screenings found.</p>";
  }

  echo '</div>';
}
