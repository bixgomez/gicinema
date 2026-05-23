<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__dedupe_screenings_table.php";

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__dedupe_screenings_table() {
  echo '<div class="wrap wrap--gicinema">';
  gicinema_render_page_info('gicinema--dedupe-screenings-page');

  // Check if the form was submitted
  if (isset($_POST['confirm_dedupe']) && $_POST['confirm_dedupe'] == 'yes') {
    ob_start();
    gicinema__dedupe_screenings_table();
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Dedupe finished.</strong></p><div class='gicinema-notice-content'>{$html}</div></div>";
  } else {
    // Display warning and confirmation form
?>
    
    <div class="notice notice-error inline">
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
