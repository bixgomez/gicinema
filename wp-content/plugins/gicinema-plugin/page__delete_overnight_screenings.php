<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__delete_overnight_screenings.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  function gicinema_page_add__delete_overnight_screenings() {
    // Add sub-menu page
    add_submenu_page(
        'gicinema--admin', // The slug name for the parent menu
        'Delete Overnight Screenings', // The text to be displayed in the title tags of the page when the menu is selected
        'Delete Overnight Screenings', // The text to be used for the menu
        'manage_options', // The capability required for this menu to be displayed to the user
        'gicinema--delete-overnight-screenings', // The slug name to refer to this submenu by (should be unique for this submenu)
        'gicinema_page_display__delete_overnight_screenings' // The function to be called to output the content for this page
    );
  }
  add_action('admin_menu', 'gicinema_page_add__delete_overnight_screenings');

  function gicinema_page_display__delete_overnight_screenings() {
    echo '<div class="wrap wrap--gicinema">';
    echo '<h2>Delete Overnight Screenings!</h2>';

    // Check if the form was submitted
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        $result = gicinema__delete_overnight_screenings();
        echo "<div class='notice notice-success'><p>{$result}</p></div>";
    } else {
        // Display warning and confirmation form
        echo '<p><strong>Warning:</strong> This action will permanently delete all overnight film posts. This action is irreversible.</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="confirm_delete" value="yes">';
        echo '<input type="submit" class="button button-primary" value="Confirm Deletion">';
        echo '</form>';
    }
    
    echo '</div>';
  }
}
