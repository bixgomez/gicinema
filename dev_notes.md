# gicinema — Dev Notes

Single source of truth for our collaboration notes. Keep entries concise and actionable.

## Conventions
- Dates in ISO format (YYYY-MM-DD)
- Reverse‑chronological session log
- Tags in brackets: [decision], [note], [todo], [question], [link], [idea]
- Use checkboxes for actions: - [ ] todo, - [x] done

## Session Log

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

## Context & Setup
- Repo root: `/Users/gilbert`
- Working mode: Codex CLI assistant; workspace-write FS; restricted network.
- Note style: concise bullets with tags and checkboxes; reverse‑chronological log.
- Local URL: `https://gicinema.ddev.site`
- Theme path: `wp-content/themes/cinema-theme`
