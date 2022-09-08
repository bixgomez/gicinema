<?php
/**
 * Cinema Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Cinema_Theme
 */

if ( ! function_exists( 'cinema_theme_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function cinema_theme_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Cinema Theme, use a find and replace
		 * to change 'cinema_theme' to the name of your theme in all the template files.
		 */
		// load_theme_textdomain( 'cinema_theme', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
    add_image_size( 'sidebar-thumb', 600, 400, true );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'cinema_theme' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'cinema_theme_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
	}
endif;
add_action( 'after_setup_theme', 'cinema_theme_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function cinema_theme_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'cinema_theme_content_width', 640 );
}
add_action( 'after_setup_theme', 'cinema_theme_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function cinema_theme_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'cinema_theme' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'cinema_theme' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'cinema_theme_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function cinema_theme_scripts() {
	wp_enqueue_style( 'cinema_theme-style', get_stylesheet_uri() );
	wp_enqueue_script( 'cinema_theme-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );
    wp_enqueue_script( 'hc-offcanvas-nav', get_template_directory_uri() .'/js/hc-offcanvas-nav.js', array('jquery'), null, true );
    wp_enqueue_script( 'hc-offcanvas-nav--config', get_template_directory_uri() .'/js/hc-offcanvas-nav--config.js', array('jquery'), null, true );
    wp_enqueue_script( 'cinema_theme-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cinema_theme_scripts' );

/**
 * Enqueue admin styles (under development).
 */
function cinema_theme_admin_style() {
  wp_enqueue_style( 'cinema_theme-admin-style', get_stylesheet_uri() );
}
add_action('admin_enqueue_scripts', 'cinema_theme_admin_style');
add_action('login_enqueue_scripts', 'cinema_theme_admin_style');


/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Cinema additions.
 */
require get_template_directory() . '/inc/cinema.php';

/**
 * Special alerts.
 */
function get_alerts() {
  include( locate_template( 'template-parts/alerts.php', false, false ) );
}

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Add REST API and GraphQL support to Book post type.
 */
add_filter( 'register_post_type_args', 'my_post_type_args', 10, 2 );

function my_post_type_args( $args, $post_type ) {

  if ( 'book' === $post_type ) {
    $args['show_in_graphql'] = true;
    $args['graphql_single_name'] = 'Book';
    $args['graphql_plural_name'] = 'Books';

    $args['show_in_rest'] = true;

    // Optionally customize the rest_base or rest_controller_class
    $args['rest_base']             = 'books';
    $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
  }

  return $args;
}


/**
 * OPTIONAL RESPONSIVE-RELATED STUFF:  Review, test, and use only if desired.
 */

/* Remove inline width style from <figure> tags (can break responsive) */
//  add_filter( 'img_caption_shortcode_width', '__return_false' );

/**
 * Disable WP's automatically-generated srcset-style responsive images
 * USE ONLY IF you have acceptable responsive image handling in your CSS and/or site code.
 */
//  function mysite_disable_srcset( $sources ) {
//     return false;
//  }
//  add_filter( 'wp_calculate_image_srcset', 'mysite_disable_srcset' );

/* END OPTIONAL RESPONSIVE-RELATED STUFF */


/**
 * Security: Disable ~ALL~ xml-rpc endpoints completely.
 * As FYI see https://kinsta.com/blog/xmlrpc-php/
 */
add_filter( 'xmlrpc_methods', function () {
	return [];
}, PHP_INT_MAX );


/** *****************************************
 * DE-CLUTTERING
 * Remove junk from the head
 */


/* Suppress GENERATOR meta tag (important for security, too) */
remove_action( 'wp_head', 'wp_generator' );

/* Suppress EditURI/RSD link */
remove_action( 'wp_head', 'rsd_link' );

/* Suppress wlwmanifest link (used only for Windows Live Writer, discontinuted) */
remove_action( 'wp_head', 'wlwmanifest_link' );

/* Remove comments feed from head  */
remove_action( 'wp_head', 'feed_links_extra', 3 );
//	The old school way: 	add_filter( 'feed_links_show_comments_feed', '__return_false' );

/* Remove main posts feed from head (DISABLE THIS IF THERE'S EVER A BLOG) */
remove_action( 'wp_head', 'feed_links', 2 );


/**
 * Remove this unnecesary style:
 * <style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
 */
function mysite_remove_recent_comments_style() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array(
		$wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
		'recent_comments_style'
	) );
}

add_action( 'widgets_init', 'mysite_remove_recent_comments_style' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param array $plugins
 *
 * @return array Difference betwen the two arrays
 */
function mysite_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/* Remove emoji cruft */
function mysite_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter( 'tiny_mce_plugins', 'mysite_disable_emojis_tinymce' );
}

add_action( 'init', 'mysite_disable_emojis' );


/**
 * Suppress shortlink HTML meta tag and HTTP header
 * Shortlink is a microformat intended for URLs that have a mini-fied version.
 *  Example: http://www.flickr.com/photos/tantek/3909804165/ --> http://flic.kr/p/6XuLyD
 * On WP, what gets rendered is the querystring reference to the page/post, vs. the permalink
 *  Example: https://msrstagingarr.azurewebsites.net/en-us/translator/?p=102
 * We don't want that.  This code disables that.
 */
function mysite_remove_shortlink() {
	// remove HTML meta tag
	// <link rel='shortlink' href='http://example.com/?p=25' />
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

	// remove HTTP header
	// Link: <https://example.com/?p=25>; rel=shortlink
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}

add_filter( 'after_setup_theme', 'mysite_remove_shortlink' );


/**
 * @function disable_oEmbeds_code_init()
 *
 * Comprehensively disable the oEmbed feature, and remove cruft from the head.
 * See https://kinsta.com/knowledgebase/disable-embeds-wordpress/
 * WP docs at https://codex.wordpress.org/Embeds
 */
function disable_oEmbeds_code_init() {

	// Remove the JS file
	wp_deregister_script( 'wp-embed' );

	// Remove the REST API endpoint.
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	// Turn off oEmbed auto discovery.
	add_filter( 'embed_oembed_discover', '__return_false' );

	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// Remove all embeds rewrite rules.
	if ( function_exists( 'disable_embeds_rewrites' ) ) {
		add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );
	}

	// Remove filter of the oEmbed result before any HTTP requests are made.
	if ( function_exists( 'wp_filter_pre_oembed_result' ) ) {
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}

}

add_action( 'init', 'disable_oEmbeds_code_init', 9999 );


/**
 * Clean up output of <script> tags: remove unnecessary TYPE declaration.
 */
function mysite_clean_script_tag( $input ) {
	$search = [
		"type='text/javascript' "
	];
	$replace = [
		''
	];

	return str_replace( $search, $replace, $input );
}

add_filter( 'script_loader_tag', 'mysite_clean_script_tag' );


/**
 * Remove Yoast's version comments and misc cruft.
 * See https://gist.github.com/paulcollett/4c81c4f6eb85334ba076
 */
if ( defined( 'WPSEO_VERSION' ) ) {

	add_filter( 'wpseo_debug_markers', '__return_false' );

	//  Suppress Yoast's SEO Schema JSON-LD.
	//  ONLY USE THIS ADVISEDLY. The JSON-LD stuff is used by Googlebot, and doesn't really hurt anything.
	/*
			function bybe_remove_yoast_json( $data ) {
					$data = array();
					return $data;
			}
			add_filter( 'wpseo_json_ld_output', 'bybe_remove_yoast_json', 10, 1 );
	*/
}


/* END De-Cluttering */


/* 404 fixes. Yes, we need both of these. */

/**
 * Treat 404s as 404s -- do not try to guess closest matching slug.
 * Disable WordPress' URL autocorrection guessing feature, which can wind up doing crazy things.
 * If you for example enter the URL http://www.myblog.com/proj you won't be redirected to http://www.myblog.com/project-2013 anymore.
 */
function remove_redirect_guess_404_permalink( $redirect_url ) {
	if ( is_404() && ! isset( $_GET['p'] ) ) {
		return false;
	}

	return $redirect_url;
}

add_filter( 'redirect_canonical', 'remove_redirect_guess_404_permalink' );

/**
 * Another 404 fix: Prevent WP from redirecting explicitly deleted stuff to nearest matching URL.
 */
remove_action( 'template_redirect', 'wp_old_slug_redirect' );
