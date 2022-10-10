<?php

function shows_importer() {

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
        $film_title = wp_strip_all_tags( $show->Name );
        $duration = $show->Duration;
        $short_description = $show->ShortDescription;
        $info_link = $show->InfoLink;

        echo '<div style="background-color: white; padding: 12px; font-size: 12px; margin: 10px 0 20px;">';

        // Set values for media variables.
        foreach( $show->AdditionalMedia as $addlMedia ) {
            if ( $addlMedia->Type == 'Image' ) {
                $poster_url = $addlMedia->Value;
            }
            if ( $addlMedia->Type == 'YouTube' ) {
                $trailer_url = $addlMedia->Value;
            }
        }

        // Set values for custom properties.
        foreach( $show->CustomProperties as $customProp ) {
            if ( $customProp->Name == 'Release Year' ) {
                $film_year = $customProp->Value;
            }
            if ( $customProp->Name == 'Format' ) {
                $format = $customProp->Value;
            }
            if ( $customProp->Name == 'Format' ) {
                $format = $customProp->Value;
            }
            if ( $customProp->Name == 'Director' ) {
                if ( $film_director != '' ) { $film_director .= ', '; }
                $film_director .= $customProp->Value;
            }
            if ( $customProp->Name == 'Production Country' ) {
                if ( $country != '' ) { $country .= ', '; }
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
        }
        $screeningsParagraph .= '</p>';
        $screening_last = $showDateTime;

        $existingFilms = get_posts([
            'post_type'  => 'film',
            'title' => $film_title,
        ]);

        // Display all the values.
        echo '<h4>' . $film_title . '</h4><hr>';
        echo '$film_title = ' . $film_title . '<br>';
        echo '$film_id = ' . $film_id . '<hr>';
        echo '$short_description = ' . $short_description . '<hr>';
        echo '$duration = ' .  $duration . '<br>';
        echo '$info_link = ' .  $info_link . '<br>';
        echo '$film_year = ' .  $film_year . '<br>';
        echo '$format = ' .  $format . '<br>';
        echo '$film_director = ' .  $film_director . '<br>';
        echo '$country = ' .  $country . '<br>';
        echo '$format = ' .  $format . '<br>';
        echo '$poster_url = ' .  $poster_url . '<br>';
        echo '$trailer_url = ' .  $trailer_url . '<br>';
        echo '$screening_first = ' .  $screening_first . '<br>';
        echo '$screening_last = ' .  $screening_last . '<hr>';
        echo '$screeningsParagraph = ' .  $screeningsParagraph . '<hr>';

        if ( empty($existingFilms) ) {
            echo '<b><i>Creating new film...</i></b><br>';
            // Create post object
            $newMovie = array(
                'post_type'     => 'film',
                'post_title'    => $film_title,
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
                echo '<h5 style="margin-bottom: 0;"><i>Updating existing film ('.$existingFilmID.')</i></h5>';
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
