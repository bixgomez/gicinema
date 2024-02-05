<?php
require_once "function__import_screenings_from_agile.php";

function import_films_from_agile() {
    global $wpdb;

    $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';
    $args = array( 'method' => 'GET' );
    $response = wp_remote_get( $url, $args );

    if ( is_wp_error( $response ) ) {
        $error_msg = $response->get_error_message();
        return "Something went wrong: $error_message";
    }

    $results = json_decode( wp_remote_retrieve_body( $response ) );
    $agile_shows_array = $results->ArrayOfShows;

    foreach( $agile_shows_array as $show ) {

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

        // Set initial (simple) values.
        $agile_film_id = $show->ID;
        $film_title = wp_strip_all_tags( $show->Name );
        $duration = $show->Duration;
        $short_description = $show->ShortDescription;
        $info_link = $show->InfoLink;

        echo '<div style="background-color: white; padding: 12px; font-size: 14px; margin: 10px 0 20px;">';

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

        $existingFilms = get_posts([
            'post_type'  => 'film',
            'title' => $film_title,
        ]);

        // Display all the values.
        echo '<h4>' . $film_title . '</h4><hr>';
        echo '$film_title = ' . $film_title . '<br>';
        echo '$agile_film_id = ' . $agile_film_id . '<hr>';
        echo '$short_description = ' . $short_description . '<hr>';
        echo '$duration = ' .  $duration . '<br>';
        echo '$info_link = ' .  $info_link . '<br>';
        echo '$film_year = ' .  $film_year . '<br>';
        echo '$format = ' .  $format . '<br>';
        echo '$film_director = ' .  $film_director . '<br>';
        echo '$country = ' .  $country . '<br>';
        echo '$format = ' .  $format . '<br>';
        echo '$poster_url = ' .  $poster_url . '<br>';
        echo '$trailer_url = ' .  $trailer_url . '<hr>';

        // If no existing films were found, create a new film.
        if ( empty($existingFilms) ) {

            echo '<b><i>Creating new film...</i></b><br>';
            // Create post object
            $newMovie = array(
                'post_type'     => 'film',
                'post_title'    => $film_title,
                'post_status'   => 'publish'
            );
            // Insert the post into the database
            $this_film_ID = wp_insert_post($newMovie);

            // https://wordpress.stackexchange.com/questions/256830/programmatically-adding-images-to-media-library
            $insert_id = $this_film_ID;
            $image_url = $poster_url;
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents( $image_url );
            $filename = basename( $image_url );
            if ( wp_mkdir_p( $upload_dir['path'] ) ) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }
            file_put_contents( $file, $image_data );
            $wp_filetype = wp_check_filetype( $filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name( $filename ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            // And finally assign featured image to post
            echo 'Attempting to insert image '.$attach_id.' into film '.$insert_id.'...';
            set_post_thumbnail($insert_id, $attach_id);
            update_field( 'film_poster', $attach_id, $insert_id );

        } else {

            foreach ( $existingFilms as $existingFilm ) {
                $existingFilm = get_post( $existingFilm );
                $this_film_ID = $existingFilm->ID;
                echo '<h5 style="margin-bottom: 0;"><i>Updating existing film ('.$this_film_ID.')</i></h5>';
            }

        }

        echo '<br><b>Update custom fields with Agile data</b><br>';

        update_post_meta($this_film_ID, 'agile_film_id', $agile_film_id);
        update_post_meta($this_film_ID, 'description', $short_description);
        update_post_meta($this_film_ID, 'film_length', $duration);
        update_post_meta($this_film_ID, 'ticket_purchase_link', $info_link);
        update_post_meta($this_film_ID, 'film_year', $film_year);
        update_post_meta($this_film_ID, 'format', $format);
        update_post_meta($this_film_ID, 'film_director', $film_director);
        update_post_meta($this_film_ID, 'country', $country);
        update_post_meta($this_film_ID, 'format', $format);
        update_post_meta($this_film_ID, 'poster_url', $poster_url);
        update_post_meta($this_film_ID, 'trailer_url', $trailer_url);

        // Create variable for future screenings array from Agile.
        $screenings_array = $show->CurrentShowings;

        import_screenings_from_agile(
            $agile_array=$screenings_array, 
            $repeater_field_key='field_screenings',
            $repeater_field_name='screenings',
            $repeater_subfield_name='screening',
            $post_id=$this_film_ID,
        );

        echo '</div>';
    }
}
