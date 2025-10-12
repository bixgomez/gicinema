<?php

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
