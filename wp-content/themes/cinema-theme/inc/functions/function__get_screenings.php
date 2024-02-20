<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function getScreenings($agile_film_id) {
    global $wpdb;
    $screenings_table_name = $wpdb->prefix . 'gi_screenings';

    $screenings_query = "
        SELECT * 
        FROM {$screenings_table_name} 
        WHERE film_id = {$agile_film_id} 
        ORDER BY screening
    ";

    $result = $wpdb->get_results($screenings_query);

    if ( count($result) ) {
        foreach( $result as $key => $row) {
            echo $row->screening . '<br>';
        }
    }
    else {
        return 'No result';
    }
}
