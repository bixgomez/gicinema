<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function function__dedupe_screenings_table() {

    echo '<div class="function-info">';
    
    echo '<div>Running <i>function__dedupe_screenings_table()</i></div>';
    
    echo '<div>Deduping custom screenings table.</div>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'gi_screenings';
  
    $query = "
        DELETE FROM $table_name
        WHERE screening_id NOT IN (
            SELECT MIN(screening_id)
            FROM $table_name
            GROUP BY screening, film_id, post_id
        )
    ";

    $rows_affected = $wpdb->query($query);
  
    // Check if the deletion was successful
    if ($rows_affected !== false) {
        // If rows were affected, deletion was successful
        if ($rows_affected > 0) {
            echo '<div>Success! ' . $rows_affected . ' duplicate rows were deleted.</div>';
        } else {
            echo '<div>No duplicate rows found to delete.</div>';
        }
    } else {
        // If $wpdb->query() returned false, there was an error in the query
        echo '<div>There was an error in attempting to delete duplicate rows.</div>';
    }

    echo "</div>";

}
