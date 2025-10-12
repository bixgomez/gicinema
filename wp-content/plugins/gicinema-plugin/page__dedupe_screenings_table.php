<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__dedupe_screenings_table.php";

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__dedupe_screenings_table() {
  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Dedupe Screenings Table!</h2>';
  gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--dedupe-screenings-page' );

  // Check if the form was submitted
  if (isset($_POST['confirm_dedupe']) && $_POST['confirm_dedupe'] == 'yes') {
    gicinema__dedupe_screenings_table();
  } else {
    // Display warning and confirmation form
?>
    <div class="info">
      <p>
        Every so often (usually locally, during development and testing) we end up with
        duplicate records
        -- not in our WordPress film posts, but in the custom screenings table.
        This procedure finds and
        deletes dupes.
      </p>
    </div>
    <div class="warning">
      <p><strong>Warning:</strong> This action will dedupe the screenings table. This action is irreversible.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('dedupe_screenings_action', 'dedupe_nonce'); ?>
      <input type="hidden" name="confirm_dedupe" value="yes">
      <input type="submit" class="button button-primary" value="Confirm Deduping">
    </form>
<?php
  }

  echo '</div>';
}
