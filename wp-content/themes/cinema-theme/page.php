<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Cinema_Theme
 */

get_header();
?>

<!-- page -->

<?php
$this_sidebar = get_post_meta( get_the_ID(), 'sidebar_content', true);
$extra_class = '';
if( ! empty( $this_sidebar ) ):
  $extra_class = 'has-sidebar';
else:
  $extra_class = 'no-sidebar';
endif;
?>

<div class="content-layout <?php echo $extra_class; ?>">

  <main class="site-main">
    <?php
    while ( have_posts() ) :
      the_post();
      the_title( '<h1 class="entry-title">', '</h1>' );
      the_content();

      // If comments are open or we have at least one comment, load up the comment template.
      if ( comments_open() || get_comments_number() ) :
        comments_template();
      endif;
    endwhile; // End of the loop.
    ?>
  </main>

  <?php
  if( ! empty( $this_sidebar ) ) {
    echo '<aside class="sidebar">' . apply_filters('the_content', $this_sidebar) . '</aside>';
  }
  ?>

</div>

<?php
get_footer();
