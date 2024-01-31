<?php

function delete_all_films() {
    function delete_all_posts_of_post_type() {
        $args = array(
            'post_type' => 'film',
            'posts_per_page' => -1,
        );
        $posts = get_posts($args);
        echo '<div>';
        echo '<h2>Deleting ALL films!</h2>';
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true); // The second parameter is set to 'true' to force delete
            echo 'Deleting film ' . $post->ID . '<br />';
        }
        echo '</div>';
    }
    
    delete_all_posts_of_post_type('film');
}
