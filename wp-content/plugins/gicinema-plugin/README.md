# Grand Illusion Cinema Plugin

A custom WordPress plugin for **Grand Illusion Cinema** that integrates with **Agile Ticketing** to automatically import, update, and manage film posts and screening schedules.  
It maintains a custom “screenings” database table, supports automated cron jobs, and provides an admin interface for manual data management, deduplication, and cleanup.

## Overview

This plugin is the operations layer that keeps the Grand Illusion Cinema WordPress site aligned with Agile Ticketing. It fetches the Agile JSON feed, stores the raw feed in a WordPress transient, and uses that cached data to create or update WordPress `film` posts. During import it updates film metadata, ticket links, descriptions, runtime, format, release year, director, production country, poster URL, trailer URL, and featured poster images.

Screening times are handled as their own data pipeline. The plugin normalizes Agile showtimes to the WordPress timezone, writes them to a custom database table named `gi_screenings`, and then syncs those canonical table values into the ACF `screenings` repeater field on each Film post. The ACF field gives editors and front-end templates an easy WordPress-native place to read showtimes, while the custom table remains the source used for deduplication, comparisons, cleanup, and repair tools.

The plugin also adds a `GI Cinema` admin area with tools for checking Film posts, refreshing the Agile feed, importing films and screenings, syncing all screenings, deduplicating the screenings table, deleting superfluous screenings, backing up the database, and managing development-only destructive actions. On individual Film edit screens it adds a top information box that compares custom-table screenings against ACF screenings and provides a shortcut to delete ACF screenings that do not match the active table records.

Automation is handled through WP-Cron. The plugin refreshes the Agile feed every 23 minutes, imports films and screenings every 30 minutes, and creates a compressed database backup once a day at 9 PM server time. It is built around repeatable imports: existing films are matched by Agile ID, screenings are inserted or updated using unique database keys, poster downloads are skipped when the poster URL has not changed, and timezone guards reduce recurring duplicate showtimes caused by UTC/Pacific offset problems.

## Features

- **Film Importing & Updating**
  - Imports films from Agile Ticketing as WordPress `film` posts.
  - Updates existing film posts when data changes in Agile.
  - Handles poster images, metadata, and custom fields.

- **Screenings Table Management**
  - Creates and maintains a custom `screenings` database table.
  - Imports screenings from Agile, linked to film posts.
  - Syncs screenings automatically via cron or on-demand.
  - Deduplicates screenings to prevent duplicate entries.

- **Admin Tools (under "GI Cinema" in WP Admin)**
  - **Update Agile Shows Array**: Manually refreshes cached API data from Agile.
  - **Import from Agile**: Manually trigger film and screening imports. Now includes immediate sync to ACF fields.
  - **Sync All Screenings**: Reconciles screenings between the custom table and the ACF field.
    - Always: reads from the custom table and ACF, merges with timezone-aware guards, and writes the merged set back to ACF so editors see the canonical times.
    - Optional two-way: can also insert missing ACF-only screenings into the custom table without creating duplicates. A strict mode can deactivate table rows not present in ACF. Includes dry-run and a “require clean ACF” preflight that aborts two-way if any film still has superfluous screenings.
  - **View All Film Posts**: Lists all film posts in the system.
  - **Deduplicate Screenings Table**: Removes duplicate screening rows.
  - **Delete Superfluous (All)**: Removes ACF screening rows that do not match active rows in the custom screenings table, with dry-run and stop/start controls.
  - **Delete All Films**: Bulk delete all film posts in local development only (use with caution).
  - **Delete Overnight Screenings**: Deprecated and disabled by default. It can be temporarily re-enabled with a filter, but the safer cleanup path is **Delete Superfluous (All)**.
  - **Truncate Screenings Table**: Completely empties the screenings table in local development only.
  - **Database Backup & Cleanup**: Backs up the full WordPress database and cleans up old backup files according to the retention policy.

- **Automation**
  - `cron_jobs.php` schedules regular API data updates and imports.
  - **Update Agile Shows Array** (every 23 minutes): Refreshes cached API data.
  - **Import Films from Agile** (every 30 minutes): Imports films and immediately syncs screenings to ACF fields.
  - **Database Backup & Cleanup** (daily at 9 PM): Backs up and maintains the database.
  - Ensures film and screening data stays current without manual intervention.

## Data Flow

1. **Agile Ticketing API** →  
   Raw film and screening data cached via `update_agile_shows_array`.
2. **Film Processing** →  
   `import_films_from_agile` creates or updates WP posts of type `film`.
3. **Screenings Import** →  
   Populates custom MySQL table with screening times.
4. **Immediate Sync** →  
   Screenings are immediately synced from database table to ACF repeater fields.
5. **Cleanup & Maintenance** →  
   Cron jobs and admin tools handle deduplication, pruning, and backup.

## Installation

1. Upload the `gicinema-plugin` folder to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins → Installed Plugins** in WordPress.
3. Confirm the hardcoded Agile feed URL in the update/import code is correct for the site.
4. Ensure the WordPress cron system is running for automated syncing.

## Requirements

- WordPress 6.0+
- PHP 7.4+ (tested with PHP 8.x)
- MySQL/MariaDB with permission to create custom tables
- Outbound HTTP access from WordPress to the Agile Ticketing JSON feed
- A `film` post type and the ACF fields used by the site, especially the `screenings` repeater

## Safety Notes

- **Destructive Actions**:  
  - `Delete All Films` permanently removes all film posts and is available only when `WP_LOCAL_DEV` is true.  
  - `Truncate Screenings Table` permanently deletes all screening records and is available only when `WP_LOCAL_DEV` is true.  
  - Use these only in development or with confirmed backups.
- The **Database Backup & Cleanup** tool should be run before performing destructive actions.

## Development Notes

- **File Structure**:
  - `function__*.php` — Logic and helpers for specific tasks.
  - `page__*.php` — Admin UI pages tied to menu items.
  - `cron_jobs.php` — Scheduled automation tasks.
  - `gicinema.php` — Plugin bootstrap and menu registration.
  - `css/gicinema-plugin.css` — Styling for admin pages.
- Keep logic in `function__*.php` files and only minimal display code in `page__*.php` files.
- Designed for maintainability: each admin action is isolated in its own page and function file.

## Cron Jobs

The custom plugin `gicinema-plugin` schedules several automated tasks. These run via WP-Cron and can also be triggered with WP-CLI.

- Agile shows feed refresh
  - Hook: `cron__update_agile_shows_array`
  - Schedule: every 23 minutes
  - Function: `gicinema__update_agile_shows_array`
  - Purpose: Fetches the Agile Ticketing JSON feed and stores it as the transient `agile_shows_array` (12h TTL).

- Import films from Agile
  - Hook: `cron__import_films_from_agile`
  - Schedule: every 30 minutes
  - Function: `gicinema__import_films_from_agile`
  - Purpose: Creates/updates Film posts and screenings from the transient; downloads poster images when URLs change; syncs screenings to ACF. If the transient is missing or invalid, it refreshes it first. The standalone dedupe tool is separate; importer idempotency comes from the screenings table unique keys and schema guard.

- Database backup and cleanup
  - Hook: `cron__db_backup_and_cleanup`
  - Schedule: daily at 21:00 (server time)
  - Function: `gicinema__db_backup_and_cleanup`
  - Output: backups written outside the web root to `../gicinema_dbs`
  - Filename: `gicinema-db--YYYY-MM-DD--HH-MM-SS.sql.gz`
  - Retention policy:
    - Keep all backups newer than 7 days
    - Keep first backup of each week for 30 days
    - Keep first backup of each month for 1 year
    - Keep first backup of each year indefinitely

### WP-CLI (DDEV) examples

- List scheduled events: `ddev wp cron event list`
- Run a specific job now: `ddev wp cron event run cron__update_agile_shows_array --due-now`
- Run all due events: `ddev wp cron event run --due-now --all`
- Check next runs for plugin jobs: `ddev wp cron event list | rg cron__`

### Local development: forcing runs

If an event exists but isn't yet due, make it due or run the callback directly.

- Ensure environment and plugin
  - Start DDEV: `ddev start`
  - Activate plugin: `ddev wp plugin activate gicinema-plugin`
  - Load schedules (first page hit): open `https://gicinema.ddev.site/`

- Make an event due, then run
  - Import: `ddev wp cron event schedule cron__import_films_from_agile 'now -1 minute' --schedule=every_30_minutes`
  - Feed: `ddev wp cron event schedule cron__update_agile_shows_array 'now -1 minute' --schedule=every_23_minutes`
  - Backup: `ddev wp cron event schedule cron__db_backup_and_cleanup 'now -1 minute' --schedule=daily`
  - Execute: `ddev wp cron event run <hook> --due-now`

- Run callbacks directly (bypass scheduling)
  - Feed refresh: `ddev wp eval 'gicinema__update_agile_shows_array();'`
  - Import films: `ddev wp eval 'gicinema__import_films_from_agile();'`
  - Via hook: `ddev wp eval 'do_action("cron__import_films_from_agile");'`

- Troubleshooting tips
  - Re-list events: `ddev wp cron event list --fields=hook,next_run,recurrence | grep cron__`
  - Ensure `DISABLE_WP_CRON` is not true in `wp-config.php` if expecting automatic WP-Cron on requests. For dev, using WP-CLI as above is sufficient.

## Agile Feed Update and Import

This section documents, in detail, the two core actions that power the data flow: updating the cached Agile feed and importing films/screenings.

### Update Agile Shows Array

- Function: `gicinema__update_agile_shows_array()`  
  File: `wp-content/plugins/gicinema-plugin/function__update_agile_shows_array.php`
- What it does
  - Fetches the Agile Ticketing JSON feed and caches the raw JSON string in the transient `agile_shows_array` for 12 hours.
  - Appends a cache-buster (`_ts`) to the URL; sets headers (`Accept`, `User-Agent`, `Referer`) to avoid WAF/proxy oddities.
  - Uses WordPress HTTP API (`wp_remote_get`) with timeout and redirection handling.
  - Strips UTF-8 BOM if present and validates JSON. If initial attempt fails, retries once with stricter headers and a different cache-buster.
  - On success: stores the response body (string) in the transient; on failure: deletes transient and logs HTTP code/content-type/length and body snippet(s).
- Admin UI: `Grand Illusion Cinema → Update Agile Shows Array`  
  File: `page__update_agile_array.php` — posts to the function above and shows a detailed log.
- Cron: scheduled every 23 minutes  
  Hook: `cron__update_agile_shows_array` (configured in `cron_jobs.php`).

### Import Films From Agile

- Function: `gicinema__import_films_from_agile()`  
  File: `wp-content/plugins/gicinema-plugin/function__import_films_from_agile.php`
- What it does
  - Ensures screenings table schema/index exist (`gicinema__ensure_screenings_unique_index()`), then reads the cached `agile_shows_array` transient; if missing or undecodable, it calls the update function to refresh and retries.
  - Robust JSON handling: strips BOM and decodes either array or object structures; supports the feed being nested under `ArrayOfShows` or `Shows`.
  - Logs “Found X films…” to confirm parse success before looping.
  - For each show:
    - Normalizes to object; pulls core fields (ID/Name/Duration/ShortDescription/InfoLink).
    - Parses `AdditionalMedia` for a poster URL (Type=Image) and trailer URL (Type=YouTube).
    - Parses `CustomProperties` for Release Year, Format, Director(s), Production Country (handles arrays/objects; concatenates multiple directors and countries).
    - Finds existing Film by meta `agile_film_id`; if none, creates a new `film` post (status `publish`).
    - Poster handling: if the poster URL changed, downloads via `wp_remote_get` and inserts an attachment, sets as featured image (no `file_get_contents`), updates ACF `film_poster`.
    - Updates ACF/meta fields: `agile_film_id`, `description`, `film_length`, `ticket_purchase_link`, `film_year`, `format`, `film_director`, `country`, `poster_url`, `trailer_url`.
    - Imports screenings by calling `gicinema__import_screenings_from_agile()` with `$show->CurrentShowings` (array/object tolerant).
      - Screenings normalization: converts each `StartDate` into WP timezone (`wp_timezone()` fallback to `timezone_string`) and formats `Y-m-d H:i:s`.
      - Database write: `INSERT ... ON DUPLICATE KEY UPDATE status = 1` into the custom table.
    - Immediately calls `gicinema__sync_screenings($post_ID)` so the ACF repeater shows the canonical, normalized times.
  - Dedupe is available as a separate admin tool, and uniqueness is enforced at schema level (see below).
- Admin UI: `Grand Illusion Cinema → Import from Agile`  
  File: `page__import_from_agile.php` — standard confirm form plus a manual fallback to paste full JSON (validated, cached 1h, then import).
- Cron: scheduled every 30 minutes  
  Hook: `cron__import_films_from_agile` (configured in `cron_jobs.php`).

### Relationship and Data Flow

- Update action writes the raw feed JSON into the `agile_shows_array` transient.
- Import action reads and decodes that transient, then:
  - Creates/updates Film posts; downloads posters if changed.
  - Normalizes screenings and inserts or updates them in the custom table without creating duplicates.
  - Syncs screenings into the ACF repeater so front-end templates use the same canonical timestamps.
- If the transient is missing/invalid, the importer automatically calls the updater first.

### Timezones and Uniqueness

- Timezone normalization: Importer and sync routines standardize times to the WordPress timezone and format `Y-m-d H:i:s` to eliminate recurring ±7/8h duplicates.
- Database uniqueness: The screenings table includes `UNIQUE KEY unique_screening_str (film_id, post_id, screening(19))` so inserts are idempotent via `ON DUPLICATE KEY` even if legacy `screening_date/time` uniqueness isn't present.  
  File: `function__create_custom_table.php`.

### Manual Triggers and Troubleshooting

- Admin pages render detailed logs for both actions and provide a JSON paste fallback on the import page for environments blocked from reaching the feed.
- WP-CLI (DDEV examples):
  - Update feed now: `ddev wp eval 'gicinema__update_agile_shows_array();'`
  - Import now: `ddev wp eval 'gicinema__import_films_from_agile();'`
  - Run scheduled: `ddev wp cron event run cron__update_agile_shows_array --due-now` and `cron__import_films_from_agile`.
- If “Found X films…” doesn't appear or feed logs show HTML instead of JSON:
  - Re-run Update; verify headers/retries in the log.
  - Use the paste-JSON fallback on the Import page as a temporary workaround.
  - Consider shorter HTTP timeouts and additional diagnostics if production networking is flaky.
