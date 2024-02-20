<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Run a series of queries to determine the maximum number of future screenings for the most-screened film.
// Break this out into its own separate function.

global $wpdb;
$i = 0;
$GLOBALS['max_screenings'] = $i;
while ($i <= 10000):
  $this_screening_metakey = 'screenings_' . $i . '_screening_date';
  $querystr = "
    SELECT 1
    FROM $wpdb->postmeta
    WHERE $wpdb->postmeta.meta_key = '".$this_screening_metakey."'";
  $pageposts = $wpdb->get_results($querystr, OBJECT);
  if ($pageposts):
    $GLOBALS['max_screenings'] = $i;
  else :
    break;
  endif;
  $i++;
endwhile;
