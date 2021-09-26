<?php
/*
Template Name: Home Page (Upcoming Films)
*/

get_header();
$container   = get_theme_mod( 'understrap_container_type' );

?>

<!-- page--home -->

<div class="content-layout no-sidebar">
  <main class="site-main <?php echo esc_attr( $container ); ?>">
    <?php
    while ( have_posts() ) :
      the_post();
      the_title( '<h1 class="entry-title">', '</h1>' );

      get_alerts();

      the_content();
    endwhile;
    ?>
  </main>
</div>

<?php get_footer(); ?>
