<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__all_film_posts.php";

function gicinema_page_display__all_film_posts() {
  ?>
  <div class="wrap wrap--gicinema">
    <h2>All Film Posts</h2>
    <?php gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--all-film-posts' ); ?>
    <?php gicinema_render_page_info('gicinema--all-film-posts'); ?>
    <?php gicinema__all_film_posts(); ?>
  </div>
  <?php
}
