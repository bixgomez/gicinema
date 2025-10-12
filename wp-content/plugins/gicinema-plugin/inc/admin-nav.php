<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema_get_admin_nav_items() {
  $items = [];

  // Match the sidebar submenu order as registered (require order):
  // Home → All Film Posts → Update Agile Array → Import → Sync → Delete Overnight (Deprecated) → Dedupe → (local) Backup → (local) Delete All → (local) Truncate → Delete Superfluous (All)

  $items[] = [
    'slug' => 'gicinema--admin',
    'label' => 'Home',
    'deprecated' => false,
    'show' => true,
    'short' => 'This plugin integrates with Agile Ticketing to keep Film posts and their Screenings up to date. Imports normalize dates/times to the WordPress timezone, write canonical times to a custom table, and sync an ACF “Screenings” field for editor visibility.',
    'long'  => 'Most tools are also scheduled via WP‑Cron. Use the top navigation to jump between tasks. All times reflect the site timezone; guards prevent ±7/±8 hour duplicates. The sidebar order and this header match and are generated from one source for consistency.'
  ];
  $items[] = [
    'slug' => 'gicinema--all-film-posts',
    'label' => 'All Film Posts',
    'deprecated' => false,
    'show' => true,
    'short' => 'Lists all Film posts in reverse chronological order and indicates whether each has an Agile ID recorded in the custom screenings table. Older films (prior to 2022‑10‑20) may not have corresponding Agile IDs.',
    'long'  => 'Each title links to its edit screen. The posted date is shown to help audit recent changes. This page is read‑only: it does not modify data.'
  ];
  $items[] = [
    'slug' => 'gicinema--update-agile-array',
    'label' => 'Update Agile Shows Array',
    'deprecated' => false,
    'show' => true,
    'short' => 'Fetches the latest film/show data from Agile Ticketing and caches it for 12 hours. The importer consumes this cached feed and refreshes it automatically if needed.',
    'long'  => 'Stores JSON in the transient `agile_shows_array` with a 12‑hour TTL. Uses WordPress HTTP API and respects site configuration. Safe to run repeatedly; no database changes beyond the transient.',
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
    'short' => 'Runs the importer: reads the Agile JSON feed (refreshes cache if needed), creates/updates Film posts, updates metadata, downloads poster images, imports screenings to the custom table, and immediately syncs the ACF “Screenings” field. All times are normalized to the site timezone. Scheduled every 30 minutes; safe to run manually.',
    'long'  => 'Idempotent where possible: screenings use a unique key to prevent duplicates; featured image and fields are updated only when they change. Output includes per‑film diagnostics to aid troubleshooting.',
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
    'short' => 'Re‑syncs screenings for every Film by reading canonical times from the custom table and merging with any ACF‑only entries. Uses a timezone‑aware guard to avoid ±7/±8 hour duplicates. Useful after manual edits. Processes newest films first.',
    'long'  => 'Writes the merged set back to the ACF repeater via `update_field`. Safe to run anytime; no changes to the canonical table occur here.'
  ];

  // Local-only tools.
  $local = defined('WP_LOCAL_DEV') && WP_LOCAL_DEV;
  if ($local) {
    $items[] = [
      'slug' => 'gicinema--backup-database',
      'label' => 'Backup DB',
      'deprecated' => false,
      'show' => true,
      'short' => 'Creates a compressed SQL backup to `../gicinema_dbs`. Scheduled daily at 21:00; safe to run on demand.',
      'long'  => 'Filenames follow `gicinema-db--YYYY-MM-DD--HH-MM-SS.sql.gz`. Retention policy: keep all < 7 days, keep weekly for 30 days, keep monthly for 1 year, and keep the first backup of each year indefinitely. Old backups are pruned accordingly.',
      'cron'  => [
        'hook'      => 'cron__db_backup_and_cleanup',
        'schedule'  => 'daily',
        'frequency' => 'Daily at 21:00 (server time)',
        'notes'     => [ 'Prunes old backups per retention policy.', 'Writes to `../gicinema_dbs`.' ]
      ]
    ];
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

  // Deprecated tool: keep visible but disabled with explanation.
  $overnight_enabled = function_exists('apply_filters') ? apply_filters('gicinema_enable_overnight_tool', false) : false;
  $items[] = [
    'slug' => 'gicinema--delete-overnight-screenings',
    'label' => 'Delete Overnight (Deprecated)',
    'deprecated' => true,
    'show' => true,
    'enabled' => $overnight_enabled,
    'deprecated_reason' => 'Replaced by timezone-normalized imports and the safer global cleanup tool.',
    'short' => 'Deprecated. The former UTC shift issue is resolved by timezone‑normalized imports. This tool used naive 22:00–10:00 windows and could remove legitimate shows. Prefer the safer “Delete Superfluous (All Films)” cleanup.',
    'long'  => 'Hidden by default; can be temporarily re‑enabled via the `gicinema_enable_overnight_tool` filter. Includes a dry‑run preview but should generally be avoided.'
  ];

  // Global cleanup tool appears last (added later in sidebar as well).
  $items[] = [
    'slug' => 'gicinema--delete-all-superfluous-screenings',
    'label' => 'Delete Superfluous (All)',
    'deprecated' => false,
    'show' => true,
    'short' => 'Iterates every Film and removes any ACF “Screenings” entries that do not match the active screenings recorded in the custom table. Timezone‑aware normalization prevents false mismatches. Includes a dry‑run preview and a running log.',
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
    'gicinema--delete-overnight-screenings'    => 'gicinema_page_display__delete_overnight_screenings',
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

  echo '<div class="gicinema-admin-nav" style="margin:10px 0 16px; padding:6px;">';
  echo '<ul style="display:flex; flex-wrap:wrap; gap:4px; margin:0; padding:0; list-style:none;">';
  foreach ($items as $it) {
    if (empty($it['show'])) { continue; }
    $slug = $it['slug'];
    $label = $it['label'];
    $is_current = ($current_slug === $slug);
    $is_deprecated = !empty($it['deprecated']);
    $enabled = isset($it['enabled']) ? (bool)$it['enabled'] : true;

    $base_style = 'display:inline-block; padding:6px 10px; border:1px solid #ccd0d4; border-radius:4px; background:#fff; color:#1d2327; text-decoration:none;';
    $active_style = 'background:#2271b1; color:#fff; border-color:#2271b1;';
    $depr_style = 'background:#f6f7f7; color:#646970; border-style:dashed;';

    echo '<li style="margin:0;">'; 
    if ($is_deprecated && !$enabled) {
      $title = isset($it['deprecated_reason']) ? $it['deprecated_reason'] : 'Deprecated tool';
      echo '<span title="' . esc_attr($title) . '" style="' . $base_style . $depr_style . '">' . esc_html($label) . '</span>';
    } else {
      $style = $base_style . ($is_current ? $active_style : '');
      echo '<a href="' . esc_url($base . $slug) . '" style="' . $style . '">' . esc_html($label) . '</a>';
    }
    echo '</li>';
  }
  echo '</ul>';
  echo '</div>';
}

// Ensure the nav appears immediately after the page title and before notices.
// Note: We render the nav within each page immediately after the <h2> title
// to ensure it appears below the title and above any page notices.

function gicinema_render_page_blurb($slug, $full = false) {
  $items = gicinema_get_admin_nav_items();
  $map = [];
  foreach ($items as $it) { $map[$it['slug']] = $it; }
  if (!isset($map[$slug])) return;
  $it = $map[$slug];
  $text = $full ? (isset($it['long']) ? $it['long'] : '') : (isset($it['short']) ? $it['short'] : '');
  if (!$text) return;
  echo '<div class="info"><p>' . esc_html($text) . '</p></div>';
}

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
  echo '<div class="info">';
  if ($short) { echo '<p>' . esc_html($short) . '</p>'; }
  if ($long)  { echo '<p>' . esc_html($long) . '</p>'; }
  echo '</div>';
}

// Renders a Cron section if cron metadata is present for the page.
function gicinema_render_cron_info($slug) {
  $items = gicinema_get_admin_nav_items();
  $map = [];
  foreach ($items as $it) { $map[$it['slug']] = $it; }
  if (!isset($map[$slug]) || empty($map[$slug]['cron'])) return;
  $c = $map[$slug]['cron'];
  echo '<div class="info" style="margin-top:8px;">';
  echo '<p><b>Cron</b></p>';
  echo '<ul style="margin:0 0 0 18px;">';
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
