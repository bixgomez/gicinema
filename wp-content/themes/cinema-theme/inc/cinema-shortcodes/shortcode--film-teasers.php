<?php
/*
 * This is the [film_teasers] shortcode.
 * It takes the following arguments:
 * - from_date
 *   - defaults to last Friday
 * - to_date
 *   - defaults to next Thursday
 * - debug
 */

// Initialize function, with attributes.
function film_teasers_function($atts = [], $content = null, $tag = '') {

  // We're going to be running queries later, so let's get the db now.
  global $wpdb;

  $output = '';
  $debug_output = '';

  // override default attributes with user attributes
  $filmteaser_atts = shortcode_atts([
    'from_date' => '0',
    'to_date' => '0',
    'list_only' => '0',
    'debug' => '0',
  ], $atts, $tag);

  // Set from & to date variables based on attributes passed.
  $from_date = $filmteaser_atts['from_date'];
  $to_date = $filmteaser_atts['to_date'];
  $list_only = $filmteaser_atts['list_only'];
  $debug = $filmteaser_atts['debug'];

  // Debug output.
  $debug_output .= '<h3>Shortcode attributes (if left blank, these are default values):</h3>';
  $debug_output .= '$from_date = '.$from_date.'<br>';
  $debug_output .= '$to_date = '.$to_date.'<br>';
  $debug_output .= '$list_only = '.$list_only.'<br>';
  $debug_output .= '$debug = '.$debug.'<hr>';

  // Building the big query
  $querystr = "
    SELECT p.ID, p.post_title, m.meta_value 
    FROM $wpdb->posts p
        LEFT JOIN $wpdb->postmeta m 
            ON (p.ID = m.post_id AND m.meta_key ='first_screening')
    WHERE p.post_type = 'film' 
      AND p.post_status = 'publish'";

  $querystr .= "AND ID IN (
      SELECT post_id 
      FROM wp_postmeta 
      WHERE post_id > 0 
      AND meta_key='screening_first' 
      )";

  if ($debug) {
    echo '<div class="debug">';
    echo $debug_output;
    echo '<h3>The Query</h3>';
    echo $querystr;
    echo '</div>';
  }

  return $output;
}

function film_teasers_init() {
  add_shortcode('film_teasers', 'film_teasers_function');
}

add_action('init', 'film_teasers_init');
