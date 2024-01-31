<?php 

function gicinema_add_admin_page() {
  add_menu_page(
      'GI Cinema Plugin', // The text to be displayed in the title tags of the page when the menu is selected
      'GI Cinema', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--admin', // The slug name to refer to this menu by (should be unique for this menu)
      'gicinema_admin_page_display', // The function to be called to output the content for this page
      'dashicons-admin-generic', // The URL to the icon to be used for this menu
      6 // The position in the menu order this one should appear
  );
}

add_action('admin_menu', 'gicinema_add_admin_page');

function gicinema_admin_page_display() {
  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>GI Cinema Plugin</h2>';
  
  echo '</div>';
}
