<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__db_backup_and_cleanup.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {

  function gicinema_page_add__db_backup_and_cleanup() {
    // Add sub-menu page
    add_submenu_page(
        'gicinema--admin', // The slug name for the parent menu
        'Backup Database', // The text to be displayed in the title tags of the page when the menu is selected
        'Backup Database', // The text to be used for the menu
        'manage_options', // The capability required for this menu to be displayed to the user
        'gicinema--backup-database', // The slug name to refer to this submenu by (should be unique for this submenu)
        'gicinema_page_display__db_backup_and_cleanup' // The function to be called to output the content for this page
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
        echo '<p><strong>Warning:</strong> This action will back up the current database and delete all backups older than one week. This action is irreversible.</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="confirm_backup" value="yes">';
        echo '<input type="submit" class="button button-primary" value="Confirm Database Backup and Cleanup">';
        echo '</form>';
    }
    
    echo '</div>';
  }
}