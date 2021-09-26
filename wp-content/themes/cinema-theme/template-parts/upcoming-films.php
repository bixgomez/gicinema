<?php

// Setting the time zone is not helpful, as it the time as though it is GMT.
// NOTE: Never, ever do this!
// https://github.com/elementor/elementor/issues/9609
// date_default_timezone_set('America/Los_Angeles');

$right_now = date('m/j/Y h:i a', time());
$today_unix = strtotime('today midnight');
$today = date('m/j/Y h:i a', $today_unix);

$i=1;
$the_friday = $today_unix;
while($i<=7) {
  $the_friday = strtotime('-1 day', $the_friday);
  if ( date('l', $the_friday) == 'Friday' ) :
    break;
  endif;
  $i++;
}

if ( date('l', $today_unix) == 'Friday' ) :
  $the_friday = $today_unix;
endif;

$the_friday_date = date('m/j/Y h:i a', $the_friday);

$n = 1;
while ($n <= 52):

  // echo $n . ', ';

  $the_thursday = strtotime('+6 days', $the_friday);
  $disp_friday = date('n/j', $the_friday);
  $disp_thursday = date('n/j', $the_thursday);

  $films_this_week = do_shortcode('[film_teasers list_only="1" from_date="' . $the_friday .'" to_date="' . $the_thursday .'"]');
  $films_this_week_arr = explode(',',$films_this_week);

  if (!empty($films_this_week)) :
    echo '<div class="week">';
    echo '<h4 class="week-title">Week of ' . $disp_friday . ' - ' . $disp_thursday . '</h4>';

    echo $disp_thursday . '<br>';
    echo $disp_friday . '<br>';

    echo do_shortcode('[film_teasers list_only="0" from_date="' . $the_friday .'" to_date="' . $the_thursday .'"]');
    echo '</div>';
    $the_friday = strtotime('+7 days', $the_friday);
    if ( $the_friday >  strtotime($GLOBALS['last_screening_date'])) :
      break;
    endif;
  endif;

  $n++;
endwhile;
