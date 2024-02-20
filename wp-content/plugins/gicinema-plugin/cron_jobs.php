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
