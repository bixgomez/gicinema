<?php

$GLOBALS['last_screening_date'] = 0;

$querystr = "SELECT meta_value FROM $wpdb->postmeta WHERE ( ";

$i=0;
while ( $i <= $GLOBALS['max_screenings'] ) :
  $querystr .= "(meta_key='screenings_" . $i . "_screening_date') ";
  if ( $i < $GLOBALS['max_screenings'] ):
    $querystr .= "OR ";
  endif;
  $i++;
endwhile;

$querystr .= ") ORDER BY meta_value ASC";

// print_r('<pre>' . $querystr . '</pre>');

$rows = $wpdb->get_results($querystr, OBJECT);

foreach ($rows as $row) {
  $GLOBALS['last_screening_date'] = $row->meta_value;
}

// print_r('<pre>' . $GLOBALS['last_screening'] . '</pre>');
