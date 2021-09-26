<?php

$args = array(
  'post_type' => 'alert'
);

$alerts_query = new WP_Query($args);

if ( $alerts_query->have_posts() ) :

  while ( $alerts_query->have_posts() ) : $alerts_query->the_post();

    $display_this_alert = 0;

    $start_date = get_field('start_date', get_the_ID());
    $end_date = get_field('end_date', get_the_ID());

    $start_date_stamp = strtotime($start_date);
    $end_date_stamp = strtotime($end_date);
    $today = strtotime('now');

    if (!is_numeric($end_date_stamp)) {
      $end_date_stamp = strtotime('2100-01-01');
    }

    // echo '$start_date = ' . $start_date . '<br>';
    // echo '$end_date = ' . $end_date . '<br>';
    // echo '$start_date_stamp = ' . $start_date_stamp . '<br>';
    // echo '$end_date_stamp = ' . $end_date_stamp . '<br>';
    // echo '$today = ' . $today . '<br>';

    if ($today >= $start_date_stamp && $end_date_stamp > $today) {
      $display_this_alert = 1;
    }

    if ($display_this_alert) {
      print '<div class="alert">' . get_the_content() . '</div>';
    }

  endwhile;

  wp_reset_postdata();

endif;
