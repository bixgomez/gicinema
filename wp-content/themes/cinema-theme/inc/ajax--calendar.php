<?php
require_once get_template_directory() . '/inc/functions/film-card.php';

$filmId = $_POST['filmId'];
$filmPostId = 0;

// echo 'This film\'s Agile ID is ' . $filmId . '<br>';

// WP_Query arguments
$args = array (
  'post_type' => 'film',
  'posts_per_page' => '1',
  'meta_key' => 'agile_film_id',
  'meta_value' => $filmId
);

// var_dump($args);

// The Query
$getThePostId = new WP_Query( $args );

// echo $getThePostId->request;

// The Loop
if ( $getThePostId->have_posts() ) :
  while ( $getThePostId->have_posts() ) : $getThePostId->the_post(); 
    $filmPostId = get_the_ID();
    filmCard(filmPostId:$filmPostId);
  endwhile;
else:
  // no posts found
endif;

// Restore original Post Data
wp_reset_query();

?>