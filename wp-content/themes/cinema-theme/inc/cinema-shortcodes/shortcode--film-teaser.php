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

      $first_screening = get_field('screening_first', $post_id);
      $last_screening = get_field('screening_last', $post_id);

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

        $output .= '<div class="film-teaser--poster">';
          $output .= do_shortcode('[film_poster]');
        $output .= '</div><!-- /.film-teaser--poster -->';

        $output .= '<div class="film-teaser--links">';
          $output .= '<div class="film-teaser--trailer">';
            $output .= do_shortcode('[film_trailer]');
          $output .= '</div><!-- /.film-teaser--trailer -->';

          $output .= '<div class="film-teaser--buy-tickets">';
            $output .= '<a class="film-trailer" href="' . $ticket_purchase_link . '">Buy Tickets</a>';
          $output .= '</div><!-- /.film-teaser--buy-tickets -->';
        $output .= '</div><!-- /.film-teaser--links -->';

        $output .= '<h2 class="film-teaser--title"><a href="' . $this_link . '">' . get_the_title() . '</a></h2>';

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
          $output .= '<div class="film-teaser--screening-range">';
            $first_screening_disp = date('M j', strtotime($first_screening));
            $last_screening_disp = date('M j', strtotime($last_screening));
            $output .= 'Playing ' . $first_screening_disp;
            if ( $last_screening_disp != $first_screening_disp ) {
              $output .= ' through ' . $last_screening_disp;
            }
          $output .= '</div><!-- /.film-teaser--screening-range -->';
        $output .= '</div><!-- /.film-teaser--film-info -->';

        $output .= '<div class="film-teaser--content">';
          $output .= $this_description;
          $output .= $this_addl_info;
        $output .= '</div><!-- /.film-teaser--content -->';

        $output .= '<div class="film-teaser--screenings">';
          $output .= do_shortcode('[film_showtimes]');
        $output .= '</div><!-- /.film-teaser--screenings -->';

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
