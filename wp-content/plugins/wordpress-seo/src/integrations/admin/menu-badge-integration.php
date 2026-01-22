<?php

namespace Yoast\WP\SEO\Integrations\Admin;

use WPSEO_Admin_Asset_Manager;
use Yoast\WP\SEO\Conditionals\Admin_Conditional;
use Yoast\WP\SEO\Integrations\Integration_Interface;

/**
 * Menu_Badge_Integration class.
 */
class Menu_Badge_Integration implements Integration_Interface {

	/**
	 * {@inheritDoc}
	 */
	public static function get_conditionals() {
		return [ Admin_Conditional::class ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks() {
		\add_action( 'admin_enqueue_scripts', [ $this, 'add_inline_styles' ] );
	}

	/**
	 * Renders the migration error.
	 *
	 * @return void
	 */
	public function add_inline_styles() {
		$custom_css = 'ul.wp-submenu span.yoast-premium-badge::after, #wpadminbar span.yoast-premium-badge::after { content:"'
			. \__( 'Premium', 'wordpress-seo' ) . '"}' . \PHP_EOL;
<<<<<<< HEAD
<<<<<<< HEAD

		$custom_css .= 'ul.wp-submenu span.yoast-ai-plus-badge::after, #wpadminbar span.yoast-ai-plus-badge::after  { content:"AI+"; }' . \PHP_EOL;
=======
>>>>>>> 07955227f67d14ec4798c4b901c136b69715eefe
=======
>>>>>>> 42e963058bc112ffad35f07a6b79625948b27421
		\wp_add_inline_style( WPSEO_Admin_Asset_Manager::PREFIX . 'admin-global', $custom_css );
	}
}
