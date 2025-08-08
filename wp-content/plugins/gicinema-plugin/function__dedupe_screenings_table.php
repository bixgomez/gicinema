<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__dedupe_screenings_table() {
    // CSRF Protection - always required since this only runs from forms
    if (!isset($_POST['dedupe_nonce']) || !wp_verify_nonce($_POST['dedupe_nonce'], 'dedupe_screenings_action')) {
        echo '<div class="notice notice-error"><p>Security check failed</p></div>';
        return;
    }

    echo '<div class="function-info">';

    echo '<h4>Deduping the custom screenings table</h4>';

    echo '<div class="function-info">';
    
    echo '<div>Running <i>gicinema__dedupe_screenings_table()</i></div>';
    
    echo '<div>Deduping custom screenings table.</div>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'gi_screenings';

    $query = "
        DELETE t1 
        FROM $table_name AS t1
        LEFT JOIN (
            SELECT MIN(screening_id) AS min_id
            FROM $table_name
            GROUP BY screening, film_id, post_id
        ) AS t2 ON t1.screening_id = t2.min_id
        WHERE t2.min_id IS NULL
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
    echo "</div>";

}
