<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__sync_all_screenings.php";

function gicinema_page_add__sync_all_screenings() {
  // Add sub-menu page
  add_submenu_page(
    'gicinema--admin', // The slug name for the parent menu
    'Sync All Screenings', // The text to be displayed in the title tags of the page when the menu is selected
    'Sync All Screenings', // The text to be used for the menu
    'manage_options', // The capability required for this menu to be displayed to the user
    'gicinema--sync-all-screenings', // The slug name to refer to this submenu by (should be unique for this submenu)
    'gicinema_page_display__sync_all_screenings' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__sync_all_screenings');

function gicinema_page_display__sync_all_screenings() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Sync All Screenings</h2>';

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    gicinema__sync_all_screenings();
  } else {
    // Display warning and confirmation form
?>
    <div class="info">
      <p>
        This is the second of our two main cron jobs, which you can run manually if needed.
      </p>
    </div>
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
