<?php
/**
 * Admin page wrapper for the Film post audit tool.
 *
 * Loaded by gicinema.php and added to the GI Cinema admin menu by
 * inc/admin-nav.php. This is the "All Film Posts" screen. It runs when an
 * administrator opens that page, shows the shared page info, then
 * calls gicinema__all_film_posts() to list Film posts and repair missing table
 * links where possible.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__all_film_posts.php";

function gicinema_page_display__all_film_posts() {
  ?>
  <div class="wrap wrap--gicinema">
    <?php gicinema_render_page_info('gicinema--all-film-posts'); ?>
    <?php gicinema__all_film_posts(); ?>
  </div>
  <?php
}
