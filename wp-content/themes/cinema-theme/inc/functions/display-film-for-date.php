<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once get_template_directory() . '/inc/functions/function__validate_date.php';

function displayFilmForDate ($post_id, $date) {

  $args = array(
    'posts_per_page' => 1,
    'post_type' => 'film',
    'p' => $post_id,
    'post_status' => 'publish',
  );
  $get_film_post = new WP_Query( $args );

  if ($get_film_post->have_posts())  {
    echo '<button data-filmid="' . $post_id . '" class="film">';
    while ($get_film_post->have_posts()) {
      $get_film_post->the_post();
      $this_link = get_permalink();
      $shortName = get_field('short_name');
      $displayName = !is_null($shortName) && strlen($shortName) ? $shortName : get_the_title();

      echo '<span class="film-title">' . $displayName . '</span>';
      if (validateDate($date)) :
        global $wpdb;
        $scrs_table_name = $wpdb->prefix . 'gi_screenings';
        $result = $wpdb->get_results("SELECT screening_time FROM $scrs_table_name WHERE post_id = '$post_id' AND screening_date = '$date' AND status = 1 ORDER BY screening_time");
        if ($result) :
          $allScreenings = '';
          $thisTime = '';
          foreach ($result as $row) :
            $scrDateTime = $date . ' ' . $row->screening_time;
            $scrDateTimeStamp = strtotime($scrDateTime);
            $scrHour = date('g', $scrDateTimeStamp);
            $scrMinute = date(':i', $scrDateTimeStamp);
            $scrTimeAmPm = date('a', $scrDateTimeStamp);
            $scrTime = $scrMinute != ':00' ? $scrHour . $scrMinute . $scrTimeAmPm : $scrHour . $scrTimeAmPm;
            if ($scrTime != $thisTime) :
              if ( strlen($allScreenings) ) :
                $allScreenings .= '/' . $scrTime;
              else :
                $allScreenings .= $scrTime;
              endif;
            endif;
            $thisTime = $scrTime;
          endforeach;
          echo '<div class="film-times">' . $allScreenings . '</div>';
        endif;
      endif;
    }
    echo '</button>';
  }

}

// https://stackoverflow.com/questions/40035148/ajax-response-using-html5-data-attribute

// https://www.smashingmagazine.com/2011/10/how-to-use-ajax-in-wordpress/

// https://www.converticacommerce.com/ux-ui-frontend-design/authoritative-guide-to-implementing-ajax-in-wordpress/