<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function getScreenings($post_id) {
    global $wpdb;
    $screenings_table_name = $wpdb->prefix . 'gi_screenings';

    $screenings_query = "
        SELECT screening
        FROM {$screenings_table_name} 
        WHERE post_id = {$post_id} 
        ORDER BY screening
    ";

    $result = $wpdb->get_results($screenings_query);

    if ( count($result) ) {

        // Transforming the array
        $screeningsArray = [];
        foreach ($result as $item) {
            $screeningsArray[] = convertDateFormat($item->screening);
        }

        // Sort the screenings array in ascending order
        usort($screeningsArray, "date_compare");

        $timezone = new DateTimeZone('America/Los_Angeles');
        $today = (new DateTime('now', $timezone))->setTime(0, 0, 0);
        $twoMonthsAgo = (new DateTime('-2 months', $timezone))->setTime(0, 0, 0);

        // date/time object of the most recent screening of this film.
        $lastScreeningCompare = (new DateTime(end($screeningsArray), $timezone))->setTime(0, 0, 0);

        echo '<ul class="screenings-list">';
        foreach ($screeningsArray as $screening_date) {
          $dateTime = new DateTime($screening_date);
          // If the screening is current, only display screening from the most recent 2 months.
          // If it's an older screening, show all of the screenings.
          $dateTimeCompare = (new DateTime($screening_date, $timezone))->setTime(0, 0, 0);
          if (  ($lastScreeningCompare < $twoMonthsAgo) || ($dateTimeCompare > $twoMonthsAgo) ) {
            $formattedDate = $dateTime->format('l, M j, Y');
            $formattedTime = $dateTime->format('g:ia');
            if ($dateTimeCompare < $today):
              $extraClass = 'past';
            elseif ($dateTimeCompare == $today):
              $extraClass = 'present';
            elseif ($dateTimeCompare > $today):
              $extraClass = 'future';
            endif;
            echo '<li class="screening '.$extraClass.'">' . $formattedDate . ', ' . $formattedTime . '</li>';
          }
        }
        echo '</ul>';

    } else {
        return 'No screenings';
    }
}

// Function to convert datetime format
function convertDateFormat($dateTimeStr) {
  $date = new DateTime($dateTimeStr);
  return $date->format('m/d/Y g:i a');
}