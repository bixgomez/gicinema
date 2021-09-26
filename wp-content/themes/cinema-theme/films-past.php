<?php
/*
Template Name: Past Films
*/

get_header();
$container   = get_theme_mod( 'understrap_container_type' );

?>

<!-- films-past -->

  <div class="wrapper" id="single-wrapper">

    <div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

      <main class="site-main" id="main">

        <?php

        while ( have_posts() ) : the_post();
          the_title( '<h1 class="entry-title">', '</h1>' );
          endwhile; // end of the loop.

        ?>

      </main><!-- #main -->

    </div><!-- Container end -->

  </div><!-- Wrapper end -->

<?php get_footer(); ?>
