<?php
/**
 * Custom screenings table creation.
 *
 * Loaded by gicinema.php and run through register_activation_hook() when the
 * plugin is activated. It uses dbDelta() to create or update the
 * {$wpdb->prefix}gi_screenings table, including keys that keep repeated Agile
 * imports from creating duplicate screening rows.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
    exit;
}

function gicinema__create_custom_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'gi_screenings';
    $sql = "CREATE TABLE $table_name (
        screening_id INTEGER NOT NULL AUTO_INCREMENT,
        film_id INTEGER,
        post_id INTEGER,
        screening TEXT,
        screening_date TEXT,
        screening_time TEXT,
        status TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (screening_id),
        UNIQUE KEY unique_screening (film_id, post_id, screening_date, screening_time),
        UNIQUE KEY unique_screening_str (film_id, post_id, screening(19))
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
