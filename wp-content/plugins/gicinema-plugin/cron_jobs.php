<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__import_films_from_agile.php";

function gicinema__hook_interval($schedules) {
    $schedules['half_hourly'] = array(
        'interval' => 1800,
        'display' => esc_html__('Every Half Hour'),
    );
    return $schedules;
}

add_filter('cron_schedules', 'gicinema__hook_interval');

if (!wp_next_scheduled('gicinema__agile_importer')) {
    wp_schedule_event(time(), 'half_hourly', 'gicinema__agile_importer');
}

add_action('gicinema__agile_importer', 'gicinema__import_films_from_agile');





if ( ! wp_next_scheduled( 'gicinema__update_agile_shows_array' ) ) {
    wp_schedule_event( time(), 'half_hourly', 'gicinema__update_agile_shows_array' );
}

add_action( 'gicinema__update_agile_shows_array', 'fetch_and_store_api_response' );

function fetch_and_store_api_response() {
    $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
    $args = array( 'method' => 'GET' );
    $response = wp_remote_get( $url, $args );
    
    if ( ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        set_transient( 'agile_shows_array', $body, 12 * HOUR_IN_SECONDS );
    }
}