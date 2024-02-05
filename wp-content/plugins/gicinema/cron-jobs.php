<?php 

require_once "function__import_from_agile.php";

add_action('import_from_agile_hook', 'import_from_agile');
if (!wp_next_scheduled('import_from_agile_hook')) {
    wp_schedule_event(time(), 'half_hourly', 'import_from_agile_hook');
}

// Custom interval
add_filter('cron_schedules', 'import_from_agile_hook_interval');
function import_from_agile_hook_interval($schedules) {
    $schedules['half_hourly'] = array(
        'interval' => 1800,
        'display' => esc_html__('Every Half Hour'),
    );
    return $schedules;
}
