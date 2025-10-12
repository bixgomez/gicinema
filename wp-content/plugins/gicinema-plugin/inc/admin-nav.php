<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

function gicinema_get_admin_nav_items() {
  $items = [];

  // Match the sidebar submenu order as registered (require order):
  // Home → All Film Posts → Update Agile Array → Import → Sync → Delete Overnight (Deprecated) → Dedupe → (local) Backup → (local) Delete All → (local) Truncate → Delete Superfluous (All)

  $items[] = [ 'slug' => 'gicinema--admin', 'label' => 'Home', 'deprecated' => false, 'show' => true ];
  $items[] = [ 'slug' => 'gicinema--all-film-posts', 'label' => 'All Film Posts', 'deprecated' => false, 'show' => true ];
  $items[] = [ 'slug' => 'gicinema--update-agile-array', 'label' => 'Update Agile Shows Array', 'deprecated' => false, 'show' => true ];
  $items[] = [ 'slug' => 'gicinema--import-films-from-agile', 'label' => 'Import from Agile', 'deprecated' => false, 'show' => true ];
  $items[] = [ 'slug' => 'gicinema--sync-all-screenings', 'label' => 'Sync All Screenings', 'deprecated' => false, 'show' => true ];

  // Local-only tools.
  $local = defined('WP_LOCAL_DEV') && WP_LOCAL_DEV;
  if ($local) {
    $items[] = [ 'slug' => 'gicinema--backup-database', 'label' => 'Backup DB', 'deprecated' => false, 'show' => true ];
    $items[] = [ 'slug' => 'gicinema--delete-all-films', 'label' => 'Delete All Films', 'deprecated' => false, 'show' => true ];
    $items[] = [ 'slug' => 'gicinema--truncate-screenings-table', 'label' => 'Truncate Screenings', 'deprecated' => false, 'show' => true ];
  }

  // Deprecated tool: keep visible but disabled with explanation.
  $overnight_enabled = function_exists('apply_filters') ? apply_filters('gicinema_enable_overnight_tool', false) : false;
  $items[] = [
    'slug' => 'gicinema--delete-overnight-screenings',
    'label' => 'Delete Overnight (Deprecated)',
    'deprecated' => true,
    'show' => true,
    'enabled' => $overnight_enabled,
    'deprecated_reason' => 'Replaced by timezone-normalized imports and the safer global cleanup tool.'
  ];

  // Global cleanup tool appears last (added later in sidebar as well).
  $items[] = [ 'slug' => 'gicinema--delete-all-superfluous-screenings', 'label' => 'Delete Superfluous (All)', 'deprecated' => false, 'show' => true ];

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
