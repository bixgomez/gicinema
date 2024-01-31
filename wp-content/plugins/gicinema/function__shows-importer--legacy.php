<?php
require_once "function__get-existing-film.php";
require_once "function__add-screening-to-existing-film.php";

function shows_importer_legacy() {

    echo '<div style="background: #333; color: #FFF; padding: 20px; font-family: helvetica;">';
    echo '<h1>Legacy Shows Importer</h1>';

    global $wpdb;
    
    // The table name
    $table_name = $wpdb->prefix . 'gi_screenings';
    echo '<b>$table_name</b> = ' . $table_name;

    // SQL query to get column names
    $columns_query = "SHOW COLUMNS FROM $table_name";

    // Execute the query and get the results
    $columns = $wpdb->get_results($columns_query);

    // Extracting the column names
    $column_names = array();
    foreach ($columns as $column) {
        $column_names[] = $column->Field;
    }

    // Do something with the column names
    foreach ($column_names as $name) {
        echo $name . '<br>';
    }
    
    /*

    // Prepare the SQL query
    $query = "SELECT * FROM $table_name ORDER BY film_id, screening";
    
    // Execute the query
    $results = $wpdb->get_results($query);
    
    // Check for results and handle them
    if (!empty($results)) {
        foreach ($results as $row) {
            $id = $row->screening_id;
            $agile_id = $row->film_id;
            $screening = $row->screening;
            echo '$agile_id = ' . $agile_id . '<br>';
            echo '$screening = ' . $screening . '<br>';
            add_screening_to_existing_film(get_existing_film($agile_id), $screening);
        }
    } else {
        echo 'No screenings found.';
    }

    */

    echo '</div>';
}
