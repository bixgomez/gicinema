<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Function that fetches and stores the Agile data as a transient.
function gicinema__update_agile_shows_array() {

  echo '<div class="function-info">';

  $base_url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
  $url = add_query_arg( array( '_ts' => time() ), $base_url );
  $args = array(
    'method'      => 'GET',
    'timeout'     => 15,
    'redirection' => 5,
    'httpversion' => '1.1',
    'headers'     => array(
      'Accept'        => 'application/json, text/javascript, */*; q=0.1',
      'User-Agent'    => 'GICinemaImporter/1.0 (+ ' . home_url('/') . ' )',
      'Referer'       => 'https://prod5.agileticketing.net/websales/',
    ),
  );
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
        // Retry once with a different cache buster and stronger headers
        $retry_url = add_query_arg( array( '_ts' => time() + 1 ), $base_url );
        $retry_args = $args;
        $retry_args['headers']['Accept'] = 'application/json';
        $retry_args['headers']['Pragma'] = 'no-cache';
        $retry_args['headers']['Cache-Control'] = 'no-cache';
        echo '<div>Retrying fetch with explicit JSON headers…</div>';
        $response2 = wp_remote_get( $retry_url, $retry_args );
        if ( ! is_wp_error( $response2 ) ) {
          $code2 = (int) wp_remote_retrieve_response_code($response2);
          $ctype2 = wp_remote_retrieve_header($response2, 'content-type');
          $body2 = wp_remote_retrieve_body( $response2 );
          if (is_string($body2)) { $body2 = preg_replace('/^\xEF\xBB\xBF/', '', $body2); }
          json_decode($body2);
          if ($code2 === 200 && json_last_error() === JSON_ERROR_NONE) {
            set_transient( 'agile_shows_array', $body2, 12 * HOUR_IN_SECONDS );
            echo '<div class="success">Retry succeeded! HTTP ' . esc_html($code2) . '; bytes=' . intval(strlen((string)$body2)) . '.</div>';
          } else {
            delete_transient('agile_shows_array');
            echo '<div class="failure">Failed fetching valid JSON from Agile. HTTP ' . esc_html($code) . '; content-type=' . esc_html((string)$ctype) . '; bytes=' . intval($len) . '.</div>';
            if (is_string($body)) {
              $snippet = esc_html(substr($body, 0, 300));
              echo '<details><summary>Body preview (first 300 chars)</summary><pre>' . $snippet . '</pre></details>';
            }
            echo '<div class="failure">Retry also failed. HTTP ' . esc_html($code2) . '; content-type=' . esc_html((string)$ctype2) . '; bytes=' . intval(strlen((string)$body2)) . '.</div>';
            if (is_string($body2)) {
              $snippet2 = esc_html(substr($body2, 0, 300));
              echo '<details><summary>Retry body preview (first 300 chars)</summary><pre>' . $snippet2 . '</pre></details>';
            }
          }
        } else {
          echo '<div class="failure">Retry HTTP request error: ' . esc_html($response2->get_error_message()) . '</div>';
        }
      }

  } else {
    echo '<div class="failure">HTTP request error: ' . esc_html($response->get_error_message()) . '</div>';
  }

  echo '</div>';
}
