<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Function that fetches and stores the Agile data as a transient.
function gicinema__update_agile_shows_array() {

  echo '<div class="function-info">';

  $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
  $args = array( 'method' => 'GET' );
  $response = wp_remote_get( $url, $args );

  echo '<div>Attempting to store Agile data in a transient.</div>';
  
  if ( ! is_wp_error( $response ) ) {
      $code = (int) wp_remote_retrieve_response_code($response);
      $ctype = wp_remote_retrieve_header($response, 'content-type');
      $body = wp_remote_retrieve_body( $response );
      $len = is_string($body) ? strlen($body) : 0;

      // Remove BOM if present
      if (is_string($body)) {
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
      }

      $valid_json = false;
      if ($code === 200 && is_string($body) && $body !== '') {
        json_decode($body);
        $valid_json = (json_last_error() === JSON_ERROR_NONE);
      }

      if ($code === 200 && $valid_json) {
        set_transient( 'agile_shows_array', $body, 12 * HOUR_IN_SECONDS );
        echo '<div class="success">Success! HTTP ' . esc_html($code) . '; bytes=' . intval($len) . '.</div>';
      } else {
        delete_transient('agile_shows_array');
        echo '<div class="failure">Failed fetching valid JSON from Agile. HTTP ' . esc_html($code) . '; content-type=' . esc_html((string)$ctype) . '; bytes=' . intval($len) . '.</div>';
        if (is_string($body)) {
          $snippet = esc_html(substr($body, 0, 300));
          echo '<details><summary>Body preview (first 300 chars)</summary><pre>' . $snippet . '</pre></details>';
        }
      }

  } else {
    echo '<div class="failure">HTTP request error: ' . esc_html($response->get_error_message()) . '</div>';
  }

  echo '</div>';
}
