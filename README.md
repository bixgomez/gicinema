# Grand Illusion Cinema

#### By Richard Gilbert, adapted from a design by Sam Dawson

This is the WordPress site for the Grand Illusion Cinema in Seattle, WA.

## Overview

Grand Illusion Cinema is a custom WordPress implementation that integrates with Agile Ticketing to manage film programming, screenings, and schedules. The site automates film imports, maintains a custom screenings database, and provides calendar views and film display through custom templates and ACF-powered blocks.

## Key Components

### Custom Theme: Cinema Theme
Location: `wp-content/themes/cinema-theme/`

The custom theme provides the presentation layer including:
- Film display templates and components
- Monthly calendar with modal film details
- ACF-powered content blocks (Film, Text Block, Alert, Event)
- Responsive design with off-canvas navigation
- Sass/Gulp build pipeline

See `wp-content/themes/cinema-theme/README.md` for detailed theme documentation.

### Custom Plugin: GI Cinema Plugin
Location: `wp-content/plugins/gicinema-plugin/`

The custom plugin manages the data integration and automation:
- Automated film and screening imports from Agile Ticketing
- Custom database table for normalized screening times
- WP-Cron scheduled tasks for synchronization
- Admin tools for data management and cleanup
- Automated daily database backups

See `wp-content/plugins/gicinema-plugin/README.md` for detailed plugin documentation.

## Contributed Plugins

- Advanced Custom Fields PRO
- Classic Editor
- WP Crontrol
- WP Migrate Lite
- Yoast SEO

## Development Environment

- DDEV project: gicinema
- PHP version: 8.2
- Server: nginx-fpm
- Database: MariaDB 10.4
- Local URL: https://gicinema.ddev.site
- Browsersync: http://localhost:3003

## Data Flow

1. Plugin fetches film and screening data from Agile Ticketing API
2. Films are created/updated as WordPress posts with ACF metadata
3. Screenings are normalized and stored in custom database table
4. Theme queries the database to display films on home page and calendar
5. ACF blocks allow editors to feature films throughout the site

## Development Workflow

1. Start DDEV: `ddev start`
2. Navigate to theme directory: `cd wp-content/themes/cinema-theme`
3. Install dependencies: `npm ci`
4. Start watcher: `npx gulp`

## Copyright

Copyright 2018-2026 Richard Gilbert / Fezziwig Media

## License

See individual component LICENSE files for licensing information.
