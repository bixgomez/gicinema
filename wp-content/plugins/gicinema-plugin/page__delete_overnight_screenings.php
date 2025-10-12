<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_overnight_screenings.php";
// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__delete_overnight_screenings() {
  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Delete Overnight Screenings</h2>';
  // If disabled via filter, show deprecation notice.
  $enabled = false;
  if (function_exists('apply_filters')) {
    $enabled = apply_filters('gicinema_enable_overnight_tool', false);
  }
  if (! $enabled) {
    echo "<div class='notice notice-warning'><p>This tool is deprecated and disabled. Use “Delete Superfluous (All Films)” instead.</p></div>";
    echo '</div>';
    return;
  }

  // Check if the form was submitted
  if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    $result = gicinema__delete_overnight_screenings();
    echo "<div class='notice notice-success'><p>{$result}</p></div>";
  } else {
    // Display warning and confirmation form
?>

    <div class="warning">
      <p><strong>Warning:</strong> This action permanently deletes matching screening rows from the custom table. It does not update the ACF field automatically.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('delete_overnight_action', 'delete_overnight_nonce'); ?>
      <label style="display:inline-block; margin:6px 0 10px 0;">
        <input type="checkbox" name="dry_run" value="1" checked>
        Dry run (list what would be deleted; no changes)
      </label>
      <input type="hidden" name="confirm_delete" value="yes">
      <input type="submit" class="button button-primary" value="Preview / Delete Overnight Screenings">
    </form>
<?php
  }

  echo '</div>';
}
