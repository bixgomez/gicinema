# gicinema — Dev Notes

Single source of truth for our collaboration notes. Keep entries concise and actionable.

## Conventions
- Dates in ISO format (YYYY-MM-DD)
- Reverse‑chronological session log
- Tags in brackets: [decision], [note], [todo], [question], [link], [idea]
- Use checkboxes for actions: - [ ] todo, - [x] done

## Session Log

### 2025-10-11
- [note] Investigated reported cron issue: tailed `wp-content/debug.log`; found repeated fatal errors from Official Facebook Pixel (invalid token) unrelated to our plugin crons.
- [note] Audited `gicinema-plugin` cron setup and backup routine; confirmed schedules in `cron_jobs.php` and daily DB backup to `../gicinema_dbs`.
- [decision] Fixed backup retention bug: recognize `.sql` and `.sql.gz` files; parse full `YYYY-MM-DD--HH-MM-SS` timestamp; deterministically sort; add safety guard (no deletion if filenames don’t match expected pattern).
- [decision] Improved SQL dump generation: preserve `NULL` values and escape strings via `$wpdb->prepare('%s', $v)`.
- [note] Files changed: `wp-content/plugins/gicinema-plugin/function__db_backup_and_cleanup.php`, `README.md` (root).
- [note] Added a Cron Jobs section to root `README.md` with hooks, schedules, retention, and DDEV WP‑CLI examples.
- [note] Verified plugin `README.md` already documents Cron Jobs; left as-is to avoid duplication.
- [todo] Update admin page copy in `wp-content/plugins/gicinema-plugin/page__db_backup_and_cleanup.php` to reflect detailed retention policy and add optional audit `error_log` line for deletions.

### 2025-10-10 (later)
- [decision] Add admin-only Film field “Title (Display)” (`title_display`) below the Title; compact WYSIWYG with code editing; no links/format selector.
- [note] Save sanitization: strip `<p>`/`<br>` and line breaks; collapse whitespace; trim.
- [note] Usage precedence for display names: `title_display` → ACF `short_name` → post title.
- [note] Integrated into `inc/functions/film-card.php` and `inc/functions/display-film-for-date.php`.
- [note] Admin-only (not exposed via REST) and not wired to front-end templates beyond those helpers yet.

### 2025-10-10
- [note] Added Project Structure and Local Development sections; documented DDEV + Gulp workflow and common commands.

### 2025-09-28
- [note] Initialized this `dev_notes.md` and agreed to store all collaboration notes here.

## Decisions
- 2025-09-28 [decision] Use this file as the canonical log for notes, decisions, and action items.
- 2025-10-10 [decision] Prefer Film `title_display` meta as the display name when present; otherwise fall back to `short_name` then post title.

## TODO Backlog
- [x] Capture project structure and basic run/build commands.
- [ ] Identify primary goals/milestones for the gicinema site.

## Open Questions
- [question] What is the tech stack and repo layout for gicinema?
- [question] Any deadlines, launch targets, or scope constraints?

## Project Structure
- WordPress root: current repo directory; DDEV used for environment.
- Theme: `wp-content/themes/cinema-theme` (Sass + Gulp build pipeline).
- DDEV config: `.ddev/config.yaml` (WordPress, PHP 8.2, nginx-fpm, MariaDB 10.4).
- Theme build inputs: `sass/**/*.scss`; outputs CSS to theme root `style.css` and admin/editor CSS.
- Gulpfile: `wp-content/themes/cinema-theme/gulpfile.js` (Browsersync proxy → `gicinema.ddev.site`).

## Local Development
- Start DDEV
  - `ddev start`
  - Site URL: `https://gicinema.ddev.site`
- Install theme dependencies (from theme dir)
  - `cd wp-content/themes/cinema-theme`
  - `npm ci` (or `npm install` if no clean lock install needed)
- Run dev server (Sass watch + Browsersync)
  - `npx gulp` (proxies `https://gicinema.ddev.site`, serves on port `3003`)
- One-off Sass compile
  - `npx gulp sass`
- Production/minified CSS build
  - `npx gulp build`
- Optional via DDEV container (if preferring in-container Node)
  - `ddev ssh`
  - `cd wp-content/themes/cinema-theme && npm ci && npx gulp`

## WP‑Cron Quick Reference (DDEV)
- Ensure env + plugin
  - `ddev start`
  - `ddev wp plugin activate gicinema-plugin`
  - First page hit to load schedules: open `https://gicinema.ddev.site/`
- List our events
  - `ddev wp cron event list --fields=hook,next_run,recurrence | grep cron__`
- Make events due, then run
  - Feed: `ddev wp cron event schedule cron__update_agile_shows_array 'now -1 minute' --schedule=every_23_minutes`
  - Import: `ddev wp cron event schedule cron__import_films_from_agile 'now -1 minute' --schedule=every_30_minutes`
  - Backup: `ddev wp cron event schedule cron__db_backup_and_cleanup 'now -1 minute' --schedule=daily`
  - Execute: `ddev wp cron event run <hook> --due-now`
- Run all due
  - `ddev wp cron event run --due-now --all`
- Bypass scheduling (direct callbacks)
  - Feed: `ddev wp eval 'gicinema__update_agile_shows_array();'`
  - Import: `ddev wp eval 'gicinema__import_films_from_agile();'`
  - Via hook: `ddev wp eval 'do_action("cron__import_films_from_agile");'`
- Note: If expecting automatic runs on requests, ensure `DISABLE_WP_CRON` is not true in `wp-config.php`.

## Context & Setup
- Repo root: `/Users/gilbert`
- Working mode: Codex CLI assistant; workspace-write FS; restricted network.
- Note style: concise bullets with tags and checkboxes; reverse‑chronological log.
- Local URL: `https://gicinema.ddev.site`
- Theme path: `wp-content/themes/cinema-theme`
