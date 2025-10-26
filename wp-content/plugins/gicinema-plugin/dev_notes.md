# gicinema-plugin — Dev Notes

Plugin-specific collaboration log and action items. Keep concise, reverse-chronological, ISO dates.

## Conventions
- Tags: [decision], [note], [todo], [question], [link], [issue]
- Checkboxes for actions: - [ ] todo, - [x] done
- Scope: This file is only for the WordPress plugin.

## Session Log

### 2025-09-28
- [note] Initialized plugin `dev_notes.md` and aligned with README scope (Agile import, screenings table, admin tools, cron).
- [note] Source path: `Sites/DDEV/gicinema/wp-content/plugins/gicinema-plugin/`.
- [note] Added a placeholder box at the top of the Edit Film form using `edit_form_after_title` hook. File: `gicinema.php: add_action('edit_form_after_title', 'gicinema_render_film_top_box')`. Basic styles in `css/gicinema-plugin.css`.
- [note] Panel now shows dynamic screenings count for the current film, sourced from `{$wpdb->prefix}gi_screenings` with `status=1` and labels it “(from custom table)”.
- [note] Added second line to panel showing count of rows in ACF `screenings` repeater for current film (fallback to post meta row count if ACF unavailable). Labeled “(from Screenings field)”.
- [note] Added comparison helper `function__compare_screenings.php` with `gicinema__render_matching_screenings($post_id)` to compute intersection of custom-table vs ACF screenings and render a bulleted list labeled “Matching screenings:”. Wired into top panel output in `gicinema.php`.
- [note] Added `gicinema__render_table_screenings($post_id)` to render a bulleted list of all active screenings from the custom table under the custom-table count line.
- [note] Appended analysis message to matching section: shows “X Superfluous Screenings” in red or “No Superfluous Screenings” in green, computed from ACF rows not present in the custom table.
- [note] Button styling now reflects need: if superfluous > 0, the “DELETE SUPERFLUOUS SCREENINGS” button renders with red background and bold white text; otherwise standard styling. Implemented via helper `gicinema__count_superfluous_screenings()`.
- [note] Added `function__delete_superfluous_screenings.php` with:
  - `gicinema__delete_superfluous_acf_screenings($post_id)` to remove non-matching ACF screenings vs custom table and update the repeater.
  - `gicinema_handle_delete_superfluous_screenings()` admin-post handler with nonce/capability checks; sets admin notice and redirects back to edit screen.
  - Wired a form/button “DELETE SUPERFLUOUS SCREENINGS” into the top panel in `gicinema.php`.
- [fix] Handler now reads `post_id` from `$_REQUEST` (works with GET link) to avoid “Invalid post”.
- [note] Verified delete action via admin-post GET URL; edit screen reloads with success notice and ACF `screenings` trimmed to matches.
- [note] Conditional red “DELETE SUPERFLUOUS SCREENINGS” button appears only when superfluous > 0; otherwise standard secondary styling remains.

## Decisions
- 2025-09-28 [decision] Track plugin tasks and findings here; keep root `dev_notes.md` for broader site notes.

## TODO Backlog
- [ ] Document environment/config: required constants in `gicinema.php` (API keys, endpoints), WP cron expectations.
- [ ] Trace data flow end-to-end: update_agile_shows_array → import_films → screenings → ACF sync.
- [ ] Identify/descope destructive admin actions for prod safety (confirm backups, capabilities checks, nonces).
- [ ] Review/standardize function naming and file responsibility (function__* vs page__* separation).
- [ ] Verify cron timings and idempotency (23m/30m/daily 9 PM) and WP-Cron vs server cron.
- [ ] Add lightweight diagnostics/logging for imports and dedupe operations.
- [ ] If using Block Editor for `film`, consider registering a meta box fallback to ensure visibility (the `edit_form_after_title` hook targets classic layout).

## Open Questions
- [question] Where are Agile credentials and endpoints defined (env, wp-config, or constants in code)?
- [question] Preferred logging mechanism (WP debug log, custom table, or admin notices)?
- [question] Any production constraints (timeouts, API rate limits, timezone handling) to codify?

## Quick Context
- Files of note: `gicinema.php`, `cron_jobs.php`, `function__*.php`, `page__*.php`, `css/gicinema-plugin.css`.
- Admin pages include: Update Agile Shows Array, Import from Agile, Sync All Screenings, View All Film Posts, Deduplicate Screenings, Delete All Films, Delete Overnight Screenings, Truncate Screenings, Database Backup & Cleanup.
