<?php
/**
 * Block Name: Film Teaser
 *
 * Description: Displays a film teaser/card.
 *
 * Resources:
 * https://alphaparticle.com/blog/custom-block-icons-with-acf-blocks/
 * https://joeyfarruggio.com/wordress/register-acf-blocks/
 */

require_once get_theme_file_path( 'inc/functions/function__get_screenings.php' );
require_once get_theme_file_path( 'inc/functions/film-card.php' );

// The block attributes
$block = $args['block'];

// The block data.
$data = $args['data'];

// The block ID.
$block_id = $args['block_id'];

// The block class names.
$class_name = $args['class_name'];

// Set the film id.
if ( $data['film']) {
    $film_id = $data['film'];
}

// Set up query
$film_block_args = array(
    'post_type' => 'film',
    'p' => $film_id,
);
$film_block_query = new WP_Query($film_block_args);

// Display query (for debugging)
// echo '<pre>' . $film_query->request . '</pre>';

// Display current film ID (for debugging)
// echo '<br>$film_id = ' . $film_id;
?>

<?php 
// If we have any results, display the film teaser card. 
if ( $film_block_query->have_posts() ) :
    while ( $film_block_query->have_posts() ) : $film_block_query->the_post();
        $filmPostId = get_the_ID();
        filmCard(filmPostId:$filmPostId);
    endwhile;
endif;
?>
