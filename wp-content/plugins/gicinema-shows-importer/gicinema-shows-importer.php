<?php
/* Plugin Name: GI Cinema Shows Importer
 * Plugin URI:  https://grandillusioncinema.org/
 * Description: Retrieves the most recently added shows..
 * Version:     1.0.0
 * Author:      Richard Gilbert
 * Author URI:  https://grandillusioncinema.org/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

require_once "shows-importer--function.php";

add_shortcode( 'import_shows', 'shows_importer' );
