<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema_page_add__import_films_from_agile() {
  // Main menu page is added here

  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Import Films from Agile', // The text to be displayed in the title tags of the page when the menu is selected
      'Import Films from Agile', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--import-films-from-agile', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__import_films_from_agile' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__import_films_from_agile');

function gicinema_page_display__import_films_from_agile() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Import from Agile</h2>';

  // Check if the form was submitted
  if (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
      require_once "function__import_films_from_agile.php";
      gicinema__import_films_from_agile();
  } else {
      // Display warning and confirmation form
      ?>
      <div class="info">
        <p>
          This is the first of our two main cron jobs, which you can run manually if needed.
        </p>
      </div>
      <div class="warning">
        <p><strong>Warning:</strong> This action will import all film posts from Agile. This action is irreversible.</p>
      </div>
      <form method="post">
      <input type="hidden" name="confirm_import" value="yes">
      <input type="submit" class="button button-primary" value="Confirm Import From Agile">
      </form>
      <?php
  }

  echo '</div>';
}
