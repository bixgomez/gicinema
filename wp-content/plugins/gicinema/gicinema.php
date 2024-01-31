<?php
/* Plugin Name: Grand Illusion Cinema
 * Plugin URI:  https://grandillusioncinema.org/
 * Description: Retrieves the most recently added shows..
 * Version:     1.0.0
 * Author:      Richard Gilbert
 * Author URI:  https://grandillusioncinema.org/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__create-db-table.php";
require_once "function__create-shortcodes.php";
require_once "cron-jobs.php";
require_once "page__admin.php";
require_once "page__all-films.php";
require_once "page__screenings-table.php";

function gicinema_enqueue_styles() {
  wp_enqueue_style('gicinema-custom-styles', plugins_url('gicinema-styles.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'gicinema_enqueue_styles');
