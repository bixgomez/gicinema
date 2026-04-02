<?php
/**
 * The header for our theme.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Cinema_Theme
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<!-- Preload critical fonts for faster rendering -->
	<link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/WorkSans-Regular.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/Anton-Regular.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/Brothers-Regular.woff" as="font" type="font/woff" crossorigin>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div class="site-wrapper">

  <div class="site-container">

    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'cinema_theme' ); ?></a>

    <header class="section-outer section-outer--site-header">
      <div class="section section--site-header">
        <div class="section-inner section-inner--site-header">

          <div class="content-layout content-layout--header">

            <div class="title-area">
              <div class="title-grid">
                <nav class="social-navigation">
                  <?php
                  wp_nav_menu( array(
                    'menu'    => 'social-media-menu',
                    'menu_id' => 'social-media-menu',
                  ) );
                  ?>
                </nav>
                <a class="logo" href="/"></a>
                <h1 class="site-title">
                  <span class="article">The</span> Grand Illusion
                </h1>
                <!-- <h2 class="site-subtitle">
                  <span class="line-1">Seattle's oldest continuously</span>
                  <span class="line-2">running movie theater</span>
                </h2> -->
                <!-- <a href="/" class="home-link">The Grand Illusion: <?php bloginfo( 'description' ); ?></a> -->
              </div>
            </div>

            <div class="menu-area">
              <a class="toggle" href="#" role="button"><span>MENU</span></a>
              <nav class="main-navigation">
                <?php
                wp_nav_menu( array(
                  'theme_location' => 'menu-1',
                  'menu_id'        => 'primary-menu',
                ) );
                ?>
              </nav>
            </div>

            <div class="donate-area">
              <a href="/tickets-membership/" class="donate-button">Donate Now!</a>
            </div>

          </div>
        </div>
      </div>
    </header>

    <main id="content" class="section-outer section-outer--site-content">
      <div class="section section--site-content">
        <div class="section-inner section-inner--site-content">
