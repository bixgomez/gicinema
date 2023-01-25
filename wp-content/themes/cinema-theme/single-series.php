<?php
/**
 * The template for displaying all single series posts.
 *
 * @package understrap
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header();
$container = get_theme_mod('understrap_container_type');
?>

<!-- single-series.php -->

<div class="wrapper" id="single-wrapper">

    <div class="<?php echo esc_attr($container); ?>" id="content" tabindex="-1">

        <!-- Do the left sidebar check -->
        <?php get_template_part('global-templates/left-sidebar-check'); ?>

        <main class="site-main" id="main">

            <?php
            $image = get_field('series_logo');
            $size = 'medium'; // (thumbnail, medium, large, full or custom size)
            ?>

            <div class="series-header">
                <?php if ($image) {
                    echo '<div class="series-header-image">' . wp_get_attachment_image($image, $size) . '</div>';
                } ?>
                <div class="series-header-info">
                    <h1 class="series-title"><?php the_title(); ?></h1>
                    <h2 class="series-dates"><?php the_field('series_dates'); ?></h2>
                    <h3 class="series-subtitle"><?php the_field('series_subtitle'); ?></h3>
                    <?php the_field('series_description'); ?>
                </div>
            </div>

            <?php the_content(); ?>

        </main><!-- #main -->

    </div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
