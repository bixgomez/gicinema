<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__delete_overnight_screenings.php";

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
?>
    <div class="info">
      <p>
        Occasionally, due to some weirdness regarding time zones, we end up with
        screenings being imported in their UTC time equivalents rather than local time.
        So, we end up with duplicate screenings that appear to occur 7-8 hours later
        than they actually do. This function seeks to take care of most of these
        occurrences, by deleting any screenings that appear to start between 10pm and 10am.
      </p>
      <p>
        <i>Yes, it's kludgy, and yes, it must be a bug in the system.</i> But, for now,
        it works... For the most part.
      </p>
    </div>
    <div class="warning">
      <p><strong>Warning:</strong> This action will permanently delete all overnight film posts. This action is irreversible.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('delete_overnight_action', 'delete_overnight_nonce'); ?>
      <input type="hidden" name="confirm_delete" value="yes">
      <input type="submit" class="button button-primary" value="Confirm Deletion">
    </form>
<?php
  }

  echo '</div>';
}
