<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__db_backup_and_cleanup.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
  // Submenu registration is centralized in inc/admin-nav.php
  function gicinema_page_display__db_backup_and_cleanup() {
    echo '<div class="wrap wrap--gicinema">';
    echo '<h2>Backup The Database!</h2>';
    gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--backup-database' );

    // Check if the form was submitted (render notice immediately after nav)
    if (isset($_POST['confirm_backup']) && $_POST['confirm_backup'] == 'yes') {
      $result = gicinema__db_backup_and_cleanup();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
    }

    // Always render page info + cron after nav (and after any notice)
    gicinema_render_page_info('gicinema--backup-database');
    gicinema_render_cron_info('gicinema--backup-database');

    // If not submitted, render the confirmation form
    if (!isset($_POST['confirm_backup']) || $_POST['confirm_backup'] != 'yes') {
      // Display warning and confirmation form
?>
      
      <div class="warning">
        <p><strong>Warning:</strong> This action will back up the current database and delete all backups older than one week. This action is irreversible.</p>
      </div>
      <form method="post">
        <?php wp_nonce_field('backup_database_action', 'backup_nonce'); ?>
        <input type="hidden" name="confirm_backup" value="yes">
        <input type="submit" class="button button-primary" value="Confirm Database Backup and Cleanup">
      </form>
<?php
    }

    echo '</div>';
  }
}
