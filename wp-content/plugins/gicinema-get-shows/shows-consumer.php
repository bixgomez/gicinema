<?php /* Shows consumer. */ ?>

<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

function shows_consumer() {

  $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json';
  // $url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';

	$args = array(
		'method' => 'GET'
	);
	$response = wp_remote_get( $url, $args );
	if ( is_wp_error( $response ) ) {
		$error_msg = $response->get_error_message();
		return "Something went wrong: $error_message";
	}

	$results = json_decode( wp_remote_retrieve_body( $response ) );

	foreach( $results->ArrayOfShows as $show ) {
		echo '<div>';
		echo '<b>ID:</b> ' . $show->ID . '<br>';
		echo '<b>Name:</b> ' . $show->Name . '<br>';

        $matchingFilms = get_posts([
            'post_type'  => 'film',
            'title' => $show->Name,
        ]);

        if ( empty($matchingFilms) ) {
            echo '<b><i>Creating new film...</i></b><br>';
	        // Create post object
	        $newMovie = array(
	            'post_type'     => 'film',
		        'post_title'    => wp_strip_all_tags( $show->Name ),
		        'post_status'   => 'publish'
	        );
	        // Insert the post into the database
	        $newMovieID = wp_insert_post($newMovie);
	        add_post_meta($newMovieID, 'description', $show->ShortDescription, true);
	        add_post_meta($newMovieID, 'film_length', $show->Duration, true);

	        foreach( $show->CustomProperties as $customProp ) {

	            echo $customProp->Name;

		        if ( $customProp->Name == 'Release Year' ) {
                    echo '<b>Year:</b> ' . $customProp->Value . '<br>';
                    add_post_meta($newMovieID, 'film_year', $customProp->Value, true);
		        }

		        if ( $customProp->Name == 'Format' ) {
			        echo '<b>Format:</b> ' . $customProp->Value . '<br>';
			        add_post_meta($newMovieID, 'format', $customProp->Value, true);
		        }

		        if ( $customProp->Name == 'Director' ) {
			        echo '<b>Director:</b> ' . $customProp->Value . '<br>';
			        add_post_meta($newMovieID, 'film_director', $customProp->Value, true);
		        }
	        }

        }
        else {
	        echo 'UPDATE (maybe)!<br>';
	        foreach ( $matchingFilms as $matchingFilm ) {
	            $thisMatchingFilm = get_post( $matchingFilm );
	            print_r($thisMatchingFilm);
	        }
        }

		echo '</div><hr />';
	}
}

shows_consumer();
