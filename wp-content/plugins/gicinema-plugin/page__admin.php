<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema_add_admin_page() {
  add_menu_page(
    'GI Cinema Plugin',            // title text
    'GI Cinema',                   // menu text
    'manage_options',              // capability for menu item to be displayed
    'gicinema--admin',             // slug name for menu
    'gicinema_admin_page_display', // function to output the content for this page
    'dashicons-admin-generic',     // menu icon
    6                              // position in the menu
  );
}

add_action('admin_menu', 'gicinema_add_admin_page');

function gicinema_admin_page_display() {
?>
  <div class="wrap wrap--gicinema">
    <p>
      This plugin integrates with Agile Ticketing to keep Film posts and their Screenings
      up to date. Imports normalize all dates/times to the site's WordPress timezone and
      write canonical screening times to a custom table, with an ACF “Screenings” field kept
      in sync for editor visibility.
    </p>
    <p>
      Use the tools below to run key tasks manually. Most are also scheduled via WP-Cron.
    </p>
    <ul>
      <?php
      $items = gicinema_get_admin_nav_items();
      $base  = admin_url('admin.php?page=');
      foreach ($items as $it) {
        if (empty($it['show'])) continue;
        if ($it['slug'] === 'gicinema--admin') continue; // skip Home in list
        echo '<li style="margin:0 0 12px 0;">';
        $label = esc_html($it['label']);
        $slug  = $it['slug'];
        $is_deprecated = !empty($it['deprecated']) && empty($it['enabled']);
        if ($is_deprecated) {
          echo '<h3>' . $label . ' <span style="color:#b32d2e; font-weight:600;">(Deprecated)</span></h3>';
        } else {
          echo '<h3><a href="' . esc_url($base . $slug) . '">' . $label . '</a></h3>';
        }
        gicinema_render_page_blurb($slug, false);
        echo '</li>';
      }
      ?>
    </ul>
    <!-- Add more HTML content here -->
  </div>
<?php
}
