<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__truncate_screenings_table.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
  // Submenu registration is centralized in inc/admin-nav.php
  function gicinema_page_display__truncate_screenings_table() {
    echo '<div class="wrap wrap--gicinema">';
    echo '<h2>Truncate Screenings Table!</h2>';
    gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--truncate-screenings-table' );

    // Check if the form was submitted
    if (isset($_POST['confirm_truncation']) && $_POST['confirm_truncation'] == 'yes') {
      $result = gicinema__truncate_screenings_table();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
    } else {
      // Display warning and confirmation form
?>
      <div class="info">
        <p>
          This one should also never be used in production. This will, as
          it implies, <i>permanently truncate the custom screenings table</i>.
          This should only be used locally. This too is not available on the live site anyway.
        </p>
      </div>
      <div class="warning">
        <p><strong>Warning:</strong> This action will permanently truncate the screenings table. This action is irreversible.</p>
      </div>
      <form method="post">
        <?php wp_nonce_field('truncate_table_action', 'truncate_nonce'); ?>
        <input type="hidden" name="confirm_truncation" value="yes">
        <input type="submit" class="button button-primary" value="Confirm Truncation">
      </form>
<?php
    }

    echo '</div>';
  }
}
