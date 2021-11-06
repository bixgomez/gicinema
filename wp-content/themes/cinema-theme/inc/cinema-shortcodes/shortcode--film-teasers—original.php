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

  // Set the default timezone (TODO: create a global setting for this.)
	// NOTE: Never, ever do this!
	// https://github.com/elementor/elementor/issues/9609
  // date_default_timezone_set('America/Los_Angeles');

	$ourTimeZone = 'America/Los_Angeles';

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

  // Get today's "day of week" value, and use it to determine the most recent Friday's date.
  $todays_dow = date('w', time());
  $debug_output .= '$todays_dow = ' . $todays_dow . '<br>';

  // Add 2 to the current day of week to get the date of last Friday.
  $days_to_last_friday = $todays_dow + 2;
  $debug_output .= '$days_to_last_friday = ' . $days_to_last_friday . '<br>';

  // Calculate what last Friday's date was.
  $last_friday = date("m/d/Y 0:0:0", strtotime('-' . $days_to_last_friday . ' days'));
  $debug_output .= '$last_friday = ' . $last_friday . '<br>';

  // Calculate what this Friday's date is.
  $one_week_out = date('m/d/Y 0:0:0', strtotime($last_friday. ' + 6 days'));

  // Calculate what next Friday's date is.
  $two_weeks_out = date('m/d/Y 0:0:0', strtotime($last_friday. ' + 13 days'));

  // Debug output.
  $debug_output .= '$one_week_out = ' . $one_week_out . '<br>';
  $debug_output .= '$two_weeks_out = ' . $two_weeks_out . '<hr>';

  // Convert last Friday, next Thursday, and two Thursdays out to timestamps.
  $last_friday = strtotime($last_friday);
  $one_week_out = strtotime($one_week_out);
  $two_weeks_out = strtotime($two_weeks_out);

  // Debug output.
  $debug_output .= '$last_friday (timestamp) = ' . $last_friday . '<br>';
  $debug_output .= '$one_week_out (timestamp) = ' . $one_week_out . '<br>';
  $debug_output .= '$two_weeks_out (timestamp) = ' . $two_weeks_out . ' (' . date("m/d/Y H:i", substr($two_weeks_out, 0, 10)) . ') ' . '<hr>';

  // If the "from" date is not 0, convert it to a timestamp.
  // If it is 0, set it to last Friday.
  if ( $from_date != "0" ) :
    $from_date = strtotime($from_date . ' '. $ourTimeZone);
  else :
    $from_date = $last_friday;
  endif;

  // If the "to" date is not 0, convert it to a timestamp.
  // If it is 0, set it to two Thursdays out.
  if ( $to_date != "0" ) :
    $to_date = strtotime($to_date . ' '. $ourTimeZone);
  else :
    $to_date = $two_weeks_out;
  endif;

  // If the "from" timestamp is larger than the "to" timestamp, set from & to dates to last Friday and two Thursdays out.
  $debug_output .= 'Is the from date later than the to date?<br>';
  if ( $from_date > $to_date) :
    $from_date = $last_friday;
    $to_date = $two_weeks_out;

    // Debug output.
    $debug_output .= 'Yes!<br>';
    $debug_output .= '$from_date = ' . $from_date . '<br>';
    $debug_output .= '$to_date = ' . $to_date . '<br><hr>';
  else :
    $debug_output .= 'Nope, all is well!<br><hr>';
  endif;

  $date_span = $to_date - $from_date;

  // Debug output.
  $debug_output .= '$from_date (timestamp) = ' . $from_date  . ' (' . date("m/d/Y H:i", substr($from_date, 0, 10)) . ')<br>';
  $debug_output .= '$to_date (timestamp) = ' . $to_date . ' (' . date("m/d/Y H:i", substr($to_date, 0, 10)) . ')<br>';
  $debug_output .= '$date_span (timestamp) = ' . $date_span . '<hr>';

  $debug_output .= '<h3>Building the query</h3>';

  $this_date = $from_date;

  // Building the big query
  $querystr = "SELECT p.ID, p.post_title, m.meta_value FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id AND m.meta_key ='first_screening') WHERE p.post_type = 'film' AND p.post_status = 'publish'";

  $querystr .= "AND ID IN (
      SELECT post_id 
      FROM wp_postmeta 
      WHERE post_id > 0 
      AND meta_key='all_screenings' 
      AND ( ";

  while ($this_date <= $to_date) :
    $debug_output .= 'This date: ' . $this_date . ' (' . date("m/d/Y H:i", substr($this_date, 0, 10)) . ')<br>';

    $querystr .= "FIND_IN_SET(". $this_date .", meta_value) ";

    $this_date = strtotime('+1 days', $this_date);

    if ($this_date > $to_date) :
      $querystr .= ") ";
    else :
      $querystr .= "OR ";
    endif;
  endwhile;

  $querystr .= ") ORDER BY m.meta_value, p.post_title, p.ID ASC";

  $rows = $wpdb->get_results($querystr, OBJECT);
  $films_to_display = array();
  $prefix = $films_list = '';

  if ($rows) :
    foreach ($rows as $row) {
      $film_id = $row->ID;
      if ( !in_array($film_id, $films_to_display) ) :
        array_push($films_to_display, $film_id);
        $films_list .= $prefix . $film_id;
        $prefix = ',';
      endif;
    }
  endif;

  if ($list_only) :

    $output = $films_list;

  else:

    $from_date_display = date('l, n/j/y', $from_date);
    $to_date_display = date('l, n/j/y', $to_date);

    $output .= '<div class="film-teasers-heading">';
    $output .= 'Displaying films showing ' . $from_date_display . ' - ' . $to_date_display;
    $output .= '</div>';

    $output .= '<div class="film-teasers">';

    if ( sizeof($films_to_display) ) :
    foreach ($films_to_display as $film_to_display) {
      $output .= do_shortcode('[film_teaser film_id="' . $film_to_display .'"]');
    }
    else:
      $output .= '<div class="no-screenings"><p>No screenings are scheduled for this time period.</p></div>';
    endif;

    $output .= '</div>';

  endif;

  // TODO: Make debug work here.
  $debug = 0;

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
