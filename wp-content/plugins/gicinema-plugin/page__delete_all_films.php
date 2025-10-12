<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_all_films.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
  // Submenu registration is centralized in inc/admin-nav.php
  function gicinema_page_display__delete_all_films() {
    echo '<div class="wrap wrap--gicinema">';
    echo '<h2>Delete All Films!</h2>';
    gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--delete-all-films' );

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
