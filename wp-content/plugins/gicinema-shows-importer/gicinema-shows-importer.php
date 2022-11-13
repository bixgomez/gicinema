<?php
/* Plugin Name: GI Cinema Shows Importer
 * Plugin URI:  https://grandillusioncinema.org/
 * Description: Retrieves the most recently added shows..
 * Version:     1.0.0
 * Author:      Richard Gilbert
 * Author URI:  https://grandillusioncinema.org/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

require_once "shows-importer--function.php";

add_shortcode( 'import_shows', 'shows_importer' );

add_action( 'shows_importer_hook', 'shows_importer' );

if ( ! wp_next_scheduled( 'shows_importer_hook' ) ) {
    wp_schedule_event( time(), 'half_hourly', 'shows_importer_hook' );
}

// Custom interval
add_filter( 'cron_schedules', 'shows_importer_hook_interval' );
function shows_importer_hook_interval( $schedules ) {
    $schedules['half_hourly'] = array(
        'interval' => 1800,
        'display' => esc_html__( 'Every Half Hour' ),
    );
    return $schedules;
}

function gicinema_shows_importer_create_db() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'gi_screenings';
    $sql = "CREATE TABLE $table_name (
     screening_id INTEGER NOT NULL AUTO_INCREMENT,
     film_id INTEGER,
     screening TEXT,
     PRIMARY KEY  (screening_id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'gicinema_shows_importer_create_db' );
