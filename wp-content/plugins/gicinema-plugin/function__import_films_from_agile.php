<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
    exit;
}

// Import functions that will be needed in this template.
require_once "function__import_screenings_from_agile.php";
require_once "function__dedupe_screenings_table.php";
require_once "function__update_agile_shows_array.php";

function gicinema__import_films_from_agile() {

    // CSRF Protection - only when called via admin form
    if (isset($_POST['confirm_import'])) {
        if (!isset($_POST['import_nonce']) || !wp_verify_nonce($_POST['import_nonce'], 'import_films_action')) {
            echo '<div class="notice notice-error"><p>Security check failed</p></div>';
            return;
        }
    }

    echo '<div class="function-info">';

    global $wpdb;

    echo '<h3>OK, let us import films from Agile!</h3>';

    $results = get_transient('agile_shows_array');

    if (false === $results) {
        echo '<div>The transient does not exist, so call the function to update it.</div>';
        gicinema__update_agile_shows_array();

        // After updating, try to get the transient again
        $results = get_transient('agile_shows_array');
    }

    $agile_shows_array = json_decode($results)->ArrayOfShows;

    echo '<i>Looping through all the films in the feed...</i>';

    foreach ($agile_shows_array as $show) {

        // Declare variables with initial default values.
        $short_description = '';
        $duration = '';
        $info_link = '';
        $film_year = '';
        $format = '';
        $film_director = '';
        $country = '';
        $screeningsParagraph = '';
        $poster_url = '';
        $trailer_url = '';

        // Set initial (simple) values.
        $agile_film_id = $show->ID;
        $film_title = wp_strip_all_tags($show->Name);
        $duration = $show->Duration;
        $short_description = $show->ShortDescription;
        $info_link = $show->InfoLink;

        echo '<div class="function-info">';

        // Set values for media variables.
        foreach ($show->AdditionalMedia as $addlMedia) {
            if ($addlMedia->Type == 'Image') {
                $poster_url = $addlMedia->Value;
            }
            if ($addlMedia->Type == 'YouTube') {
                $trailer_url = $addlMedia->Value;
            }
        }

        // Set values for custom properties.
        foreach ($show->CustomProperties as $customProp) {
            if ($customProp->Name == 'Release Year') {
                $film_year = $customProp->Value;
            }
            if ($customProp->Name == 'Format') {
                $format = $customProp->Value;
            }
            if ($customProp->Name == 'Director') {
                if ($film_director != '') {
                    $film_director .= ', ';
                }
                $film_director .= $customProp->Value;
            }
            if ($customProp->Name == 'Production Country') {
                if ($country != '') {
                    $country .= ', ';
                }
                $country .= $customProp->Value;
            }
        }

        // Display all the values.
        echo '<h4>' . $film_title . '</h4>';
        echo '<div class="function-info scrolly">';
        echo '<h5>The data from the API feed</h5>';
        echo '<div>$film_title = ' . $film_title . '</div>';
        echo '<div>$agile_film_id = ' . $agile_film_id . '</div>';
        echo '<div>$short_description = ' . $short_description . '</div>';
        echo '<div>$duration = ' .  $duration . '</div>';
        echo '<div>$info_link = ' .  $info_link . '</div>';
        echo '<div>$film_year = ' .  $film_year . '</div>';
        echo '<div>$format = ' .  $format . '</div>';
        echo '<div>$film_director = ' .  $film_director . '</div>';
        echo '<div>$country = ' .  $country . '</div>';
        echo '<div>$poster_url = ' .  $poster_url . '</div>';
        echo '<div>$trailer_url = ' .  $trailer_url . '</div>';
        echo '</div>';

        // Query to find the WordPress film posts with this Agile film id.
        echo '<div>Checking WordPress posts for film with Agile ID of ' . $agile_film_id . ')</div>';
        $existingFilmPost = get_posts([
            'post_type'      => 'film',
            'posts_per_page' => 1, // Limit to only 1 post
            'meta_query'     => [
                [
                    'key'   => 'agile_film_id',
                    'value' => $agile_film_id,
                    'compare' => '=',
                ],
            ],
        ]);

        // If no existing films were found, create a new film.
        if (empty($existingFilmPost)) {

            echo '<div class="failure">No existing film found.</div>';
            echo '<div>Creating new WordPress post of type "film"</div>';

            // Create post object
            $newMovie = array(
                'post_type'     => 'film',
                'post_title'    => $film_title,
                'post_status'   => 'publish'
            );

            // Insert the post into the database
            wp_cache_set('skip_gicinema_update', true, '', 60);
            $post_ID = wp_insert_post($newMovie);

            echo '<div>The post_id of the new film post is ' . $post_ID . '</div>';
        } else {

            $post_ID = $existingFilmPost[0]->ID;
            echo '<div class="success">Existing film found</div>';
        }

        // Handle poster image (for both new and existing films)
        $current_poster_url = get_field('poster_url', $post_ID);

        if (!empty($poster_url) && filter_var($poster_url, FILTER_VALIDATE_URL) !== false) {

            if ($current_poster_url !== $poster_url) {
                echo '<div class="success">New poster URL detected, downloading image.</div>';

                // https://wordpress.stackexchange.com/questions/256830/programmatically-adding-images-to-media-library
                $insert_id = $post_ID;
                $image_url = $poster_url;
                $upload_dir = wp_upload_dir();

                $image_data = file_get_contents($image_url);
                $filename = basename($image_url);
                if (wp_mkdir_p($upload_dir['path'])) {
                    $file = $upload_dir['path'] . '/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                }
                file_put_contents($file, $image_data);
                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment($attachment, $file);
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                wp_update_attachment_metadata($attach_id, $attach_data);

                // And finally assign featured image to post
                echo '<div>Inserting image ' . $attach_id . ' into film ' . $insert_id . '</div>';
                set_post_thumbnail($insert_id, $attach_id);
                update_field('film_poster', $attach_id, $insert_id);
            } else {
                echo '<div>Poster URL unchanged, skipping image download.</div>';
            }
        } else {
            echo '<div class="failure">The $image_url is either empty or invalid.</div>';
        }

        echo '<div>Updating ACF fields with data from Agile</div>';

        update_post_meta($post_ID, 'agile_film_id', $agile_film_id);
        update_post_meta($post_ID, 'description', $short_description);
        update_post_meta($post_ID, 'film_length', $duration);
        update_post_meta($post_ID, 'ticket_purchase_link', $info_link);
        update_post_meta($post_ID, 'film_year', $film_year);
        update_post_meta($post_ID, 'format', $format);
        update_post_meta($post_ID, 'film_director', $film_director);
        update_post_meta($post_ID, 'country', $country);
        update_post_meta($post_ID, 'poster_url', $poster_url);
        update_post_meta($post_ID, 'trailer_url', $trailer_url);

        // Create variable for future screenings array from Agile.
        $screenings_array = $show->CurrentShowings;

        gicinema__import_screenings_from_agile(
            $screenings_array,
            'field_screenings',
            'screenings',
            'screening',
            $post_ID,
            $agile_film_id
        );

        echo '</div>';
    }

    echo '</div>';

    echo '<div class="function-info">';
    gicinema__dedupe_screenings_table();
    echo '</div>';
}
