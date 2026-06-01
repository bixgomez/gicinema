<?php
/**
 * Top-level GI Cinema admin page.
 *
 * Loaded by gicinema.php during plugin bootstrap. It registers the main
 * "GI Cinema" WordPress admin menu item on admin_menu and renders the landing
 * page for the plugin tools. The page pulls navigation metadata from
 * inc/admin-nav.php so its tool list stays aligned with the submenu pages.
 */

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
    <table class="widefat striped">
      <thead>
        <tr>
          <th scope="col">Tool</th>
          <th scope="col">Description</th>
          <th scope="col">Runs</th>
          <th scope="col">Availability</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $items = gicinema_get_admin_nav_items();
        $base  = admin_url('admin.php?page=');
        $local_only_slugs = [
          'gicinema--delete-all-films' => true,
          'gicinema--truncate-screenings-table' => true,
        ];

        foreach ($items as $it) {
          if (empty($it['show'])) continue;
          if ($it['slug'] === 'gicinema--admin') continue; // skip Home in list

          $slug = $it['slug'];
          $label = isset($it['label']) ? $it['label'] : $slug;
          $description = isset($it['short']) ? $it['short'] : '';
          $is_deprecated = !empty($it['deprecated']);
          $enabled = isset($it['enabled']) ? (bool) $it['enabled'] : true;
          $runs = 'Manual';
          if (!empty($it['cron']['frequency'])) {
            $runs .= ' + WP-Cron: ' . $it['cron']['frequency'];
          }
          $availability = isset($local_only_slugs[$slug]) ? 'Local dev only' : 'All environments';
          if ($is_deprecated && !$enabled) {
            $availability = 'Deprecated';
          }

          echo '<tr>';
          echo '<th scope="row">';
          if ($is_deprecated && !$enabled) {
            echo esc_html($label) . ' <span class="description">(Deprecated)</span>';
          } else {
            echo '<a class="row-title" href="' . esc_url($base . $slug) . '">' . esc_html($label) . '</a>';
          }
          echo '</th>';
          echo '<td><span class="description">' . esc_html($description) . '</span></td>';
          echo '<td>' . esc_html($runs) . '</td>';
          echo '<td>' . esc_html($availability) . '</td>';
          echo '</tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
<?php
}
