<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__update_agile_array() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Update Agile Shows Array</h2>';
  gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--update-agile-array' );

  // Check if the form was submitted
  if (isset($_POST['confirm_update']) && $_POST['confirm_update'] == 'yes') {
    require_once "function__update_agile_shows_array.php";
    gicinema__update_agile_shows_array();
  } else {
    // Display warning and confirmation form
?>
    <div class="info">
      <p>
        This will fetch the latest film and show data from the Agile API and cache it for 12 hours.
      </p>
    </div>
    <div class="warning">
      <p><strong>Note:</strong> This updates the cached API data that is used by the film import process.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('update_agile_array_action', 'update_nonce'); ?>
      <input type="hidden" name="confirm_update" value="yes">
      <input type="submit" class="button button-primary" value="Update Agile Shows Array">
    </form>
<?php
  }

  echo '</div>';
}
