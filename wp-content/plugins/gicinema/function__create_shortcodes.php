<?php 

require_once "function__import_films_from_agile.php";

// shows importer shortcode
function import_films_from_agile_shortcode_function($atts) {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return __('You do not have permission to access this content.', 'text-domain');
    }
    import_films_from_agile();
}
add_shortcode('import_films_from_agile_shortcode', 'import_films_from_agile_shortcode_function');
