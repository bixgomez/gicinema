<?php
/**
 * Shared admin navigation metadata and rendering helpers.
 *
 * Loaded by gicinema.php after the page callback files are included. This file
 * owns the ordered list of plugin tools, submenu registration, the horizontal
 * admin navigation, page blurbs, and cron-info panels. It runs on admin_menu
 * and in_admin_header so the WordPress sidebar and the plugin page header stay
 * in sync from one metadata source.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema_get_admin_nav_items() {
  $items = [];

  // Match the sidebar submenu order as registered (require order):
  // Home -> All Film Posts -> Update Agile Array -> Import -> Sync -> Dedupe -> Backup -> (local) Delete All -> (local) Truncate -> Delete Superfluous (All)

  $items[] = [
    'slug' => 'gicinema--admin',
    'label' => 'Home',
    'deprecated' => false,
    'show' => true,
    'short' => 'This plugin integrates with Agile Ticketing to keep Film posts and their Screenings up to date. Imports normalize dates/times to the WordPress timezone, write canonical times to a custom table, and sync an ACF “Screenings” field for editor visibility.',
    'long'  => 'Most tools are also scheduled via WP-Cron. Use the top navigation to jump between tasks. All times reflect the site timezone; guards prevent ±7/±8 hour duplicates. The sidebar order and this header match and are generated from one source for consistency.'
  ];
  $items[] = [
    'slug' => 'gicinema--all-film-posts',
    'label' => 'All Film Posts',
    'deprecated' => false,
    'show' => true,
    'short' => 'Lists all Film posts in reverse chronological order and indicates whether each has an Agile ID recorded in the custom screenings table. Older films (prior to 2022-10-20) may not have corresponding Agile IDs.',
    'long'  => 'Each title links to its edit screen. The posted date is shown to help audit recent changes. This page is read-only: it does not modify data.'
  ];
  $items[] = [
    'slug' => 'gicinema--update-agile-array',
    'label' => 'Update Agile Shows Array',
    'deprecated' => false,
    'show' => true,
    'short' => 'Fetches the latest film/show data from Agile Ticketing and caches it for 12 hours. The importer consumes this cached feed and refreshes it automatically if needed.',
    'long'  => 'Stores JSON in the transient `agile_shows_array` with a 12-hour TTL. Uses WordPress HTTP API and respects site configuration. Safe to run repeatedly; no database changes beyond the transient. Note: This updates the cached API data that is used by the film import process.',
    'cron'  => [
      'hook'      => 'cron__update_agile_shows_array',
      'schedule'  => 'every_23_minutes',
      'frequency' => 'Every 23 minutes',
      'notes'     => [ 'Caches Agile feed as transient `agile_shows_array` for 12 hours.' ]
    ]
  ];
  $items[] = [
    'slug' => 'gicinema--import-films-from-agile',
    'label' => 'Import from Agile',
    'deprecated' => false,
    'show' => true,
    'short' => 'Runs the importer: reads the cached Agile JSON (refreshes the transient if missing/invalid), creates/updates Film posts, downloads poster images when URLs change, imports screenings into the custom table (normalized to the site timezone), and immediately syncs the ACF “Screenings” field. Scheduled every 30 minutes; safe to run manually.',
    'long'  => 'Source: cached feed stored as transient `agile_shows_array` (auto-refreshed if absent or undecodable; manual JSON paste available on the Import page). Repeatability: screenings are inserted or updated with a unique key on the normalized timestamp to prevent duplicates; poster and fields update only when values change. Times are normalized to the WordPress timezone before writing. Output includes per-film diagnostics and an edit-link for quick review.',
    'cron'  => [
      'hook'      => 'cron__import_films_from_agile',
      'schedule'  => 'every_30_minutes',
      'frequency' => 'Every 30 minutes',
      'notes'     => [ 'Refreshes Agile cache if missing before import.', 'Creates/updates Film posts and syncs ACF “Screenings”.' ]
    ]
  ];
  $items[] = [
    'slug' => 'gicinema--sync-all-screenings',
    'label' => 'Sync All Screenings',
    'deprecated' => false,
    'show' => true,
    'short' => 'Manual tool for rebuilding each Film&rsquo;s ACF “Screenings” field from the merged custom-table and ACF screening lists.',
    'long'  => '<h3>Default behavior</h3>'
      . '<p>With neither checkbox selected, the tool processes every Film, reads active custom-table screenings (<code>gi_screenings.status = 1</code>) and ACF screenings, normalizes and merges them, and rewrites the ACF <code>screenings</code> field from that merged list. It does not change the custom table.</p>'
      . '<h3>How the merge works</h3>'
      . '<ul class="ul-disc">
          <li>Active custom-table rows (<code>status = 1</code>) are always included. Inactive custom-table rows (<code>status = 0</code>) are ignored and are not copied into ACF.</li>
          <li>ACF-only rows are also included unless they look like timezone-shadow duplicates of table rows.</li>
          <li>Because ordinary ACF-only rows are preserved, this page is not the cleanup tool for bad ACF data. Use Delete Superfluous for that.</li>
        </ul>'
      . '<h3>Additional options</h3>'
      . '<ul class="ul-disc">
          <li><strong>Copy ACF-only screenings into the custom table:</strong> Adds or reactivates ACF-only screenings in <code>gi_screenings</code>. In Dry run mode, it only reports what would be added.</li>
          <li><strong>Mark active custom-table rows inactive when they are missing from ACF:</strong> Sets <code>gi_screenings.status = 0</code> for active table rows that are not present in ACF. It does not delete those rows. In Dry run mode, it only reports what would be marked inactive.</li>
        </ul>'
      . '<h3>Output</h3>'
      . '<p>Shows a results table for each Film with active custom-table rows, current ACF rows, resulting ACF rows, the ACF action, and any selected custom-table action. Screening lists are collapsed by default so the page stays readable.</p>'
  ];

  // Backup DB (admins only; available in all environments)
  $items[] = [
    'slug' => 'gicinema--backup-database',
    'label' => 'Backup DB',
    'deprecated' => false,
    'show' => true,
    'short' => 'Creates a compressed SQL backup to `../gicinema_dbs`. Scheduled daily at 21:00; safe to run on demand.',
    'long'  => 'Admins only. Filenames follow `gicinema-db--YYYY-MM-DD--HH-MM-SS.sql.gz`. Retention policy: keep all < 7 days, keep weekly for 30 days, keep monthly for 1 year, and keep the first backup of each year indefinitely. Old backups are pruned accordingly. Backups are stored outside the web root and downloadable via a nonce‑protected endpoint.',
    'cron'  => [
      'hook'      => 'cron__db_backup_and_cleanup',
      'schedule'  => 'daily',
      'frequency' => 'Daily at 21:00 (server time)',
      'notes'     => [ 'Prunes old backups per retention policy.', 'Writes to `../gicinema_dbs`.' ]
    ]
  ];

  // Local-only tools (hidden on production).
  $local = defined('WP_LOCAL_DEV') && WP_LOCAL_DEV;
  if ($local) {
    $items[] = [
      'slug' => 'gicinema--delete-all-films',
      'label' => 'Delete All Films',
      'deprecated' => false,
      'show' => true,
      'short' => 'Permanently deletes all Film posts. Local development only; not available on production.',
      'long'  => 'Irreversible action. Back up first. This affects WP `film` posts and related metadata; it does not truncate the custom screenings table.'
    ];
    $items[] = [
      'slug' => 'gicinema--truncate-screenings-table',
      'label' => 'Truncate Screenings',
      'deprecated' => false,
      'show' => true,
      'short' => 'Permanently truncates the custom `gi_screenings` table. Local development only.',
      'long'  => 'Deletes all rows from the canonical screenings table; Film posts remain unaffected. Back up first before running.'
    ];
  }

  // Global cleanup tool appears last (added later in sidebar as well).
  $items[] = [
    'slug' => 'gicinema--delete-all-superfluous-screenings',
    'label' => 'Delete Superfluous (All)',
    'deprecated' => false,
    'show' => true,
    'short' => 'Iterates every Film and removes any ACF “Screenings” entries that do not match the active screenings recorded in the custom table. Timezone-aware normalization prevents false mismatches. Includes a dry-run preview and a running log.',
    'long'  => 'Processes newest films first, one at a time, with Start/Stop controls. Skips films with zero screenings to reduce noise. Live runs update ACF only; the custom table remains unchanged.'
  ];

  return $items;
}

// Centralized submenu registration to keep sidebar and header nav in sync
function gicinema_register_admin_submenus() {
  $items = gicinema_get_admin_nav_items();

  // Map slugs to display callbacks.
  $callbacks = [
    'gicinema--all-film-posts'                 => 'gicinema_page_display__all_film_posts',
    'gicinema--update-agile-array'             => 'gicinema_page_display__update_agile_array',
    'gicinema--import-films-from-agile'        => 'gicinema_page_display__import_films_from_agile',
    'gicinema--sync-all-screenings'            => 'gicinema_page_display__sync_all_screenings',
    'gicinema--dedupe-screenings-page'         => 'gicinema_page_display__dedupe_screenings_table',
    'gicinema--backup-database'                => 'gicinema_page_display__db_backup_and_cleanup',
    'gicinema--delete-all-films'               => 'gicinema_page_display__delete_all_films',
    'gicinema--truncate-screenings-table'      => 'gicinema_page_display__truncate_screenings_table',
    'gicinema--delete-all-superfluous-screenings' => 'gicinema_page_display__delete_all_superfluous_screenings',
  ];

  foreach ($items as $it) {
    if (empty($it['show'])) continue;
    $slug = $it['slug'];
    if ($slug === 'gicinema--admin') continue; // top-level already registered

    $cb = isset($callbacks[$slug]) ? $callbacks[$slug] : '';
    if (!$cb || !function_exists($cb)) continue;

    $is_deprecated = !empty($it['deprecated']);
    $enabled = isset($it['enabled']) ? (bool)$it['enabled'] : true;
    if ($is_deprecated && !$enabled) {
      // Skip registering deprecated/disabled submenu
      continue;
    }

    add_submenu_page(
      'gicinema--admin',
      $it['label'],
      $it['label'],
      'manage_options',
      $slug,
      $cb
    );
  }
}
add_action('admin_menu', 'gicinema_register_admin_submenus', 20);

function gicinema_render_admin_nav($current_slug = '') {
  $items = gicinema_get_admin_nav_items();
  $base = admin_url('admin.php?page=');

  echo '<div class="gicinema-admin-nav">';
  echo '<ul class="gicinema-admin-nav__list">';
  foreach ($items as $it) {
    if (empty($it['show'])) { continue; }
    $slug = $it['slug'];
    $label = $it['label'];
    $is_current = ($current_slug === $slug);
    $is_deprecated = !empty($it['deprecated']);
    $enabled = isset($it['enabled']) ? (bool)$it['enabled'] : true;

    echo '<li class="gicinema-admin-nav__item">'; 
    if ($is_deprecated && !$enabled) {
      $title = isset($it['deprecated_reason']) ? $it['deprecated_reason'] : 'Deprecated tool';
      echo '<span title="' . esc_attr($title) . '" class="gicinema-admin-nav__link is-deprecated">' . esc_html($label) . '</span>';
    } else {
      $classes = 'gicinema-admin-nav__link';
      if ($is_current) {
        $classes .= ' is-current';
      }
      echo '<a href="' . esc_url($base . $slug) . '" class="' . esc_attr($classes) . '">' . esc_html($label) . '</a>';
    }
    echo '</li>';
  }
  echo '</ul>';
  echo '</div>';
}

// Render page title + nav in the admin header so it appears before notices.
function gicinema_render_page_header() {
  if (!is_admin()) return;
  $slug = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
  if (!$slug) return;
  $items = gicinema_get_admin_nav_items();
  $map = [];
  foreach ($items as $it) {
    $map[$it['slug']] = $it;
  }
  if (!isset($map[$slug])) return;
  $it = $map[$slug];
  $title = isset($it['title']) && $it['title'] ? $it['title'] : (isset($it['label']) ? $it['label'] : '');
  if ($title) {
    echo '<h2>' . esc_html($title) . '</h2>';
  }
  gicinema_render_admin_nav($slug);
}
add_action('in_admin_header', 'gicinema_render_page_header', 20);

// Renders a single info box with both short and long blurbs (when present).
function gicinema_render_page_info($slug) {
  $items = gicinema_get_admin_nav_items();
  $map = [];
  foreach ($items as $it) { $map[$it['slug']] = $it; }
  if (!isset($map[$slug])) return;
  $it = $map[$slug];
  $short = isset($it['short']) ? $it['short'] : '';
  $long  = isset($it['long']) ? $it['long'] : '';
  if (!$short && !$long) return;
  echo '<div class="notice notice-info inline">';
  if ($short) { echo '<p>' . esc_html($short) . '</p>'; }
  if ($long)  {
    // Allow limited markup in long descriptions so instructions can use headings and WordPress list classes.
    $allowed = [
      'ul' => [ 'class' => [], 'style' => [] ],
      'li' => [ 'style' => [] ],
      'strong' => [],
      'h3' => [],
      'i'  => [],
      'em' => [],
      'code' => [],
      'a' => [ 'href' => [], 'target' => [], 'rel' => [] ],
      'p' => [],
      'br' => []
    ];

    $has_markup = preg_match('/<\s*(p|ul|ol|li|h[1-6]|strong|em|i|code|a|br)\b/i', $long);
    if ($has_markup) {
      echo wp_kses($long, $allowed);
    } else {
      echo '<p>' . esc_html($long) . '</p>';
    }
  }
  echo '</div>';
}

// Renders a Cron section if cron metadata is present for the page.
function gicinema_render_cron_info($slug) {
  $items = gicinema_get_admin_nav_items();
  $map = [];
  foreach ($items as $it) { $map[$it['slug']] = $it; }
  if (!isset($map[$slug]) || empty($map[$slug]['cron'])) return;
  $c = $map[$slug]['cron'];
  echo '<div class="notice notice-info inline gicinema-cron-info">';
  echo '<h3>Cron</h3>';
  echo '<ul class="gicinema-cron-list">';
  if (!empty($c['hook']))      echo '<li>Hook: ' . esc_html($c['hook']) . '</li>';
  if (!empty($c['frequency'])) echo '<li>Frequency: ' . esc_html($c['frequency']) . '</li>';
  if (!empty($c['schedule']))  echo '<li>Schedule key: ' . esc_html($c['schedule']) . '</li>';
  if (!empty($c['notes']) && is_array($c['notes'])) {
    foreach ($c['notes'] as $note) {
      echo '<li>' . esc_html($note) . '</li>';
    }
  }
  echo '</ul>';
  echo '</div>';
}
