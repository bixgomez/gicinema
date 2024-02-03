<?php 

require_once "function__shows-importer.php";

// shows importer shortcode
function shows_importer_shortcode_function($atts) {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return __('You do not have permission to access this content.', 'text-domain');
    }
    shows_importer();
}
add_shortcode('shows_importer_shortcode', 'shows_importer_shortcode_function');
