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
    <div class="info">
      <p>
        This simply displays all the WordPress films posts in reverse order of date posted.
        It will check the custom table to see if we have an Agile ID associated with the film,
        and let you know if we find one... or not!
      </p>
      <p>
        <b>Please note:</b> For films posted prior to 10/20/2022, it is 
        unlikely that we have a matching record in the custom table.
      </p>
    </div>
    <?php gicinema__all_film_posts(); ?>
  </div>
  <?php
}
