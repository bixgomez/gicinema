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
if (!defined('ABSPATH')) {
  exit;
}

// Imports all necessary functions and pages.
require_once "function__create_custom_table.php";
require_once "function__update_film_on_save.php";
require_once "function__compare_screenings.php";
require_once "cron_jobs.php";
require_once "page__admin.php";
require_once "page__all_film_posts.php";
require_once "page__update_agile_array.php";
require_once "page__import_from_agile.php";
require_once "page__sync_all_screenings.php";
require_once "page__delete_overnight_screenings.php";
require_once "page__dedupe_screenings_table.php";
require_once "page__db_backup_and_cleanup.php";
require_once "page__delete_all_films.php";
require_once "page__truncate_screenings_table.php";

function gicinema_enqueue_styles() {
  wp_enqueue_style('gicinema-custom-styles', plugins_url('css/gicinema-plugin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'gicinema_enqueue_styles');

/**
 * Inject a simple info box at the very top of the Edit Film form
 * (after the title/permalink area). For now, it’s a placeholder.
 */
function gicinema_render_film_top_box() {
  // Ensure we are on a post edit screen and the post type is 'film'.
  global $post;
  if (!is_admin() || empty($post) || $post->post_type !== 'film') {
    return;
  }

  // Count screenings for this film from the custom table.
  $count_label = '0 screenings';
  if (function_exists('get_option')) {
    global $wpdb;
    $table_name = isset($wpdb) ? $wpdb->prefix . 'gi_screenings' : null;
    if ($table_name && $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name) {
      $screenings_count = (int) $wpdb->get_var(
        $wpdb->prepare(
          "SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d AND status = 1",
          $post->ID
        )
      );
      $count_label = sprintf(
        _n('%d screening', '%d screenings', $screenings_count, 'gicinema'),
        $screenings_count
      );
    }
  }

  // Count number of rows in the ACF "screenings" field (repeater) for this film.
  $acf_count = 0;
  if (function_exists('get_field')) {
    $acf_value = get_field('screenings', $post->ID);
    if (is_array($acf_value)) {
      $acf_count = count($acf_value);
    } elseif (!empty($acf_value) && is_numeric($acf_value)) {
      $acf_count = (int) $acf_value;
    }
  } else {
    // Fallback: ACF repeater stores a meta key equal to the field name with the row count.
    $acf_count = (int) get_post_meta($post->ID, 'screenings', true);
  }
  $acf_count_label = sprintf(_n('%d screening', '%d screenings', $acf_count, 'gicinema'), $acf_count);

  echo '<div id="gicinema-film-top-box" class="gicinema-admin-box" style="margin:12px 0 18px;">'
    . '<p style="margin:0 0 4px; color:#555;"><strong>' . esc_html($count_label) . '</strong> <span style="color:#888;">(from custom table)</span></p>'
    . gicinema__render_table_screenings($post->ID)
    . '<p style="margin:6px 6px 6px 0; color:#555;"><strong>' . esc_html($acf_count_label) . '</strong> <span style="color:#888;">(from Screenings field)</span></p>'
    . gicinema__render_matching_screenings($post->ID)
    . '</div>';
}
add_action('edit_form_after_title', 'gicinema_render_film_top_box');

// Creates the custom screenings table.
register_activation_hook(__FILE__, 'gicinema__create_custom_table');
