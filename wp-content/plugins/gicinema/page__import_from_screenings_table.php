<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__import_from_screenings_table.php";

function gicinema_page_add__import_from_screenings_table() {
  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Import From Screenings Table', // The text to be displayed in the title tags of the page when the menu is selected
      'Import From Screenings Table', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--import-from-screenings-table', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__import_from_screenings_table' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__import_from_screenings_table');

function gicinema_page_display__import_from_screenings_table() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Import from Screenings Table</h2>';

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    function__import_from_screenings_table();
  } else {
      // Display warning and confirmation form
      echo '<p><strong>Warning:</strong> This action will import all screenings from the custom screenings table. This action is irreversible.</p>';
      echo '<form method="post">';
      echo '<input type="hidden" name="confirm_import" value="yes">';
      echo '<input type="submit" class="button button-primary" value="Confirm Import From Screenings Table">';
      echo '</form>';
  }

  echo '</div>';
}
