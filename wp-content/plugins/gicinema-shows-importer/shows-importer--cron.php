<?php

require_once "shows-importer--function.php";

add_action( 'shows_importer_hook', 'shows_importer' );

// wp_next_scheduled( 'shows_importer_hook' );

if ( ! wp_next_scheduled( 'shows_importer_hook' ) ) {
    wp_schedule_event( time(), 'five_seconds', 'shows_importer_hook' );
}