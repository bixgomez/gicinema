<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__update_agile_array() {

  echo '<div class="wrap wrap--gicinema">';
  gicinema_render_page_info('gicinema--update-agile-array');
  gicinema_render_cron_info('gicinema--update-agile-array');

  // Check if the form was submitted
  if (isset($_POST['confirm_update']) && $_POST['confirm_update'] == 'yes') {
    require_once "function__update_agile_shows_array.php";
    ob_start();
    gicinema__update_agile_shows_array();
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Agile feed update finished.</strong></p><div class='gicinema-notice-content'>{$html}</div></div>";
  } else {
    // Display warning and confirmation form
?>
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
