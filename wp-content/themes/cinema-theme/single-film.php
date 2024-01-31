<?php
/**
 * The template for displaying all single films.
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();
?>

<!-- single-film.php -->

<div class="wrapper" id="single-wrapper">
	<div id="content" tabindex="-1">
    <main class="site-main" id="main">

      <?php

      $output = '';

      while ( have_posts() ) : the_post();

        $this_post_id = get_the_ID();
        
        filmCard($this_post_id);
        // $output .= do_shortcode('[film_teaser film_id="' . $this_post_id .'"]');
        // print $output;

      endwhile; // end of the loop.

      ?>

    </main><!-- #main -->
  </div><!-- Container end -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
