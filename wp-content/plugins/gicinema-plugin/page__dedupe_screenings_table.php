<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__dedupe_screenings_table.php";

function gicinema_page_add__dedupe_screenings_table() {
  // Add sub-menu page
  add_submenu_page(
    'gicinema--admin', // The slug name for the parent menu
    'Dedupe Screenings', // The text to be displayed in the title tags of the page when the menu is selected
    'Dedupe Screenings', // The text to be used for the menu
    'manage_options', // The capability required for this menu to be displayed to the user
    'gicinema--dedupe-screenings-page', // The slug name to refer to this submenu by (should be unique for this submenu)
    'gicinema_page_display__dedupe_screenings_table' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__dedupe_screenings_table');

function gicinema_page_display__dedupe_screenings_table() {
  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Dedupe Screenings Table!</h2>';

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
