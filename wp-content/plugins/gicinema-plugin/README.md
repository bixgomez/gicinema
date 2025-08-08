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

- **Admin Tools (under “Grand Illusion Cinema” in WP Admin)**
  - **Import from Agile**: Manually trigger film and screening imports.
  - **Sync All Screenings**: Re-syncs all screenings from Agile.
  - **View All Film Posts**: Lists all film posts in the system.
  - **Deduplicate Screenings Table**: Removes duplicate screening rows.
  - **Delete All Films**: Bulk delete all film posts (use with caution).
  - **Delete Overnight Screenings**: Removes screenings with start times between 12:00 AM and 6:00 AM. This helps clean up unexpected duplicate screenings caused by time zone offsets (likely GMT vs Pacific Time). The exact cause is unclear — could stem from Agile, WordPress, server config, or plugin logic.
  - **Truncate Screenings Table**: Completely empties the screenings table.
  - **Database Backup & Cleanup**: Backs up the screenings table and performs cleanup tasks.

- **Automation**
  - `cron_jobs.php` schedules regular syncs with Agile.
  - Ensures film and screening data stays current without manual intervention.

## Data Flow

1. **Agile Ticketing API** →  
   Raw film and screening data retrieved by `import_films_from_agile` and `import_screenings_from_agile`.
2. **Film Processing** →  
   Creates or updates WP posts of type `film`.
3. **Screenings Table** →  
   Populates or updates the custom MySQL table.
4. **Sync & Cleanup** →  
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
