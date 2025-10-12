<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_overnight_screenings.php";

function gicinema_page_add__delete_overnight_screenings() {
  // Deprecated tool: hidden from menu by default. Re-enable via filter if needed.
  $enabled = false;
  if (function_exists('apply_filters')) {
    $enabled = apply_filters('gicinema_enable_overnight_tool', false);
  }
  if (! $enabled) {
    return;
  }
  add_submenu_page(
    'gicinema--admin',
    'Delete Overnight Screenings',
    'Delete Overnight Screenings',
    'manage_options',
    'gicinema--delete-overnight-screenings',
    'gicinema_page_display__delete_overnight_screenings'
  );
}
add_action('admin_menu', 'gicinema_page_add__delete_overnight_screenings');

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
    <div class="info">
      <p>
        This tool identifies “overnight” screenings in the custom screenings table by
        naive time-of-day windows (22:00–23:59:59 and 00:00–10:00), plus a small set of
        legacy cleanup rules. Use the Dry run option below to preview exactly what would be
        deleted before proceeding.
      </p>
    </div>
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
