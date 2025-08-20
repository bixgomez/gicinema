<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema_page_add__update_agile_array() {
  // Main menu page is added here

  // Add sub-menu page
  add_submenu_page(
    'gicinema--admin', // The slug name for the parent menu
    'Update Agile Shows Array', // The text to be displayed in the title tags of the page when the menu is selected
    'Update Agile Shows Array', // The text to be used for the menu
    'manage_options', // The capability required for this menu to be displayed to the user
    'gicinema--update-agile-array', // The slug name to refer to this submenu by (should be unique for this submenu)
    'gicinema_page_display__update_agile_array' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__update_agile_array');

function gicinema_page_display__update_agile_array() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Update Agile Shows Array</h2>';

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