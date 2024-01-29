<?php 

require_once "function__shows-importer.php";
require_once "function__shows-importer--legacy.php";
require_once "function__delete-all-films.php";

// shows importer shortcode
function shows_importer_shortcode_function($atts) {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return __('You do not have permission to access this content.', 'text-domain');
    }
    shows_importer();
}
add_shortcode('shows_importer_shortcode', 'shows_importer_shortcode_function');

// legacy shows importer shortcode
function shows_importer_legacy_shortcode_function($atts) {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return __('You do not have permission to access this content.', 'text-domain');
    }
    shows_importer_legacy();
}
add_shortcode('shows_importer_legacy_shortcode', 'shows_importer_legacy_shortcode_function');

// delete all films shortcode
function delete_all_films_shortcode_function($atts) {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return __('You do not have permission to access this content.', 'text-domain');
    }
    delete_all_films();
}
add_shortcode('delete_all_films_shortcode', 'delete_all_films_shortcode_function');
