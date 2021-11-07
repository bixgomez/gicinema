<?php

function film_teaser_function($atts = [], $content = null, $tag = '') {

  // normalize attribute keys, lowercase
  $atts = array_change_key_case((array)$atts, CASE_LOWER);

  // override default attributes with user attributes
  $filmteaser_atts = shortcode_atts([
    'film_id' => '0',
  ], $atts, $tag);

  $film_id = $filmteaser_atts['film_id'];

  if ( is_numeric($film_id) ) {
    $query = new WP_Query( array( 'p' => $film_id, 'post_type' => 'film' ) );
  }
  else {
    $query = new WP_Query( array( 'name' => $film_id, 'post_type' => 'film' ) );
  }

  $output = '';

  if ( $query->have_posts() ){
    while ( $query->have_posts() ) {

      $query->the_post();

      $this_link = get_permalink($post_id);

      $first_screening = get_field('first_screening', $post_id);
      $last_screening = get_field('last_screening', $post_id);

      $ticket_purchase_link = get_field('ticket_purchase_link', $post_id);

      $this_director = do_shortcode('[film_director]');
      $this_director = (string) $this_director;

      $this_year = do_shortcode('[film_year]');
      $this_year = (string) $this_year;

      $this_country = do_shortcode('[film_country]');
      $this_country = (string) $this_country;

      $this_length = do_shortcode('[film_length]');
      $this_length = (string) $this_length;

      $this_format = do_shortcode('[film_format]');
      $this_format = (string) $this_format;

      $this_description = get_field('description', $post_id);
      $this_description = wpautop( $this_description, false );

      $this_addl_info = get_field('additional_info', $post_id);
      $this_addl_info = wpautop( $this_addl_info, false );

      // open box
      $output .= '<div class="film-teaser">';

        $output .= '<div class="film-teaser--sidebar">';
          $output .= do_shortcode('[film_poster]');
          $output .= do_shortcode('[film_trailer]');
          $output .= '<div class="film-teaser--buy-tickets">';
            $output .= '<a class="film-trailer" href="' . $ticket_purchase_link . '">Buy Tickets</a>';
          $output .= '</div><!-- /.film-teaser--buy-tickets -->';
        $output .= '</div><!-- /.film-teaser--sidebar -->';

        $output .= '<div class="film-teaser--main">';

          $output .= '<h2 class="film-teaser--title"><a href="' . $this_link . '">' . get_the_title() . '</a></h2>';

          $playing_from = '';
          $playing_until =  '';

          if ( is_numeric($first_screening) ) {
            $first_screening_disp = date_format(date_create('@'. $first_screening)->setTimezone(new DateTimeZone('America/Los_Angeles')), 'M j');
            $last_screening_disp = date_format(date_create('@'. $last_screening)->setTimezone(new DateTimeZone('America/Los_Angeles')), 'M j');

            // $output .= '<pre>' . 'First screening: ' . $first_screening . ' ' . date('r', $first_screening) . '</pre>';
            // $playing_from = gmdate("M j", $first_screening);
            $playing_from = $first_screening_disp;
          }

          if ( is_numeric($last_screening) ) {
            // $output .= '<pre>Last screening: ' . $last_screening . '</pre>';
            // $playing_until = gmdate("M j", $last_screening);
            $playing_until = $last_screening_disp;
          }

          $output .= '<div class="film-teaser--film-info">';

            $output .= '<div class="film-teaser--director">';
              $output .= $this_director;
              if ($this_director && $this_year) { $output .= ' · '; }
              $output .= $this_year;
              if ($this_country) { $output .= ' · '; }
              $output .= $this_country;
            $output .= '</div><!-- /.film-teaser--director -->';

            $output .= '<div class="film-teaser--format">';
              $output .= $this_length;
              if ($this_length && $this_format) {  $output .= ' · '; }
              $output .= $this_format;
            $output .= '</div><!-- /.film-teaser--format -->';

            if ( is_numeric($first_screening) && is_numeric($last_screening) ) {
              $output .= '<div class="film-teaser--screening-range">';
                $output .= 'Playing ' . $playing_from . ' through ' . $playing_until;
              $output .= '</div><!-- /.film-teaser--screening-range -->';
            }

          $output .= '</div><!-- /.film-teaser--film-info -->';

          $output .= '<div class="film-teaser--content">';
            $output .= $this_description;
            $output .= $this_addl_info;
          $output .= '</div><!-- /.film-teaser--content -->';

          $output .= '<div class="film-teaser--screenings">';
            $output .= do_shortcode('[film_showtimes]');
          $output .= '</div><!-- /.film-teaser--screenings -->';

        $output .= '</div><!-- /.film-teaser--main -->';

      // close box
      $output .= '</div><!-- /.film-teaser -->';

    } /* end while */
  } /* end if */

  return $output;
}

function film_teaser_init() {
  add_shortcode('film_teaser', 'film_teaser_function');
}

add_action('init', 'film_teaser_init');
