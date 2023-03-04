<?php
require_once get_template_directory() . '/inc/cinema-functions/validate-date.php';

function getFilm(
  $film_id, 
  $format="title", 
  $date
  ) {

  $args = array(
    'posts_per_page' => 1,
    'post_type' => 'film',
    'meta_key' => 'agile_film_id',
    'meta_value' => $film_id
  );
  $get_film_post = new WP_Query( $args );

  if ($get_film_post->have_posts())  {

    echo '<div class="film">';
    while ($get_film_post->have_posts()) {
      $get_film_post->the_post();
      $this_link = get_permalink();
      $shortName = get_field('short_name');
      $displayName = strlen($shortName) ? $shortName : get_the_title();
      echo '<a class="film-title" href="' . $this_link . '">' . $displayName . '</a>';
      if (validateDate($date)) :
        global $wpdb;
        $scrs_table_name = $wpdb->prefix . 'gi_screenings';
        $result = $wpdb->get_results("SELECT screening_time FROM $scrs_table_name WHERE film_id = '$film_id' AND screening_date = '$date' ORDER BY screening_time");
        if ($result) :
          $allScreenings = '';
          foreach ($result as $row) :
            $scrDateTime = $date . ' ' . $row->screening_time;
            $scrDateTimeStamp = strtotime($scrDateTime);
            $scrHour = date('g', $scrDateTimeStamp);
            $scrMinute = date(':i', $scrDateTimeStamp);
            $scrTimeAmPm = date('a', $scrDateTimeStamp);
            $scrTime = $scrMinute != ':00' ? $scrHour . $scrMinute . $scrTimeAmPm : $scrHour . $scrTimeAmPm;
            if ( strlen($allScreenings) ) :
              $allScreenings .= '/' . $scrTime;
            else :
              $allScreenings .= $scrTime;
            endif;
          endforeach;
          echo '<div class="film-times">' . $allScreenings . '</div>';
        endif;
      endif;
    }
    echo '</div>';
  }

}
