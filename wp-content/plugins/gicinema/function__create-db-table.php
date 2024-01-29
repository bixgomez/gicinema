<?php

function gicinema_shows_importer_create_db() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'gi_screenings';
    $sql = "CREATE TABLE $table_name (
        screening_id INTEGER NOT NULL AUTO_INCREMENT,
        film_id INTEGER,
        screening TEXT,
        screening_date TEXT,
        screening_time TEXT,
        PRIMARY KEY  (screening_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'gicinema_shows_importer_create_db');
