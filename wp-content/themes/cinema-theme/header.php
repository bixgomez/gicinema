<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
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
              <h1 class="site-title"><span class="article">The</span> Grand Illusion</h1>
              <h2 class="site-subtitle">
                <span class="line-1">Seattle's oldest continuously</span>
                <span class="line-2">running movie theater</span>
              </h2>
              <a href="/" class="home-link">The Grand Illusion: <?php bloginfo( 'description' ); ?></a>
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

            <div class="social-media-area">
              <nav class="social-navigation">
                <?php
                wp_nav_menu( array(
                  'menu'    => 'social-media-menu',
                  'menu_id' => 'social-media-menu',
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
    </header><!-- #masthead -->

    <section class="section-outer section-outer--site-content">
      <div class="section section--site-content">
        <div class="section-inner section-inner--site-content">
