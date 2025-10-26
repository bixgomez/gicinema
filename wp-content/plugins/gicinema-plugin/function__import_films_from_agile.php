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

    // Ensure DB schema/index is in place even when running via WP-Cron
    if (function_exists('gicinema__ensure_screenings_unique_index')) {
        gicinema__ensure_screenings_unique_index();
    }

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
    $refreshed_feed = false;

    if (false === $results) {
        echo '<div>The transient does not exist, so call the function to update it.</div>';
        gicinema__update_agile_shows_array();
        $refreshed_feed = true;

        // After updating, try to get the transient again
        $results = get_transient('agile_shows_array');
    }

    // Be resilient to JSON structure and environments (array/object + BOM)
    $agile_shows_array = [];
    $decoded_assoc = [];
    if (is_string($results) && $results !== '') {
        // Remove UTF-8 BOM if present
        $clean = preg_replace('/^\xEF\xBB\xBF/', '', $results);
        $decoded_assoc = json_decode($clean, true);
        if (!is_array($decoded_assoc)) {
            // Try as object structure
            $decoded_obj = json_decode($clean);
            if (is_object($decoded_obj)) {
                if (isset($decoded_obj->ArrayOfShows)) {
                    $tmp = $decoded_obj->ArrayOfShows;
                    if (is_object($tmp)) { $tmp = (array) $tmp; }
                    if (is_array($tmp)) { $agile_shows_array = $tmp; }
                } elseif (isset($decoded_obj->Shows)) {
                    $tmp = $decoded_obj->Shows;
                    if (is_object($tmp)) { $tmp = (array) $tmp; }
                    if (is_array($tmp)) { $agile_shows_array = $tmp; }
                }
            }
        }
    }

    // If assoc decode worked, prefer it
    if (empty($agile_shows_array) && is_array($decoded_assoc)) {
        if (isset($decoded_assoc['ArrayOfShows']) && is_array($decoded_assoc['ArrayOfShows'])) {
            $agile_shows_array = $decoded_assoc['ArrayOfShows'];
        } elseif (isset($decoded_assoc['Shows']) && is_array($decoded_assoc['Shows'])) {
            $agile_shows_array = $decoded_assoc['Shows'];
        }
    }

    // If still empty or failed to decode, try refreshing the transient once
    if (empty($agile_shows_array)) {
        echo '<div class="notice notice-warning"><p>Unable to decode Agile feed JSON from transient; refreshing…</p></div>';
        gicinema__update_agile_shows_array();
        $refreshed_feed = true;
        $results = get_transient('agile_shows_array');
        if (is_string($results) && $results !== '') {
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', $results);
            $decoded_assoc = json_decode($clean, true);
            if (is_array($decoded_assoc)) {
                if (isset($decoded_assoc['ArrayOfShows']) && is_array($decoded_assoc['ArrayOfShows'])) {
                    $agile_shows_array = $decoded_assoc['ArrayOfShows'];
                } elseif (isset($decoded_assoc['Shows']) && is_array($decoded_assoc['Shows'])) {
                    $agile_shows_array = $decoded_assoc['Shows'];
                }
            } else {
                $decoded_obj = json_decode($clean);
                if (is_object($decoded_obj)) {
                    if (isset($decoded_obj->ArrayOfShows)) {
                        $tmp = $decoded_obj->ArrayOfShows;
                        if (is_object($tmp)) { $tmp = (array) $tmp; }
                        if (is_array($tmp)) { $agile_shows_array = $tmp; }
                    } elseif (isset($decoded_obj->Shows)) {
                        $tmp = $decoded_obj->Shows;
                        if (is_object($tmp)) { $tmp = (array) $tmp; }
                        if (is_array($tmp)) { $agile_shows_array = $tmp; }
                    }
                }
            }
        }
    }

    if (empty($agile_shows_array)) {
        echo '<div class="notice notice-error"><p>Unable to decode Agile feed JSON after refresh. See details above in the fetch log.</p></div>';
        echo '</div>';
        return;
    }

    $count_shows = is_array($agile_shows_array) ? count($agile_shows_array) : 0;
    echo '<div>Found ' . intval($count_shows) . ' films in the cached feed (transient).</div>';
    echo '<i>Looping through films from cached feed (transient)…</i>';

    // Record a lightweight log entry (keeps last 10 attempts)
    if (!function_exists('gicinema__append_import_log')) {
        function gicinema__append_import_log($entry) {
            $log = get_option('gicinema_import_log');
            if (!is_array($log)) { $log = []; }
            $log[] = $entry;
            // Keep only the last 10
            if (count($log) > 10) {
                $log = array_slice($log, -10);
            }
            update_option('gicinema_import_log', $log, false);
        }
    }
    $context = (defined('DOING_CRON') && DOING_CRON) ? 'cron' : (is_admin() ? 'admin' : 'frontend');
    gicinema__append_import_log([
        'time'      => time(),
        'count'     => (int) $count_shows,
        'context'   => $context,
        'refreshed' => (bool) $refreshed_feed,
    ]);

    foreach ($agile_shows_array as $show) {

        // Normalize show to object for consistent property access
        if (is_array($show)) {
            $show = (object) $show;
        }

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
        $additionalMedia = isset($show->AdditionalMedia) ? $show->AdditionalMedia : [];
        if (is_object($additionalMedia)) { $additionalMedia = (array) $additionalMedia; }
        if (!is_array($additionalMedia)) { $additionalMedia = []; }
        foreach ($additionalMedia as $addlMedia) {
            if (is_array($addlMedia)) { $addlMedia = (object) $addlMedia; }
            if (isset($addlMedia->Type) && $addlMedia->Type == 'Image' && isset($addlMedia->Value)) {
                $poster_url = $addlMedia->Value;
            }
            if (isset($addlMedia->Type) && $addlMedia->Type == 'YouTube' && isset($addlMedia->Value)) {
                $trailer_url = $addlMedia->Value;
            }
        }

        // Set values for custom properties.
        $customProps = isset($show->CustomProperties) ? $show->CustomProperties : [];
        if (is_object($customProps)) { $customProps = (array) $customProps; }
        if (!is_array($customProps)) { $customProps = []; }
        foreach ($customProps as $customProp) {
            if (is_array($customProp)) { $customProp = (object) $customProp; }
            if (isset($customProp->Name) && $customProp->Name == 'Release Year' && isset($customProp->Value)) {
                $film_year = $customProp->Value;
            }
            if (isset($customProp->Name) && $customProp->Name == 'Format' && isset($customProp->Value)) {
                $format = $customProp->Value;
            }
            if (isset($customProp->Name) && $customProp->Name == 'Director' && isset($customProp->Value)) {
                if ($film_director != '') {
                    $film_director .= ', ';
                }
                $film_director .= $customProp->Value;
            }
            if (isset($customProp->Name) && $customProp->Name == 'Production Country' && isset($customProp->Value)) {
                if ($country != '') {
                    $country .= ', ';
                }
                $country .= $customProp->Value;
            }
        }

        // (H4 with link + API data block are rendered after we know the post ID)

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

        // Now that we have a post ID, render the title as a link to the edit screen, then show API data
        $edit_link = get_edit_post_link($post_ID, '');
        if ($edit_link) {
            echo '<h4><a href="' . esc_url($edit_link) . '" target="_blank" rel="noopener noreferrer">' . esc_html($film_title) . '</a></h4>';
        } else {
            echo '<h4>' . esc_html($film_title) . '</h4>';
        }
        echo '<div class="function-info scrolly">';
        echo '<h5>The data from the API feed</h5>';
        echo '<div>$film_title = ' . esc_html($film_title) . '</div>';
        echo '<div>$agile_film_id = ' . esc_html($agile_film_id) . '</div>';
        echo '<div>$short_description = ' . esc_html($short_description) . '</div>';
        echo '<div>$duration = ' .  esc_html($duration) . '</div>';
        echo '<div>$info_link = ' .  esc_html($info_link) . '</div>';
        echo '<div>$film_year = ' .  esc_html($film_year) . '</div>';
        echo '<div>$format = ' .  esc_html($format) . '</div>';
        echo '<div>$film_director = ' .  esc_html($film_director) . '</div>';
        echo '<div>$country = ' .  esc_html($country) . '</div>';
        echo '<div>$poster_url = ' .  esc_html($poster_url) . '</div>';
        echo '<div>$trailer_url = ' .  esc_html($trailer_url) . '</div>';
        echo '</div>';

        // Handle poster image (for both new and existing films)
        $current_poster_url = get_field('poster_url', $post_ID);

        if (!empty($poster_url) && filter_var($poster_url, FILTER_VALIDATE_URL) !== false) {

            if ($current_poster_url !== $poster_url) {
                echo '<div class="success">New poster URL detected, downloading image.</div>';

                // Use WordPress HTTP API to fetch the image (more reliable on production)
                $insert_id = $post_ID;
                $image_url = $poster_url;
                $upload_dir = wp_upload_dir();

                $response = wp_remote_get($image_url, [
                    'timeout' => 15,
                    'redirection' => 5,
                ]);

                if (!is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
                    $image_data = wp_remote_retrieve_body($response);
                    $filename = basename(parse_url($image_url, PHP_URL_PATH));
                    if (!$filename) { $filename = 'image-' . uniqid() . '.jpg'; }

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
                    $err = is_wp_error($response) ? $response->get_error_message() : ('HTTP ' . wp_remote_retrieve_response_code($response));
                    echo '<div class="failure">Failed to download image: ' . esc_html($err) . '</div>';
                }
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
        $screenings_array = isset($show->CurrentShowings) ? $show->CurrentShowings : [];
        if (is_object($screenings_array)) { $screenings_array = (array) $screenings_array; }

        gicinema__import_screenings_from_agile(
            $screenings_array,
            'field_screenings',
            'screenings',
            'screening',
            $post_ID,
            $agile_film_id
        );

        // Immediately sync screenings to ACF field so they're visible on frontend
        gicinema__sync_screenings($post_ID);

        echo '</div>';
    }

    echo '</div>';
}
