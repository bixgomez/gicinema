<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Loading functions we'll be running as cron jobs.
require_once "function__import_films_from_agile.php";
require_once "function__manage_screenings.php";

// Setting up custom cron intervals.
function gicinema__hook_interval($schedules) {
    $schedules['every_23_minutes'] = array(
        'interval' => 1380,
        'display' => esc_html__('Every 23 Minutes'),
    );
    $schedules['every_30_minutes'] = array(
        'interval' => 1800,
        'display' => esc_html__('Every 30 Minutes'),
    );
    $schedules['every_47_minutes'] = array(
        'interval' => 2820,
        'display' => esc_html__('Every 47 Minutes'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'gicinema__hook_interval');

// Cron job to manage screenings.
if (!wp_next_scheduled('cron__manage_screenings')) {
    wp_schedule_event(time(), 'every_47_minutes', 'cron__manage_screenings');
}
add_action('cron__manage_screenings', 'gicinema__manage_screenings');

// Cron job to import films from Agile.
if (!wp_next_scheduled('cron__import_films_from_agile')) {
    wp_schedule_event(time(), 'every_30_minutes', 'cron__import_films_from_agile');
}
add_action('cron__import_films_from_agile', 'gicinema__import_films_from_agile');

// Cron job to run the Agile shows fetcher.
if ( ! wp_next_scheduled( 'cron__update_agile_shows_array' ) ) {
    wp_schedule_event( time(), 'every_23_minutes', 'cron__update_agile_shows_array' );
}
add_action( 'cron__update_agile_shows_array', 'gicinema__update_agile_shows_array' );

// Function that fetches and stores the Agile data as a transient.
function gicinema__update_agile_shows_array() {
    $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
    $args = array( 'method' => 'GET' );
    $response = wp_remote_get( $url, $args );
    
    if ( ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        set_transient( 'agile_shows_array', $body, 12 * HOUR_IN_SECONDS );
    }
}