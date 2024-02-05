<?php 

require_once "function__import_films_from_agile.php";

add_action('shows_importer_hook', 'import_films_from_agile');
if (!wp_next_scheduled('shows_importer_hook')) {
    wp_schedule_event(time(), 'half_hourly', 'shows_importer_hook');
}

// Custom interval
add_filter('cron_schedules', 'shows_importer_hook_interval');
function shows_importer_hook_interval($schedules) {
    $schedules['half_hourly'] = array(
        'interval' => 1800,
        'display' => esc_html__('Every Half Hour'),
    );
    return $schedules;
}
