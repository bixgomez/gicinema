<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__db_backup_and_cleanup.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  function gicinema_page_add__db_backup_and_cleanup() {
    // Add sub-menu page
    add_submenu_page(
      'gicinema--admin',
      'Backup Database',
      'Backup Database',
      'manage_options',
      'gicinema--backup-database',
      'gicinema_page_display__db_backup_and_cleanup'
    );
  }
  add_action('admin_menu', 'gicinema_page_add__db_backup_and_cleanup');

  function gicinema_page_display__db_backup_and_cleanup() {
    echo '<div class="wrap wrap--gicinema">';
    echo '<h2>Backup The Database!</h2>';

    // Check if the form was submitted
    if (isset($_POST['confirm_backup']) && $_POST['confirm_backup'] == 'yes') {
      $result = gicinema__db_backup_and_cleanup();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
    } else {
      // Display warning and confirmation form
?>
      <div class="info">
        <p>
          This creates a backup of the database, and sticks it in a directory outside the
          web root (gicinema_dbs).
          It also backs up any database backup older than one week.
          This runs as a cron job once every 24 hours.
        </p>
      </div>
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
