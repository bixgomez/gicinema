<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__update_screenings_field.php";

function gicinema_page_add__update_screenings_field() {
  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Update Screenings Field', // The text to be displayed in the title tags of the page when the menu is selected
      'Update Screenings Field', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--import-from-screenings-field', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__update_screenings_field' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__update_screenings_field');

function gicinema_page_display__update_screenings_field() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Update Screenings Field</h2>';

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    function__update_screenings_field();
  } else {
      // Display warning and confirmation form
      echo '<p><strong>Warning:</strong> This action will update the screenings field. This action is irreversible.</p>';
      echo '<form method="post">';
      echo '<input type="hidden" name="confirm_import" value="yes">';
      echo '<input type="submit" class="button button-primary" value="Confirm Update Screenings Field">';
      echo '</form>';
  }
  
  echo '</div>';
}
