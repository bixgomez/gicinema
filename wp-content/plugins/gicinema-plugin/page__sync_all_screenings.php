<?php
/**
 * Admin page wrapper for the manual all-film screenings sync.
 *
 * Loaded by gicinema.php and added to the GI Cinema admin menu by
 * inc/admin-nav.php. This is the manual Sync All Screenings screen. It shows a
 * security-protected form with a dry-run/commit mode toggle and optional
 * repair choices, then calls gicinema__sync_all_screenings() after confirmation.
 * The sync output is shown back on the admin screen.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__sync_all_screenings.php";

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__sync_all_screenings() {

  echo '<div class="wrap wrap--gicinema">';
  gicinema_render_page_info('gicinema--sync-all-screenings');

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    ob_start();
    $sync_mode = isset($_POST['sync_mode']) ? sanitize_text_field(wp_unslash($_POST['sync_mode'])) : 'dry_run';
    gicinema__sync_all_screenings([
      'two_way'            => !empty($_POST['two_way']),
      'dry_run'            => ($sync_mode !== 'commit'),
      'deactivate_missing' => !empty($_POST['deactivate_missing']),
    ]);
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Sync all screenings finished.</strong></p></div>";
    echo $html;
  } else {
    // Display warning and confirmation form
?>

    <div class="notice notice-warning inline">
      <p><strong>Warning:</strong> Run Delete Superfluous before copying ACF-only screenings into the custom table. Otherwise old or incorrect ACF rows can be copied into <code>gi_screenings</code>.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('sync_screenings_action', 'sync_nonce'); ?>
      <input type="hidden" name="confirm_import" value="yes">
      <fieldset class="gicinema-sync-mode-toggle">
        <legend>Mode</legend>
        <label>
          <input type="radio" name="sync_mode" value="dry_run" checked>
          Dry run
        </label>
        <label>
          <input type="radio" name="sync_mode" value="commit">
          Commit changes
        </label>
      </fieldset>
      <p class="gicinema-option-list">
        <label class="gicinema-option">
          <input type="checkbox" name="two_way" value="1">
          Copy ACF-only screenings into the custom table
        </label>
        <label class="gicinema-option">
          <input type="checkbox" name="deactivate_missing" value="1">
          Mark active custom-table rows inactive when they are missing from ACF
        </label>
      </p>
      <input id="gicinema-sync-all-submit" type="submit" class="button button-primary" value="Run Sync All Screenings">
    </form>
    <script>
      (function() {
        const form = document.currentScript.previousElementSibling;
        const submit = document.getElementById('gicinema-sync-all-submit');
        form && form.addEventListener('submit', function(ev) {
          const strict = form.querySelector('input[name="deactivate_missing"]')?.checked;
          const commit = form.querySelector('input[name="sync_mode"][value="commit"]')?.checked;
          if (strict && commit) {
            const ok = window.confirm('This will mark active custom-table rows inactive when they are missing from ACF. It will not delete those rows. Are you sure?');
            if (!ok) {
              ev.preventDefault();
              return false;
            }
          }
        });
      })();
    </script>
<?php
  }

  echo '</div>';
}
