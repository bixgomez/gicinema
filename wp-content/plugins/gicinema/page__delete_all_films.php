<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__delete_all_films.php";

function gicinema_page_add__delete_all_films() {
  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Delete All Films', // The text to be displayed in the title tags of the page when the menu is selected
      'Delete All Films', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--delete-all-films', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__delete_all_films' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__delete_all_films');

function gicinema_page_display__delete_all_films() {
  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Delete All Films!</h2>';

  // Check if the form was submitted
  if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
      $result = delete_all_film_posts();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
  } else {
      // Display warning and confirmation form
      echo '<p><strong>Warning:</strong> This action will permanently delete all film posts. This action is irreversible.</p>';
      echo '<form method="post">';
      echo '<input type="hidden" name="confirm_delete" value="yes">';
      echo '<input type="submit" class="button button-primary" value="Confirm Deletion">';
      echo '</form>';
  }
  
  echo '</div>';
}
