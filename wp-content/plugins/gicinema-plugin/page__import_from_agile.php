<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__import_films_from_agile() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Import from Agile</h2>';
  gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--import-films-from-agile' );

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    require_once "function__import_films_from_agile.php";
    gicinema__import_films_from_agile();
  } else {
    // Display warning and confirmation form
?>
    <div class="info">
      <p>
        This is the first of our two main cron jobs, which you can run manually if needed.
      </p>
    </div>
    <div class="warning">
      <p><strong>Warning:</strong> This action will import all film posts from Agile. This action is irreversible.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('import_films_action', 'import_nonce'); ?>
      <input type="hidden" name="confirm_import" value="yes">
      <input type="submit" class="button button-primary" value="Confirm Import From Agile">
    </form>
<?php
  }

  echo '</div>';
}
