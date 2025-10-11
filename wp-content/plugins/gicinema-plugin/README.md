# Grand Illusion Cinema Plugin

A custom WordPress plugin for **Grand Illusion Cinema** that integrates with **Agile Ticketing** to automatically import, update, and manage film posts and screening schedules.  
It maintains a custom “screenings” database table, supports automated cron jobs, and provides an admin interface for manual data management, deduplication, and cleanup.

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

- **Admin Tools (under "Grand Illusion Cinema" in WP Admin)**
  - **Update Agile Shows Array**: Manually refreshes cached API data from Agile.
  - **Import from Agile**: Manually trigger film and screening imports. Now includes immediate sync to ACF fields.
  - **Sync All Screenings**: Re-syncs all screenings from Agile.
  - **View All Film Posts**: Lists all film posts in the system.
  - **Deduplicate Screenings Table**: Removes duplicate screening rows.
  - **Delete All Films**: Bulk delete all film posts (use with caution).
  - **Delete Overnight Screenings**: Removes screenings with start times between 12:00 AM and 6:00 AM. This helps clean up unexpected duplicate screenings caused by time zone offsets (likely GMT vs Pacific Time). The exact cause is unclear — could stem from Agile, WordPress, server config, or plugin logic.
  - **Truncate Screenings Table**: Completely empties the screenings table.
  - **Database Backup & Cleanup**: Backs up the screenings table and performs cleanup tasks.

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
3. Configure any required constants (API keys, Agile endpoint URLs) in `gicinema.php` or your environment.
4. Ensure the WordPress cron system is running for automated syncing.

## Requirements

- WordPress 6.0+
- PHP 7.4+ (tested with PHP 8.x)
- MySQL/MariaDB with permission to create custom tables
- Agile Ticketing API credentials
- cURL enabled in PHP

## Safety Notes

- **Destructive Actions**:  
  - `Delete All Films` permanently removes all film posts.  
  - `Truncate Screenings Table` permanently deletes all screening records.  
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

The custom plugin `gicinema-plugin` schedules several automated tasks. These run via WP‑Cron and can also be triggered with WP‑CLI.

- Agile shows feed refresh
  - Hook: `cron__update_agile_shows_array`
  - Schedule: every 23 minutes
  - Function: `gicinema__update_agile_shows_array`
  - Purpose: Fetches the Agile Ticketing JSON feed and stores it as the transient `agile_shows_array` (12h TTL).

- Import films from Agile
  - Hook: `cron__import_films_from_agile`
  - Schedule: every 30 minutes
  - Function: `gicinema__import_films_from_agile`
  - Purpose: Creates/updates Film posts and screenings from the transient; downloads poster images; syncs screenings and runs dedupe. If the transient is missing, it refreshes it first.

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

### WP‑CLI (DDEV) examples

- List scheduled events: `ddev wp cron event list`
- Run a specific job now: `ddev wp cron event run cron__update_agile_shows_array --due-now`
- Run all due events: `ddev wp cron event run --due-now --all`
- Check next runs for plugin jobs: `ddev wp cron event list | rg cron__`

### Local development: forcing runs

If an event exists but isn’t yet due, make it due or run the callback directly.

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
  - Ensure `DISABLE_WP_CRON` is not true in `wp-config.php` if expecting automatic WP‑Cron on requests. For dev, using WP‑CLI as above is sufficient.
