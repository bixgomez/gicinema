<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Function that fetches and stores the Agile data as a transient.
function gicinema__update_agile_shows_array() {

  echo '<div class="function-info">';

  $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
  $args = array( 'method' => 'GET' );
  $response = wp_remote_get( $url, $args );

  echo '<div>Attempting to store Agile data in a transient.</div>';
  
  if ( ! is_wp_error( $response ) ) {
      $body = wp_remote_retrieve_body( $response );
      set_transient( 'agile_shows_array', $body, 12 * HOUR_IN_SECONDS );
      echo '<div class="success">Success!</div>';

  } else {
    echo '<div class="failure">Something went wrong.</div>';
  }

  echo '</div>';
}
