<?php
/* Plugin Name: GI Cinema Get Shows
 * Plugin URI:  https://grandillusioncinema.org/
 * Description: Retrieves the most recently added shows..
 * Version:     1.0.0
 * Author:      Richard Gilbert
 * Author URI:  https://grandillusioncinema.org/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

add_shortcode( 'external_data', 'external_data_callback' );

// add_action( 'admin_init', 'admin_callback_function' );

function external_data_callback() {

  // $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json';
  $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
  $args = array( 'method' => 'GET' );
  $response = wp_remote_get( $url, $args );

  if ( is_wp_error( $response ) ) {
    $error_msg = $response->get_error_message();
		return "Something went wrong: $error_message";
	}

	$results = json_decode( wp_remote_retrieve_body( $response ) );

  foreach( $results->ArrayOfShows as $show ) {

    // Declare variables with initial default values.
    $short_description = '';
    $duration = '';
    $info_link = '';
    $film_year = '';
    $format = '';
    $film_director = '';
    $country = '';
    $format = '';
    $screeningsParagraph = '';
    $poster_url = '';
    $trailer_url = '';
    $screening_first = '';
    $screening_last = '';

    // Set initial (simple) values.
    $film_id = $show->ID;
    $film_title = $show->Name;
    $duration = $show->Duration;
    $short_description = $show->ShortDescription;
    $info_link = $show->InfoLink;

    echo '<div style="background-color: white; padding: 10px; font-size: 11px; margin-bottom: 20px">';
    echo '<b>ID:</b> ' . $film_id . '<br>';
    echo '<b>Name:</b> ' . $film_title . '<br>';
    echo '<b>Duration:</b> ' . $duration . '<br>';
    echo '<b>Short Description:</b> ' . $short_description . '<br>';
    echo '<b>Info Link:</b> ' . $info_link . '<br>';

    // Set values for media variables.
    foreach( $show->AdditionalMedia as $addlMedia ) {
      if ( $addlMedia->Type == 'Image' ) {
        echo '<b>Image:</b> ' . $addlMedia->Value . '<br>';
        $poster_url = $addlMedia->Value;
      }
      if ( $addlMedia->Type == 'YouTube' ) {
        echo '<b>YouTube:</b> ' . $addlMedia->Value . '<br>';
        $trailer_url = $addlMedia->Value;
      }
    }

    foreach( $show->CustomProperties as $customProp ) {
      if ( $customProp->Name == 'Release Year' ) {
        echo '<b>Year:</b> ' . $customProp->Value . '<br>';
        $film_year = $customProp->Value;
      }
      if ( $customProp->Name == 'Format' ) {
        echo '<b>Format:</b> ' . $customProp->Value . '<br>';
        $format = $customProp->Value;
      }
      if ( $customProp->Name == 'Format' ) {
        echo '<b>Format:</b> ' . $customProp->Value . '<br>';
        $format = $customProp->Value;
      }
      if ( $customProp->Name == 'Director' ) {
        echo '<b>Director:</b> ' . $customProp->Value . '<br>';
        if ( $film_director != '' ) { $film_director .= ', '; }
        $film_director .= $customProp->Value;
      }
      if ( $customProp->Name == 'Production Country' ) {
        if ( $country != '' ) { $country .= ', '; }
        echo '<b>Country:</b> ' . $customProp->Value . '<br>';
        $country .= $customProp->Value;
      }
    }

    $screening_first = '';
    $screening_last = '';

    $screeningsParagraph = '<p>';
    foreach( $show->CurrentShowings as $showing ) {
      $showDateTime = $showing->StartDate;
      $showDate = date('l, M j', strtotime($showDateTime));
      $showTime = date('g:i a', strtotime($showDateTime));
      $showDateTime = date('Y-m-d H:i:s', strtotime($showDateTime));
      if ($screeningsParagraph == '<p>') {
        $screeningsParagraph .= $showDate . ': ' . $showTime;
        $screening_first = $showDateTime;
      }
      elseif ( strpos($screeningsParagraph, $showDate) ) {
        $screeningsParagraph .= ', ' . $showTime;
      }
      else {
        $screeningsParagraph .= '<br>' . $showDate . ': ' . $showTime;
      }
      echo $showDate . ' ' . $showTime;
      echo '<br>';
    }
    $screeningsParagraph .= '</p>';
    $screening_last = $showDateTime;
    echo '<hr>' . $screeningsParagraph . '<hr>';

    $existingFilms = get_posts([
      'post_type'  => 'film',
      'title' => $film_title,
    ]);

    if ( empty($existingFilms) ) {
      echo '<b><i>Creating new film...</i></b><br>';
      // Create post object
      $newMovie = array(
        'post_type'     => 'film',
        'post_title'    => wp_strip_all_tags( $film_title ),
        'post_status'   => 'publish'
      );
      // Insert the post into the database
      $newMovieID = wp_insert_post($newMovie);
      add_post_meta($newMovieID, 'description', $short_description, true);
      add_post_meta($newMovieID, 'film_length', $duration, true);
      add_post_meta($newMovieID, 'ticket_purchase_link', $info_link, true);
      add_post_meta($newMovieID, 'film_year', $film_year, true);
      add_post_meta($newMovieID, 'format', $format, true);
      add_post_meta($newMovieID, 'film_director', $film_director, true);
      add_post_meta($newMovieID, 'country', $country, true);
      add_post_meta($newMovieID, 'format', $format, true);
      add_post_meta($newMovieID, 'film_screenings', $screeningsParagraph, true);
      add_post_meta($newMovieID, 'poster_url', $poster_url, true);
      add_post_meta($newMovieID, 'trailer_url', $trailer_url, true);
      add_post_meta($newMovieID, 'screening_first', $screening_first, true);
      add_post_meta($newMovieID, 'screening_last', $screening_last, true);
    }
    else {
      foreach ( $existingFilms as $existingFilm ) {
        $existingFilm = get_post( $existingFilm );
        $existingFilmID = $existingFilm->ID;
        echo '<b><i>Updating existing film ('.$existingFilmID.')</i></b><br>';
        update_post_meta($existingFilmID, 'description', $short_description);
        update_post_meta($existingFilmID, 'film_length', $duration);
        update_post_meta($existingFilmID, 'ticket_purchase_link', $info_link);
        update_post_meta($existingFilmID, 'film_year', $film_year);
        update_post_meta($existingFilmID, 'format', $format);
        update_post_meta($existingFilmID, 'film_director', $film_director);
        update_post_meta($existingFilmID, 'country', $country);
        update_post_meta($existingFilmID, 'format', $format);
        update_post_meta($existingFilmID, 'film_screenings', $screeningsParagraph);
        update_post_meta($existingFilmID, 'poster_url', $poster_url);
        update_post_meta($existingFilmID, 'trailer_url', $trailer_url);
        update_post_meta($existingFilmID, 'screening_first', $screening_first);
        update_post_meta($existingFilmID, 'screening_last', $screening_last);
      }
    }

    echo '</div>';
  }
}
