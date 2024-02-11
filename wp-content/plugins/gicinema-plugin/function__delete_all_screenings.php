<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

    // Function to delete all screenings data
    function delete_all_screenings() {
        // Query all 'film' posts
        $args = array(
            'post_type' => 'film',
            'posts_per_page' => -1, // Retrieve all posts
        );
        $film_posts = get_posts($args);

        // Loop through each 'film' post
        foreach ($film_posts as $post) {
            // Use ACF function to delete the 'screenings' repeater field for the post
            delete_field('screenings', $post->ID);
        }
    }

}
