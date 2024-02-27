<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Loading functions we'll be running as cron jobs.
require_once "function__import_films_from_agile.php";
require_once "function__manage_screenings.php";
require_once "function__db_backup_and_cleanup.php";
require_once "function__update_agile_shows_array.php";

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
    $schedules['every_128_minutes'] = array(
        'interval' => 7680,
        'display' => esc_html__('Every 128 Minutes'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'gicinema__hook_interval');

// Cron job to manage screenings.
if (!wp_next_scheduled('cron__manage_screenings')) {
    wp_schedule_event(time(), 'every_128_minutes', 'cron__manage_screenings');
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

// Cron job to backup the database.
if (!wp_next_scheduled('cron__db_backup_and_cleanup')) {
    wp_schedule_event(strtotime('21:00:00'), 'daily', 'cron__db_backup_and_cleanup');
}
add_action('cron__db_backup_and_cleanup', 'gicinema__db_backup_and_cleanup');
