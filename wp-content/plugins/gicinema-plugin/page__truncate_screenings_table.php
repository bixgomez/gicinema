<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__truncate_screenings_table.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  function gicinema_page_add__truncate_screenings_table() {
    // Add sub-menu page
    add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Truncate Screenings Table', // The text to be displayed in the title tags of the page when the menu is selected
      'Truncate Screenings Table', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--truncate-screenings-table', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__truncate_screenings_table' // The function to be called to output the content for this page
    );
  }
  add_action('admin_menu', 'gicinema_page_add__truncate_screenings_table');

  function gicinema_page_display__truncate_screenings_table() {
    echo '<div class="wrap wrap--gicinema">';
    echo '<h2>Truncate Screenings Table!</h2>';

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
