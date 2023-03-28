<?php
require get_template_directory() . '/inc/functions/film-card.php';

function film_teaser_function($atts = [], $content = null, $tag = '') {

    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    // override default attributes with user attributes
    $filmteaser_atts = shortcode_atts([
        'film_id' => '0',
    ], $atts, $tag);

    $film_id = $filmteaser_atts['film_id'];

    if (is_numeric($film_id)) {
        $query = new WP_Query(array('p' => $film_id, 'post_type' => 'film'));
    } else {
        $query = new WP_Query(array('name' => $film_id, 'post_type' => 'film'));
    }

    $output = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $filmPostId = get_the_ID();
            filmCard($filmPostId);
        }
    }
}

function film_teaser_init() {
    add_shortcode('film_teaser', 'film_teaser_function');
}

add_action('init', 'film_teaser_init');
