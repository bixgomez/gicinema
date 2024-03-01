<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once get_template_directory() . '/inc/functions/film-card.php';

$filmId = $_POST['filmId'];
$filmPostId = 0;

// WP_Query arguments
$args = array (
  'post_type' => 'film',
  'posts_per_page' => '1',
  'meta_key' => 'agile_film_id',
  'meta_value' => $filmId
);

// The Query
$getThePostId = new WP_Query( $args );

// The Loop
if ( $getThePostId->have_posts() ) :
  while ( $getThePostId->have_posts() ) : $getThePostId->the_post(); 
    $filmPostId = get_the_ID();
    filmCard(filmPostId:$filmPostId);
  endwhile;
endif;

// Restore original Post Data
wp_reset_query();
