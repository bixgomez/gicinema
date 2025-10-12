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
      <h2>GI Cinema Plugin</h2>
      <?php /* This file already outputs HTML within PHP; render nav via PHP call, not short-open tags. */ ?>
      <?php gicinema_render_admin_nav( isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'gicinema--admin' ); ?>
      <p>
        This plugin integrates with Agile Ticketing to keep Film posts and their Screenings
        up to date. Imports normalize all dates/times to the site’s WordPress timezone and
        write canonical screening times to a custom table, with an ACF “Screenings” field kept
        in sync for editor visibility.
      </p>
      <p>
        Use the tools below to run key tasks manually. Most are also scheduled via WP‑Cron.
      </p>
      <ul>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--all-film-posts') ); ?>">All Film Posts</a></h3>
          <p>
            Lists all Film posts (newest first). For each film it indicates whether a matching
            Agile ID is present in the custom screenings table. Older films (prior to 2022‑10‑20)
            may not have a corresponding Agile ID recorded.
          </p>
        </li>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--import-films-from-agile') ); ?>">Import from Agile</a></h3>
          <p>
            Runs the importer that reads the Agile Ticketing JSON feed (refreshes the cached feed
            if needed), then creates or updates Film posts. It updates metadata, downloads poster
            images, imports screenings into the custom table, and immediately syncs the ACF
            “Screenings” field. All screening times are normalized to the site timezone.
            Scheduled every 30 minutes; safe to run manually any time.
          </p>
        </li>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--sync-all-screenings') ); ?>">Sync All Screenings</a></h3>
          <p>
            Re-syncs screenings for every Film: reads canonical times from the custom table and
            merges with any ACF‑only entries, using a timezone‑aware guard to avoid ±7/±8 hour
            duplicates. Useful after manual edits. Newest films are processed first.
          </p>
        </li>

        <li>
          <h3>Delete Overnight Screenings <span style="color:#b32d2e; font-weight:600;">(Deprecated)</span></h3>
          <p>
            Deprecated. Timezone‑normalized imports resolved the former UTC shift issue; this tool
            used naive time windows (22:00–10:00) that could remove legitimate shows. It is hidden
            by default. Prefer the safer cleanup: “Delete Superfluous (All Films)” →
            <a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--delete-all-superfluous-screenings') ); ?>">open tool</a>.
          </p>
        </li>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--dedupe-screenings-page') ); ?>">Dedupe Screenings</a></h3>
          <p>
            Removes exact duplicate rows from the custom screenings table, keeping the earliest
            row per (screening, film_id, post_id). With the unique index on the normalized timestamp
            in place, duplicates should not recur; use this mainly for historical cleanup.
          </p>
        </li>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--backup-database') ); ?>">Backup Database</a></h3>
          <p>
            Creates a compressed SQL backup of the entire database to a directory outside the web
            root (`../gicinema_dbs`). Filenames include a timestamp (YYYY‑MM‑DD‑‑HH‑MM‑SS.sql.gz).
            Retention: keep all backups < 7 days; keep weekly for 30 days; keep monthly for 1 year;
            keep the first backup of each year indefinitely. Scheduled daily at 21:00 (server time).
            You can run it here on demand.
          </p>
        </li>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--delete-all-films') ); ?>">Delete All Films</a></h3>
          <p>
            Permanently deletes all Film posts. Intended for local development only; not available
            on production. Make a backup first.
          </p>
        </li>

        <li>
          <h3><a href="<?php echo esc_url( admin_url('admin.php?page=gicinema--truncate-screenings-table') ); ?>">Truncate Screenings Table</a></h3>
          <p>
            Permanently truncates the custom screenings table. Intended for local development only;
            not available on production. Make a backup first.
          </p>
        </li>

      </ul>
      <!-- Add more HTML content here -->
  </div>
  <?php
}
