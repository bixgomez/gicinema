<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__all_film_posts.php";

function gicinema_page_add__display_all_films() {
  // Main menu page is added here

  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'All Film Posts', // The text to be displayed in the title tags of the page when the menu is selected
      'All Film Posts', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--all-film-posts', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__all_film_posts' // The function to be called to output the content for this page
  );
}

add_action('admin_menu', 'gicinema_page_add__display_all_films');

function gicinema_page_display__all_film_posts() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>All Film Posts</h2>';

  gicinema__all_film_posts();

  echo '</div>';

}
