# gicinema — Dev Notes

Single source of truth for our collaboration notes. Keep entries concise and actionable.

## Conventions
- Dates in ISO format (YYYY-MM-DD)
- Reverse‑chronological session log
- Tags in brackets: [decision], [note], [todo], [question], [link], [idea]
- Use checkboxes for actions: - [ ] todo, - [x] done

## Session Log

### 2025-10-14
- [note] Clarified data flow paths drive the front end: theme queries the custom table `{$wpdb->prefix}gi_screenings` for Now Playing/Coming Soon and per‑film screenings, not the ACF field.
  - Front end files: `wp-content/themes/cinema-theme/page--home__new__save01.php` (lines querying `gi_screenings`), and `wp-content/themes/cinema-theme/inc/functions/function__get_screenings.php`.
- [note] Mapped “Sync All Screenings” precisely: loops all Film posts, reads canonical screenings from the custom table + ACF, merges with a timezone‑shadow guard, and writes back to ACF only; does not modify the table; no Agile calls; manual only (not a cron).
- [decision] Updated the Sync All Screenings page info to a detailed, bulleted overview and enabled safe HTML rendering inside the page “info” box for clarity.
  - Files changed:
    - `wp-content/plugins/gicinema-plugin/inc/admin-nav.php` — expanded `long` description for slug `gicinema--sync-all-screenings` into bullet list; updated `gicinema_render_page_info()` to allow limited HTML via `wp_kses`.
  - Impact: Admin page now documents exact behavior, entry points, what it does/doesn’t do, and timezone‑guard specifics.
- [note] Confirmed “Import From Agile” updates both storage paths: it upserts into the custom table (normalized to WP timezone) and then syncs ACF per film.
- [note] Confirmed “Sync All Screenings” is manual‑only; the scheduled path is the importer (which also keeps ACF in sync).
- [issue] Per‑film “Superfluous Screenings” indicator disagreed with the bulk “Delete Superfluous (All)” tool.
  - Root cause: Per‑film counter previously normalized without WP timezone and lacked the timezone‑shadow guard used by the bulk logic, causing false “superfluous” flags.
- [fix] DRY’d per‑film superfluous calculation to use the exact same code path as the bulk tool by invoking the deleter in dry‑run mode.
  - Files changed:
    - `wp-content/plugins/gicinema-plugin/function__compare_screenings.php` — both the inline count in `gicinema__render_matching_screenings()` and `gicinema__count_superfluous_screenings()` now call `gicinema__delete_superfluous_acf_screenings($post_id, true)` and read its `deleted` count.
  - Impact: The per‑film badge/analysis now matches the bulk tool’s decision‑making (WP‑timezone normalization + timezone‑shadow guard + table‑empty safety).
- [note] Reported behavior: On some films, pressing “DELETE SUPERFLUOUS SCREENINGS” appears to do nothing.
  - Likely reasons (by design):
    - Safety when the custom table has zero active rows for the film → no ACF deletions to avoid wiping editor‑entered data.
    - Timezone‑shadow guard treats ± WP offset twins as matches → ACF rows are kept.
- [idea] Make the per‑film action optionally affect the front end by updating the custom table as well.
  - Safe default: upsert missing times from the kept set (no deletions), `status = 1`, with a dry‑run preview.
  - Optional stricter mode (with explicit confirm): deactivate table rows not present in the kept set, still respecting timezone‑shadow guard.
- [idea] Add a “force” override for per‑film delete to bypass the timezone‑shadow guard and (optionally) the table‑empty safety for rare fix‑ups; expose via a second button or a `force=1` flag.
- [todo] Align warnings/docs:
  - Update Sync All page warning text (currently claims it updates “table AND field”; actual behavior: ACF only).
  - Update plugin `README.md` “Sync All Screenings” description to reflect that it does not fetch from Agile and does not modify the table.
- [todo] Implement per‑film enhancements:
  - Add optional “also update custom table (upsert only)” checkbox + dry‑run.
  - Add optional “force delete” path that disables timezone‑shadow guard (and possibly table‑empty safety) with clear confirmation.

- [fix] Per‑film delete kept ±offset “twins”; disable timezone‑shadow keep by default during deletions.
  - Context: On post 5079 (Cora Bora), ACF had 6 legitimate screenings plus 6 additional entries exactly +7h from each. After running “DELETE SUPERFLUOUS SCREENINGS,” those +7h twins were being kept.
  - Root cause: The delete routine treated timezone‑shifted values (± WP timezone offset, computed via `$tz->getOffset($dt)`) as matches and kept them to avoid false deletions during prior timezone issues.
  - Change: Default behavior for delete now keeps only exact matches; timezone‑shadow matching is DISABLED by default for deletions so +/‑ offset twins are removed.
  - Opt‑in guard: Can be re‑enabled via `define('GICINEMA_TZ_SHADOW_GUARD', true)` or `add_filter('gicinema_enable_tz_shadow_guard_delete', '__return_true')`.
  - File changed: `wp-content/plugins/gicinema-plugin/function__delete_superfluous_screenings.php` (keep decision block).
  - Impact: Running per‑film delete on 5079 should yield exactly the 6 canonical ACF rows; bulk tool shares the same core function and behavior.


### 2025-10-12
- [note] Investigated reported cron issue: tailed `wp-content/debug.log`; found repeated fatal errors from Official Facebook Pixel (invalid token) unrelated to our plugin crons.
- [note] Audited `gicinema-plugin` cron setup and backup routine; confirmed schedules in `cron_jobs.php` and daily DB backup to `../gicinema_dbs`.
- [decision] Fixed backup retention bug: recognize `.sql` and `.sql.gz` files; parse full `YYYY-MM-DD--HH-MM-SS` timestamp; deterministically sort; add safety guard (no deletion if filenames don’t match expected pattern).
- [decision] Improved SQL dump generation: preserve `NULL` values and escape strings via `$wpdb->prepare('%s', $v)`.
- [note] Files changed: `wp-content/plugins/gicinema-plugin/function__db_backup_and_cleanup.php`, `README.md` (root).
- [note] Added a Cron Jobs section to root `README.md` with hooks, schedules, retention, and DDEV WP‑CLI examples.
- [note] Verified plugin `README.md` already documents Cron Jobs; left as-is to avoid duplication.
- [todo] Update admin page copy in `wp-content/plugins/gicinema-plugin/page__db_backup_and_cleanup.php` to reflect detailed retention policy and add optional audit `error_log` line for deletions.

- [note] Timezone bug (critical): ACF screenings parsed/saved as UTC, causing ~7h offset (e.g., The Last Class entries displayed as 20:30/20:00/22:00 instead of local times). This mismatch between ACF (`screenings` repeater) and the custom table (`gi_screenings.screening`) caused the merge step to treat the same showtime as different values, doubling entries in “Array of merged screenings from both sources.”
- [decision] Standardize all date-time handling to the WordPress timezone (WP Settings → General → Timezone). Normalize every timestamp to `Y-m-d H:i:s` in WP timezone before storage or comparison.
- [note] Root cause analysis (detailed):
  - ACF field stores human strings like `m/d/Y g:i a` with no timezone. Parsing via `DateTime::createFromFormat()` without specifying a timezone (or relying on PHP default) treated them as UTC on our system, shifting Pacific times by +7/+8 hours.
  - Importer wrote Agile `StartDate` into the custom table using `strtotime()`/`date()` inconsistently with WP timezone assumptions. The mix led to ACF values and table values diverging by hours.
  - Comparison/merge helpers then saw different strings (`2025-10-12 20:00:00` vs `2025-10-12 13:00:00`) and “merged” both, creating duplicates.
- [fix] Implemented WP‑timezone normalization across all paths:
  - Parse and store Agile times in WP timezone when populating the custom table.
    - File: `wp-content/plugins/gicinema-plugin/function__import_screenings_from_agile.php`
      - Use `wp_timezone()` (or `timezone_string` fallback) to create a `DateTimeZone`.
      - `new DateTime($screening->StartDate, $tz)` then `$dt->setTimezone($tz)` and output `Y-m-d H:i:s`.
      - Removed `date_default_timezone_set()` and replaced with diagnostic output of WP timezone only.
  - Read/normalize ACF `screenings` consistently in WP timezone, preserving already-normalized values:
    - File: `wp-content/plugins/gicinema-plugin/function__sync_screenings.php`
      - In `gicinema__get_screenings_from_post()`, if value matches `^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$`, keep as-is; otherwise parse with `wp_timezone()` and format to `Y-m-d H:i:s`.
    - File: `wp-content/plugins/gicinema-plugin/function__compare_screenings.php`
      - Normalize ACF rows using WP timezone before intersecting with table values.
    - File: `wp-content/plugins/gicinema-plugin/function__sync_screenings_on_save.php`
      - In `gicinema__simplify_screenings_array()`, normalize each row with WP timezone, preserving pre‑normalized values.
    - File: `wp-content/plugins/gicinema-plugin/function__delete_superfluous_screenings.php`
      - Normalize ACF values to WP timezone before comparing to table; keep only matches.
- [impact] After normalization, ACF array values and custom table values align in local time; the merged array no longer duplicates identical showtimes; “Matching screenings” in the edit panel reflects true intersections.
- [verify] Steps to confirm fix:
  - Admin page: `/wp-admin/admin.php?page=gicinema--import-films-from-agile` and run import for a film (e.g., “The Last Class”).
    - Confirm “Array of screenings from ACF repeater field” shows local times (no +7h offset).
    - Confirm “Array of merged screenings from both sources” contains unique, sorted local timestamps.
  - CLI path (DDEV):
    - `ddev wp cron event run cron__update_agile_shows_array --due-now`
    - `ddev wp eval 'gicinema__import_films_from_agile();'`
    - Optional per‑film sync: `ddev wp eval 'gicinema__sync_screenings(POST_ID);'`
- [note] Data hygiene: existing ACF entries previously saved in UTC will be normalized on the next import/sync for each film. Use “Sync All Screenings” (`/wp-admin/admin.php?page=gicinema--sync-all-screenings`) once to normalize across all films.
- [todo] Prefix safety: `function__sync_screenings_on_save.php` still references `wp_gi_screenings` in two places. Replace with `$wpdb->prefix . 'gi_screenings'` for multisite/prefix safety.
- [decision] Duplicates in custom table output: Observed repeated timestamps in “Array of screenings from custom table”. Likely causes: historical duplicate rows (from period before unique constraints took effect) and lack of DISTINCT in readers. Mitigations applied:
  - Retrieval now uses `SELECT DISTINCT ... ORDER BY screening` in `gicinema__get_screenings_from_table()` and in the admin panel’s table list to avoid echoing duplicates.
  - Schema: added `UNIQUE KEY unique_screening_str (film_id, post_id, screening(19))` via `dbDelta` to enforce idempotency even if `screening_date/time` unique fails (TEXT keys require lengths). This complements existing unique on `(film_id, post_id, screening_date, screening_time)`.
  - Cleanup: `gicinema__dedupe_screenings_table()` (already called after import) removes historical dupes by keeping the lowest `screening_id` per `(screening, film_id, post_id)`.
  - Prefix safety fix: replaced hardcoded `wp_gi_screenings` with `$wpdb->prefix . 'gi_screenings'` in update/delete helpers.

#### Defense-in-depth: timezone-shadow guard

- Problem addressed: Post-normalization regressions could reintroduce ±7/±8 hour “phantom” showtimes due to timezone misparsing or DST edges.
- Strategy: When merging ACF screenings into canonical table screenings, reject any ACF candidate that equals a table screening shifted by the WP timezone offset at that instant (accounts for PDT/PST via `wp_timezone()->getOffset()`).
- Implementation details:
  - Location: `wp-content/plugins/gicinema-plugin/function__sync_screenings.php`
  - Function: `gicinema__merge_screenings_arrays()`
  - Logic per ACF value `v` (already normalized):
    - If `v` exists in table set → accept.
    - Else compute `$offset = wp_timezone()->getOffset(new DateTime(v, wp_timezone()))`.
    - If `v ± offset` matches any table value → skip `v` (treat as timezone-shadow duplicate).
    - Else accept `v`.
  - Merge accepted ACF values with table set; `array_unique` + sort.
- Toggle/disable options:
  - Constant: set `define('GICINEMA_TZ_SHADOW_GUARD', false);` in `wp-config.php` to disable entirely.
  - Filter: `add_filter('gicinema_enable_tz_shadow_guard', '__return_false');` to disable from code.
- Rationale: Only filters ACF candidates; never removes table rows, minimizing false positives (e.g., real 1:00 pm and 8:00 pm screenings on the same day are still allowed if they don’t align exactly with the timezone offset transformation).

- [note] UX improvement: On the Import from Agile admin page, film titles are now clickable links to their edit pages.
  - Change: In `function__import_films_from_agile.php`, delay rendering the `<h4>` title until after we determine `$post_ID`, then print `<h4><a href="EDIT_LINK">TITLE</a></h4>` with `target="_blank"` and proper escaping.

#### Deep dive: timezones + duplicate screenings (root cause, fixes, verification)

- Symptoms observed
  - ACF repeater (“Array of screenings from ACF repeater field”) displayed datetimes ~7 hours ahead of actual showtimes (e.g., 20:30 instead of 13:30 PT), implying UTC parsing.
  - Merged array (“Array of merged screenings from both sources”) doubled entries because the ACF side and the custom-table side didn’t match byte-for-byte.
  - “Array of screenings from custom table” sometimes repeated identical timestamps (e.g., `2025-10-12 13:00:00` twice).

- Root causes
  - Timezone normalization gap:
    - ACF values stored as human strings (`m/d/Y g:i a`) lack timezone. Prior parsing used PHP default timezone or naive `strtotime`, effectively treating inputs as UTC in this environment and reformatting them as UTC.
    - Importer inserted Agile `StartDate` with `strtotime()/date()` without an explicit WP timezone, which could diverge from ACF conversions.
    - Result: ACF normalized to UTC while the custom table held local (or mixed) times → strings didn’t match, so merges produced duplicates.
  - Unenforced uniqueness on inserts:
    - Table schema used `UNIQUE (film_id, post_id, screening_date, screening_time)` over TEXT columns. MySQL requires a prefix length on TEXT; without it, the index may not be created (“silently” during some dbDelta runs), leaving no unique constraint for `INSERT ... ON DUPLICATE KEY UPDATE` to latch onto.
    - The importer’s write path uses `INSERT ... ON DUPLICATE KEY UPDATE status = 1`. Without a working unique index, identical rows were inserted repeatedly.

- Fixes implemented (code and schema)
  - Timezone normalization to WP timezone (consistently normalize to `Y-m-d H:i:s`):
    - Importer: `function__import_screenings_from_agile.php`
      - Use `wp_timezone()` (or `timezone_string` fallback) with `DateTime` to parse each Agile `StartDate`, convert to WP timezone, and format `Y-m-d H:i:s` before insert.
      - Removed global `date_default_timezone_set()` side effects; only log the WP timezone used.
    - ACF readers/parsers and comparisons:
      - `function__sync_screenings.php` → `gicinema__get_screenings_from_post()` now:
        - Preserves already-normalized `Y-m-d H:i:s` values.
        - Otherwise parses `m/d/Y g:i a` in WP timezone and formats to `Y-m-d H:i:s`.
      - `function__compare_screenings.php` normalizes ACF values with WP timezone before intersecting with table values; preserves normalized values.
      - `function__sync_screenings_on_save.php` (`gicinema__simplify_screenings_array`) normalizes each row with WP timezone; preserves normalized values.
      - `function__delete_superfluous_screenings.php` normalizes ACF values to WP timezone before comparing to table values.
  - Database uniqueness/idempotency:
    - Schema: `function__create_custom_table.php` adds `UNIQUE KEY unique_screening_str (film_id, post_id, screening(19))` via `dbDelta`.
      - Rationale: `screening` is a `TEXT` formatted as `YYYY-MM-DD HH:MM:SS` (19 chars); indexing the first 19 characters is sufficient and valid for a unique constraint. This ensures `INSERT ... ON DUPLICATE KEY` de-duplicates on the normalized timestamp regardless of `screening_date/time` values.
      - Existing unique on `(film_id, post_id, screening_date, screening_time)` is kept for compatibility where supported.
    - Readers: use `SELECT DISTINCT` in both `gicinema__get_screenings_from_table()` and the admin list in `function__compare_screenings.php` to avoid presenting legacy dupes.
    - Dedupe helper: `function__dedupe_screenings_table.php` deletes older duplicates, keeping the minimum `screening_id` for each `(screening, film_id, post_id)`.
  - Prefix safety: Replaced hardcoded `wp_gi_screenings` with `$wpdb->prefix . 'gi_screenings'` in `function__sync_screenings_on_save.php` to respect custom prefixes/multisite.

- Operational guidance / verification
  - One-time cleanup after deploy:
    1) Run “Deduplicate Screenings Table” in admin (or `ddev wp eval 'gicinema__dedupe_screenings_table();'`).
    2) Run importer: refresh feed then import (`ddev wp cron event run cron__update_agile_shows_array --due-now` → `ddev wp eval 'gicinema__import_films_from_agile();'`). This re-normalizes and re-syncs.
    3) Spot-check a film (e.g., “The Last Class”):
       - “Array of screenings from ACF repeater field” shows correct local times; no +7h offset.
       - “Array of screenings from custom table” shows unique, sorted timestamps; no repeated lines.
       - “Array of merged screenings from both sources” contains unique values only.
  - Ongoing behavior:
    - New imports are idempotent due to the `unique_screening_str` constraint.
    - Any admin-side save of ACF screenings normalizes to WP timezone before syncing to the table.
  - If dbDelta cannot add the index (rare):
    - Manual SQL fallback: `CREATE UNIQUE INDEX unique_screening_str ON <prefix>gi_screenings (film_id, post_id, screening(19));`
    - Confirm with: `SHOW INDEX FROM <prefix>gi_screenings;`

- New admin utility
  - Delete Superfluous Screenings (All Films): `wp-content/plugins/gicinema-plugin/page__delete_all_superfluous_screenings.php`
    - Submenu label: “Delete Superfluous (All Films)”.
    - Batch UI with running log: processes one film at a time via AJAX and updates a live log and counters on screen.
    - DRY run supported (no changes) via checkbox; entries are prefixed with [dry].
    - Dry-run summary phrasing: "Processed N / N films — Would delete X; Would keep Y of Z total." (adjusts dynamically based on the checkbox).
    - AJAX endpoint: `wp_ajax_gicinema_delete_superfluous_batch` in `function__delete_superfluous_screenings.php`.
    - Security: `manage_options` capability check + nonce `gicinema_delete_all_superfluous`.
    - Flow: page preloads Film IDs; clicking Start iterates over them, calls the AJAX handler, logs per‑film results (title + link), and aggregates totals.
    - Uses the same timezone‑aware normalization and comparison logic as the per‑film button; safe after fixes above.
    - Code reuse: Both per‑film and batch paths call the same core function `gicinema__delete_superfluous_acf_screenings($post_id, $dry_run=false)` to avoid logic drift.
    - UX tweak: Do not list films that have zero ACF screenings (original==0) in the running log to reduce noise.
    - Ordering: Processes films in reverse chronological order (newest posts first) using `orderby => 'date', order => 'DESC'`.
    - Per‑film log phrasing: If any deletions would occur, show “would delete X of Y (keeping Z)” in bold red during dry runs; for live runs use “deleted X of Y (kept Z)”; neutral style when zero to delete.
    - Safety: The per‑film deletion logic now skips deletion entirely if the custom table has zero active screenings for that film (prevents wiping ACF when canonical set is empty/stale). It also treats timezone-shadow equivalents as matches to avoid false deletions.

- Overnight cleanup tool revisited
  - Page: `page__delete_overnight_screenings.php`; Function: `function__delete_overnight_screenings.php`
  - Added Dry run mode (default) that previews exactly which screening rows match the current naive time windows and would be deleted.
  - Preview lists per-row details (film title, date/time, screening_id) and offers a “Proceed to delete” confirmation.
  - Actual deletion now deletes the exact previewed rows by `screening_id` (chunked), rather than broad time-window deletes, to keep preview and result aligned.
  - Updated warning text to reflect that it deletes screening rows (not posts) and does not update ACF automatically.
  - Note: This tool remains a blunt instrument; long-term we can replace it with precise timezone-shadow cleanup using the same normalization logic introduced elsewhere.
  - Deprecated by default: Hidden from the menu and short-circuited via `apply_filters('gicinema_enable_overnight_tool', false)`. It can be temporarily re-enabled by hooking the filter to return true if needed.

- Dedupe automation removed
  - Removed automatic calls to `gicinema__dedupe_screenings_table()` at the end of the importer (`function__import_films_from_agile.php`) and at the start/end of the “Sync All Screenings” job (`function__sync_all_screenings.php`).
  - Rationale: With timezone normalization + the unique index on `screening(19)`, duplicates should not be introduced going forward. Keep the manual admin tool for one-shot cleanup or emergencies.

- Admin hub page polish
  - File: `page__admin.php`
  - Converted section titles to links to their respective subpages (All Film Posts, Import from Agile, Sync All Screenings, Dedupe Screenings, Backup Database, Delete All Films, Truncate Screenings Table).
  - Updated descriptions for accuracy: importer behavior, timezone normalization, sync semantics, dedupe’s purpose, backup retention policy and schedule, and clarified destructive tools.
  - Marked “Delete Overnight Screenings” as Deprecated (no link) with guidance to use “Delete Superfluous (All Films)” instead and a link to that page.

- DRY top navigation
  - Added `inc/admin-nav.php` with `gicinema_render_admin_nav($current_slug)` and a shared list of pages (including local-only tools).
  - Injected top nav across plugin pages: admin hub, All Film Posts, Update Agile Shows Array, Import from Agile, Sync All Screenings, Delete Superfluous (All Films), Dedupe Screenings, Backup Database (local), Delete All Films (local), Truncate Screenings (local).
  - Deprecated “Delete Overnight” appears disabled with an explanation by default (can be re-enabled via `gicinema_enable_overnight_tool` filter).
  - Navigation order now matches the sidebar submenu order (Home, All Film Posts, Update Agile Array, Import, Sync, Deprecated Overnight, Dedupe, local tools, then Delete Superfluous).

- Centralized descriptions (+ Cron metadata)
  - Each page now has centralized `short` and `long` blurbs in `gicinema_get_admin_nav_items()`; individual pages render both inside a single `.info` box via `gicinema_render_page_info($slug)`.
  - Added cron metadata per page and `gicinema_render_cron_info($slug)` to render a “Cron” section with hook, frequency, and notes (Update Agile, Import, Backup DB).

- Title + menu placement (header)
  - Implemented `gicinema_render_page_header()` hooked to `in_admin_header` to render the page title (from `title` or `label`) followed immediately by the horizontal menu; this ensures Title → Menu precedes all notices.
  - Removed in-page `<h2>` titles and duplicate menu renders to prevent duplication.

- Standardized notice placement (after menu)
  - For tools that previously printed inline output, we now buffer and wrap results into WP admin notices printed after the header (menu) and before page info:
    - Update Agile (`page__update_agile_array.php`)
    - Import from Agile (`page__import_from_agile.php`)
    - Sync All Screenings (`page__sync_all_screenings.php`)
    - Dedupe Screenings (`page__dedupe_screenings_table.php`)
  - Existing notices for Backup DB, Delete All Films, Truncate Screenings also now appear below the header menu.

- Destructive actions: confirmations + result details
  - Delete All Films (`page__delete_all_films.php`): pre-submit JS confirm dialog shows an exact count of Film posts to be deleted; result message now reports “Deleted X of Y; Z failed”
  - Truncate Screenings (`page__truncate_screenings_table.php`): pre-submit JS confirm shows the current row count and table name; result message reports rows removed

- Batch cleanup UI (global Delete Superfluous) improvements
  - Added DRY-run toggle, live log, skip listing films with zero ACF screenings, reverse chronological processing, red/bold emphasis for deletions, and phrasing polish (“would delete … (keeping …)” vs “deleted … (kept …)”).
  - Added AJAX endpoint `wp_ajax_gicinema_delete_superfluous_batch`; both batch and per‑film button share `gicinema__delete_superfluous_acf_screenings($post_id, $dry_run=false)`.
  - Safety: skip deletions when the canonical table has zero active screenings; treat timezone-shadow equivalents as matches (avoid false deletions).

- Overnight cleanup deprecated + preview-only
  - Deprecated tool hidden from menu by default; added dry-run preview listing exact rows and precise deletion by `screening_id` when explicitly re-enabled; updated page copy.

- Importer idempotency (duplicate screenings prevention)
  - Added `function__ensure_schema.php`: ensures a proper unique index on the normalized `screening` string and performs a one-time dedupe of historical duplicates (keeps the lowest `screening_id`).
  - Limited scope: We call `gicinema__ensure_screenings_unique_index()` at the start of `gicinema__import_films_from_agile()` so it only runs during Agile imports (manual or WP‑Cron), not on every request.
  - With the unique index in place, `INSERT ... ON DUPLICATE KEY UPDATE` prevents duplicate rows for the same (film_id, post_id, screening).
  - Added an option flag `gicinema_screenings_schema_v1` so dedupe runs at most once unless explicitly forced via the `gicinema_force_schema_ensure` filter.

### 2025-10-11
- [note] (No additional notes; items for this working session were moved to 2025-10-12.)

- Modified files (for traceability)
  - Timezone normalization: `function__import_screenings_from_agile.php`, `function__sync_screenings.php`, `function__compare_screenings.php`, `function__sync_screenings_on_save.php`, `function__delete_superfluous_screenings.php`.
  - Dedupe visibility/uniqueness: `function__sync_screenings.php` (DISTINCT), `function__compare_screenings.php` (DISTINCT), `function__create_custom_table.php` (unique index on `screening(19)`).
  - Safety: `function__sync_screenings_on_save.php` (prefix-safe queries).

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
