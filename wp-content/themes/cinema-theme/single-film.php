<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();
$container   = get_theme_mod( 'understrap_container_type' );
?>

<!-- single-film -->

<div class="wrapper" id="single-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

    <!-- Do the left sidebar check -->
    <?php get_template_part( 'global-templates/left-sidebar-check' ); ?>

    <main class="site-main" id="main">

      <?php

      $output = '';

      while ( have_posts() ) : the_post();

        $this_post_id = get_the_ID();
        $output .= do_shortcode('[film_teaser film_id="' . $this_post_id .'"]');

        print $output;

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
          comments_template();
        endif;

      endwhile; // end of the loop.

      ?>

    </main><!-- #main -->

		<!-- Do the right sidebar check -->
		<?php get_template_part( 'global-templates/right-sidebar-check' ); ?>

  </div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
