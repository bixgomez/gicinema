<?php

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
    gicinema__sync_all_screenings([
      'two_way'            => !empty($_POST['two_way']),
      'dry_run'            => !empty($_POST['dry_run']),
      'require_clean_acf'  => !empty($_POST['require_clean_acf']),
      'deactivate_missing' => !empty($_POST['deactivate_missing']),
    ]);
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Sync all screenings finished.</strong></p><div class='gicinema-notice-content' style='max-height:420px; overflow:auto;'>{$html}</div></div>";
  } else {
    // Display warning and confirmation form
?>

    <div class="warning" style="border-left:4px solid #d63638; background:#fff; padding:8px 12px;">
      <p style="margin:0 0 6px;"><strong>Important:</strong> Run the two-way sync only after deleting all superfluous screenings. Otherwise you may re-introduce incorrect times into the custom table.</p>
      <p style="margin:0;">Defaults: Two-way table update ON; Require clean ACF ON; Dry-run OFF; Strict deactivate OFF. ACF-only sync runs regardless.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('sync_screenings_action', 'sync_nonce'); ?>
      <input type="hidden" name="confirm_import" value="yes">
      <p style="margin-top:10px;">
        <label style="display:block; margin:6px 0;">
          <input type="checkbox" name="two_way" value="1" checked>
          Also update the custom table with missing ACF screenings
        </label>
        <label style="display:block; margin:6px 0;">
          <input type="checkbox" name="deactivate_missing" value="1">
          Strict mode: deactivate table rows not in ACF
        </label>
        <label style="display:block; margin:6px 0;">
          <input type="checkbox" name="require_clean_acf" value="1" checked>
          Require clean ACF (abort two-way if any superfluous remain)
        </label>
        <label style="display:block; margin:6px 0;">
          <input type="checkbox" name="dry_run" value="1">
          Dry run (preview two-way actions without writing)
        </label>
      </p>
      <input id="gicinema-sync-all-submit" type="submit" class="button button-primary" value="Confirm sync all screenings">
    </form>
    <script>
      (function() {
        const form = document.currentScript.previousElementSibling;
        const submit = document.getElementById('gicinema-sync-all-submit');
        form && form.addEventListener('submit', function(ev) {
          const strict = form.querySelector('input[name="deactivate_missing"]')?.checked;
          if (strict) {
            const ok = window.confirm('Strict mode will deactivate table rows not present in ACF. Are you sure?');
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
