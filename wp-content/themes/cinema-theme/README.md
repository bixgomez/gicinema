Cinema Theme (Grand Illusion Cinema)
===================================

Custom WordPress theme for the Grand Illusion Cinema site (gicinema). Built on Underscores, extended with a Sass + Gulp pipeline and custom content types for films, series, formats, and more.

Overview
- WordPress theme located at `wp-content/themes/cinema-theme`
- Local environment via DDEV at `https://gicinema.ddev.site`
- Styles compiled from Sass with Gulp; Browsersync live reload
- Custom Post Types: `film`, `series`, `director`, `format`, `country`, `alert`
- ACF field groups tracked in `acf-json/`

Requirements
- Docker + DDEV (PHP 8.2, nginx-fpm, MariaDB 10.4) — project config in `.ddev/config.yaml`
- Node.js LTS (18+) and npm
- No global Gulp needed; commands use `npx`

Quick Start (Local)
1) Start DDEV (from repo root)
   - `ddev start`
   - Site URL: `https://gicinema.ddev.site`
2) Install theme dependencies
   - `cd wp-content/themes/cinema-theme`
   - `npm ci` (or `npm install`)
3) Run dev watcher with Browsersync
   - `npx gulp`
   - Proxies `https://gicinema.ddev.site`, serves on port `3003`

Common Commands
- Dev/watch + live reload: `npx gulp`
- One‑off Sass compile: `npx gulp sass`
- Production/minified CSS: `npx gulp build`
- Run inside the web container:
  - `ddev ssh`
  - `cd wp-content/themes/cinema-theme && npm ci && npx gulp`

Build & Assets
- Entry Sass files:
  - Theme: `sass/style.scss` → outputs `style.css`
  - Admin: `sass/style_admin.scss` → outputs `style_admin.css`
  - Editor: `sass/styles_editor.scss`, `sass/styles_editor_extra.scss` → outputs editor CSS
- Gulp features:
  - Dart Sass compilation with glob imports
  - Sourcemaps and Autoprefixer
  - Cache‑busted enqueues using `filemtime()` in `functions.php`
- Browsersync:
  - Proxy target: `gicinema.ddev.site`
  - Local server: `http://localhost:3003`

Theme Structure (selected)
- `functions.php` — theme setup, enqueues, Ajax hook, head cleanup
- `header.php`, `footer.php` — site shell, menus, donate CTA, Mailchimp form
- `sass/` — modular SCSS: `config`, `layout`, `navigation`, `elements`, `components`, `pages`, `admin`
- `js/` — navigation, off‑canvas nav, calendar behaviors
- `inc/cinema.php` — includes all custom post types
- `inc/posttypes/` — CPT registrations (`film`, `series`, `director`, `format`, `country`, `alert`)
- `inc/functions/` — helpers for films and screenings (cards, queries, dates)
- `template-parts/alerts.php` — reusable alerts partial
- `acf-json/` — versioned ACF field groups for sync

Custom Content
- Film (`inc/posttypes/posttype--film.php`): public CPT with archive, featured images
- Series (`inc/posttypes/posttype--series.php`): groups films; REST enabled
- Director (`inc/posttypes/posttype--director.php`): title auto‑generated from ACF first/last name
- Format (`inc/posttypes/posttype--format.php`): film/video formats; REST enabled
- Country (`inc/posttypes/posttype--country.php`): country taxonomy as CPT title only
- Alert (`inc/posttypes/posttype--alerts.php`): site‑wide messages

Templates
- Home (new): `page--home__new.php`
  - Renders “Now Playing” and “Upcoming” using custom `gi_screenings` table + `filmCard()`
- Calendar (monthly): `page--calendar__monthly.php`
  - Uses helpers in `inc/functions/` to list films per day, with client JS in `js/calendar.js`
- Single Film: `single-film.php`
  - Uses `filmCard()` to render a film detail view

Display Title Field
- Admin-only field: “Title (Display)” stored as post meta `title_display`
- Location: Film edit screen, directly under the main Title (inside `#titlediv`)
- Editor: compact WYSIWYG with Text/Quicktags; no links/format selector; custom `del` without `datetime`
- Save normalization: strips `<p>` and `<br>` tags and any line breaks; collapses whitespace and trims
- Usage precedence:
  - `title_display` if non-empty after stripping tags/whitespace
  - else ACF `short_name`
  - else WordPress post title
- Implemented in:
  - `inc/functions/film-card.php`
  - `inc/functions/display-film-for-date.php`
- REST: not registered for REST; admin-only until needed

Ajax
- Action: `cinema_theme_ajax_call` (front + logged‑out)
- Handler includes `inc/ajax--calendar.php`
- Global `ajaxurl` injected in `<head>` via `cinema_theme_ajaxurl()`

Menus & Theme Support
- Menus: `menu-1` (Primary), plus separate Social menu in header
- Supports: `title-tag`, `post-thumbnails`, HTML5 forms/comments/gallery/caption

Security & Head Cleanup
- Disables XML‑RPC endpoints
- Removes generator, RSD, WLW, and feed links from `<head>`

Coding Standards
- PHP: PHPCS config available at `phpcs.xml.dist`
- Sass lint: `gulp-sass-lint` supported (config referenced as `.sass-lint.yml` if present)

Troubleshooting
- Browsersync not reloading: ensure `ddev start` is running and theme `npx gulp` is active
- SSL trust: trust DDEV certificates (`ddev trust-ca`) if the browser blocks proxying
- Node errors: use a current LTS (18+) and reinstall deps with `npm ci`

License
- See `LICENSE` (GPL v2 or later). Theme header in `sass/style.scss` reflects the license.
