# Cinema Theme

A custom WordPress theme for **Grand Illusion Cinema**. It controls the public site shell, film and series templates, calendar display, custom post types, ACF block rendering, editor/admin styling, and the Sass/Gulp build pipeline.

The theme is based on Underscores and works with the custom `gicinema-plugin`: the plugin imports and maintains Film and screening data, while this theme registers the content model and displays that data on the front end.

## Overview

This theme is the presentation layer for the Grand Illusion Cinema WordPress site. It defines the site header, footer, navigation, typography, layout, responsive image sizes, and public templates for pages, Film posts, Film Series posts, search/archive screens, and the monthly calendar.

The theme also registers the site's custom post types: `film`, `series`, `director`, `format`, `country`, and `alert`. Those post types provide the editorial structure for cinema programming, while ACF field groups stored in `acf-json/` provide the film metadata, screening fields, series details, alert scheduling, and custom block fields used throughout the site.

Film schedules are displayed from the custom `gi_screenings` database table maintained by the companion plugin. The home page uses that table to build "Now Playing" and "Coming Soon" sections. The monthly calendar uses the same table to list films by date, and its mobile modal view loads a full film card via Ajax. Individual Film pages render the same shared `filmCard()` component, so film display stays consistent across home, calendar, block, and single-film contexts.

The theme includes a small set of ACF-powered blocks: Film, Text Block, Alert, and Event. It also adds admin tools for Film editing, including a compact "Title (Display)" field and bulk controls for deleting selected rows from the ACF `screenings` repeater.

The asset pipeline is built with Gulp 4 and Dart Sass. Sass entry files in `sass/` compile to root CSS files such as `style.css`, `style_admin.css`, `styles_editor.css`, and `styles_editor_extra.css`. The active front-end stylesheet is `style.css`, admin screens use `style_admin.css`, and the block editor receives `styles_editor_extra.css`.

## Features

- **Site Shell**
  - Renders the Grand Illusion header, logo, primary navigation, social navigation, donate link, footer contact information, and Mailchimp signup form.
  - Preloads self-hosted Work Sans, Anton, and Brothers Regular font files.
  - Provides a responsive off-canvas menu through `hc-offcanvas-nav`.

- **Film Display**
  - Uses `filmCard()` as the shared film display component.
  - Shows title, poster, director, year, runtime, format, trailer link, ticket link, custom ticket links, screenings, location, description, and additional info.
  - Uses the admin-only `title_display` value first, then ACF `short_name`, then the WordPress post title.
  - Gives the first home-page film poster eager loading and high fetch priority for LCP.

- **Schedule Display**
  - Reads active screenings from the custom `gi_screenings` table.
  - Home page lists currently playing films and upcoming films in next-screening order.
  - Monthly calendar groups films by day and marks days as past, present, or future.
  - Calendar film buttons open a modal and load the film card by Ajax.

- **Custom Content**
  - Registers `film`, `series`, `director`, `format`, `country`, and `alert` post types.
  - Saves Director post titles from ACF first and last name fields.
  - Stores ACF field groups in `acf-json/` for Film details, Film media, additional Film info, Series fields, Alert schedule, content areas, and custom blocks.

- **ACF Blocks**
  - Registers Film, Text Block, Alert, and Event blocks from `blocks/register-blocks.php`.
  - Uses `block.json` files with PHP render templates.
  - Provides block-specific ACF field groups in `acf-json/`.

- **Admin And Editor**
  - Adds a compact Film "Title (Display)" editor below the main title field.
  - Normalizes saved display titles by removing paragraph tags, line breaks, and extra whitespace.
  - Adds bulk select/delete controls to the Film `screenings` ACF repeater.
  - Enqueues theme admin CSS on admin and login screens.
  - Enqueues extra editor CSS for the block editor.

- **Performance And Cleanup**
  - Defines explicit image sizes and filters out unwanted generated core sizes.
  - Moves front-end jQuery to the footer and removes jQuery Migrate.
  - Disables XML-RPC methods.
  - Removes generator, RSD, WLW, feed links, emoji output, shortlinks, oEmbed routes/discovery, and selected Yoast debug output.
  - Disables WordPress' closest-slug guessing on 404s and old-slug redirects.

## Data Flow

1. **Plugin Import**
   - `gicinema-plugin` imports films and normalized screenings from Agile Ticketing.
   - It writes film metadata to Film posts and active screening rows to the custom `gi_screenings` table.

2. **Theme Queries**
   - Home and calendar templates query `gi_screenings` for active screenings.
   - Helper functions in `inc/functions/` map dates and post IDs to displayable Film cards and calendar entries.

3. **Film Rendering**
   - `filmCard()` reads Film fields, poster data, links, descriptions, and screenings.
   - The same component is used for home sections, single Film pages, the Film block, and calendar modal responses.

4. **Calendar Modal**
   - `page--calendar__monthly.php` renders a month grid.
   - `js/calendar.js` posts the selected Film ID to the `cinema_theme_ajax_call` action.
   - `inc/ajax--calendar.php` returns the film card HTML for the modal.

5. **Editor And Admin Enhancements**
   - ACF JSON defines the fields available to editors.
   - Admin scripts and hooks improve Film editing without changing the front-end data model.

## Installation

1. Place the theme folder at `/wp-content/themes/cinema-theme/`.
2. Activate **Cinema Theme** in WordPress.
3. Ensure the companion `gicinema-plugin` is active when using film imports and schedule display.
4. Sync ACF field groups from `acf-json/` if WordPress reports pending ACF JSON changes.
5. Confirm the primary menu is assigned to the `menu-1` location and that a menu named `social-media-menu` exists if social links should display.

## Requirements

- WordPress with PHP compatible with the project environment.
- Local development environment: DDEV project `gicinema`, PHP 8.2, nginx-fpm, MariaDB 10.4.
- Advanced Custom Fields for field groups and ACF-rendered blocks.
- The custom `gicinema-plugin` for Agile imports and the `gi_screenings` table used by schedule templates.
- Node.js and npm for rebuilding theme CSS.
- Gulp 4 and Dart Sass dependencies installed from `package.json`.

## Development Notes

- **File Structure**
  - `functions.php` - theme setup, enqueues, Ajax hook, post type includes, cleanup hooks, block registration.
  - `header.php` and `footer.php` - site shell, navigation, donate CTA, footer contact information, Mailchimp form.
  - `page--home__new.php` - custom home page template.
  - `page--calendar__monthly.php` - custom monthly calendar template.
  - `single-film.php` and `single-series.php` - Film and Series single templates.
  - `inc/posttypes/` - custom post type registrations.
  - `inc/functions/` - film card, schedule, date, and admin helper functions.
  - `blocks/` - ACF block definitions and render templates.
  - `acf-json/` - versioned ACF field groups.
  - `sass/` - Sass source for front-end, admin, and editor CSS.
  - `js/` - navigation, calendar modal, off-canvas menu config, and admin repeater controls.
  - `fonts/` and `images/` - self-hosted fonts and theme image assets.

- **Theme Support**
  - Supports `automatic-feed-links`, `title-tag`, `post-thumbnails`, and HTML5 search/comment/gallery/caption markup.
  - Registers one theme menu location: `menu-1` (`Primary`).
  - Registers one widget area: `sidebar-1`.
  - Defines image sizes: `thumbnail` 150x150 cropped, `small` 400px wide, `medium` 768px wide, and `large` 1040px wide.

- **Display Title Field**
  - Stored as post meta `title_display`.
  - Rendered on Film edit screens below the main title field.
  - Supports limited inline formatting through a compact editor.
  - Used before `short_name` and the WordPress post title in Film cards and calendar day entries.

## Build And Assets

- Sass entry files:
  - `sass/style.scss` -> `style.css`
  - `sass/style_admin.scss` -> `style_admin.css`
  - `sass/styles_editor.scss` -> `styles_editor.css`
  - `sass/styles_editor_extra.scss` -> `styles_editor_extra.css`

- Active enqueues:
  - Front end: `style.css`
  - Admin and login: `style_admin.css`
  - Block editor: `styles_editor_extra.css`

- Gulp tasks:
  - `npx gulp` - run Browsersync and watch Sass/PHP.
  - `npx gulp sass` - compile Sass with inline sourcemaps.
  - `npx gulp watch` - watch Sass/PHP without starting Browsersync.
  - `npx gulp build` - compile compressed production CSS without sourcemaps.

- Browsersync:
  - Proxy target: `https://gicinema.ddev.site`
  - Local port: `3003`

## Local Development

1. Start the local site from the project root:

```bash
ddev start
```

2. Install theme dependencies from the theme directory:

```bash
cd wp-content/themes/cinema-theme
npm ci
```

3. Start the watcher:

```bash
npx gulp
```

The local WordPress site runs at `https://gicinema.ddev.site`. Browsersync proxies it at `http://localhost:3003`.

## Custom Templates

- **Home Page (new)**
  - File: `page--home__new.php`
  - Displays page block content as "Special Events and Series" when blocks are present.
  - Queries upcoming active screenings from `gi_screenings`.
  - Displays films as "Now Playing" for roughly the current week and "Coming Soon" for later upcoming screenings.

- **Monthly Calendar**
  - File: `page--calendar__monthly.php`
  - Accepts `?month=YYYY-MM`.
  - Displays previous/next month links with abbreviated month names.
  - Shows "This month" only when viewing a different month.
  - Lists films per day from `gi_screenings` and opens film details in an Ajax modal.

- **Single Film**
  - File: `single-film.php`
  - Renders the current Film post through `filmCard()`.

- **Single Series**
  - File: `single-series.php`
  - Displays series logo, title, dates, subtitle, description, and post content.

## JavaScript

- `js/hc-offcanvas-nav.js` and `js/hc-offcanvas-nav--config.js` power the mobile/off-canvas primary navigation.
- `js/calendar.js` powers the monthly calendar modal and Ajax film-card loading.
- `js/admin-screenings-bulk.js` adds select-all, select-none, and delete-selected controls to the Film `screenings` ACF repeater.
- `js/customizer.js`, `js/navigation.js`, and `js/skip-link-focus-fix.js` remain from the Underscores foundation.

## Safety Notes

- The home and calendar templates assume the custom `gi_screenings` table exists. Keep `gicinema-plugin` active for schedule-driven pages.
- The theme disables several WordPress default outputs and redirects. Review `functions.php` before re-enabling feeds, oEmbed, XML-RPC, emojis, shortlinks, or old-slug redirects.
- The monthly calendar Ajax handler expects a Film post ID in `POST filmId` and returns rendered film-card HTML.
- Theme build commands write compiled CSS files into the theme root.

## Troubleshooting

- If the home page or calendar has no films, confirm `gicinema-plugin` is active and that `gi_screenings` contains active rows.
- If ACF fields are missing, sync the field groups from `acf-json/`.
- If the mobile menu does not open, confirm `hc-offcanvas-nav.js` and its config are enqueued and that the primary menu exists.
- If Browsersync does not load, confirm DDEV is running and `npx gulp` was started from the theme directory.
- If CSS changes do not appear, rebuild Sass and confirm `style.css`, `style_admin.css`, or `styles_editor_extra.css` changed as expected.

## License

See `LICENSE`. The theme header in `sass/style.scss` declares GPL v2 or later and notes its Underscores foundation.
