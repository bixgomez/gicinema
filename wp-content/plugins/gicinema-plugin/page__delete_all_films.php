<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_all_films.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

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
?>
      <div class="info">
        <p>
          This one should almost never be used, especially in production. This will, as
          it implies, <i>permanently delete all film posts</i>. This should only be used locally.
          In fact, it's not even available on the live site! So there ya go.
        </p>
      </div>
      <div class="warning">
        <p><strong>Warning:</strong> This action will permanently delete all film posts. This action is irreversible.</p>
      </div>
      <form method="post">
        <?php wp_nonce_field('delete_all_films_action', 'delete_films_nonce'); ?>
        <input type="hidden" name="confirm_delete" value="yes">
        <input type="submit" class="button button-primary" value="Confirm Deletion">
      </form>
<?php
    }

    echo '</div>';
  }
}
