<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__sync_all_screenings.php";

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__sync_all_screenings() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Sync All Screenings</h2>';
  gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--sync-all-screenings' );
  gicinema_render_page_info('gicinema--sync-all-screenings');

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    ob_start();
    gicinema__sync_all_screenings();
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Sync all screenings finished.</strong></p><div class='gicinema-notice-content' style='max-height:420px; overflow:auto;'>{$html}</div></div>";
  } else {
    // Display warning and confirmation form
?>

    <div class="warning">
      <p><strong>Warning:</strong> This action will update the screenings table AND the screenings field. This action is irreversible.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('sync_screenings_action', 'sync_nonce'); ?>
      <input type="hidden" name="confirm_import" value="yes">
      <input type="submit" class="button button-primary" value="Confirm sync all screenings">
    </form>
<?php
  }

  echo '</div>';
}
