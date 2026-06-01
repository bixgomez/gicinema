# GI Cinema Plugin Tests

Modern PHPUnit-based test suite for the Grand Illusion Cinema WordPress plugin.

Current validation status:

```text
Unit:        OK (42 tests, 45 assertions)
Integration: OK (11 tests, 66 assertions)
```

## Setup

### PHP dependencies

From the plugin root directory:

```bash
composer install
```

This installs:

- PHPUnit 9.6
- Yoast PHPUnit Polyfills
- Brain Monkey for unit-test WordPress function mocking

### WordPress integration test environment

Unit tests do not need WordPress. Integration tests do.

The integration suite boots a throwaway WordPress test install, loads the real
GI Cinema plugin, loads ACF Pro, and registers the Film Details field group from
the active theme's ACF JSON export.

Current integration bootstrap dependencies:

- WordPress test library in `/tmp/wordpress-tests-lib`
- WordPress test core in `/tmp/wordpress`
- Test database: `wordpress_test`
- ACF Pro: `wp-content/plugins/advanced-custom-fields-pro/acf.php`
- Film Details field group:
  `wp-content/themes/cinema-theme/acf-json/group_5bfe5b155c062.json`

In DDEV, install or reinstall the WordPress test library from the project root:

```bash
ddev exec "cd wp-content/plugins/gicinema-plugin && bin/install-wp-tests.sh wordpress_test root root db latest"
```

The WordPress test library lives in the container's `/tmp`, so it is wiped by a
DDEV restart. Re-run the install command above after restarting DDEV.

## Running Tests

### Unit tests

Unit tests are fast and do not boot WordPress:

```bash
./vendor/bin/phpunit --testsuite unit
```

Equivalent Composer script:

```bash
composer test:unit
```

### Integration tests

Integration tests must run with `GICINEMA_INTEGRATION_TESTS=1` so
`tests/bootstrap.php` boots the WordPress test suite instead of the unit-test
Brain Monkey bootstrap.

In DDEV:

```bash
ddev exec "cd wp-content/plugins/gicinema-plugin && GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration"
```

From inside an already-running DDEV web shell:

```bash
GICINEMA_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite integration
```

The integration tests stub outbound Agile/poster HTTP calls. They should not
contact Agile's live server.

### About `composer test`

The current `composer test` script runs plain `phpunit`, which attempts to run
all configured suites. Prefer the explicit unit and integration commands above
so the correct bootstrap mode is used.

### Run a specific test file

```bash
./vendor/bin/phpunit tests/unit/ParseScreeningDatetimeTest.php
```

### Run with coverage report

Requires Xdebug:

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Test Organization

```text
tests/
|-- bootstrap.php
|-- README.md
|-- unit/
|   |-- ParseScreeningDatetimeTest.php
|   |-- TimezoneShadowTest.php
|   |-- MergeScreeningsArraysTest.php
|   `-- UpdateFilmOnSaveWrapperTest.php
`-- integration/
    |-- UpdateAgileShowsArrayTest.php
    |-- ImportScreeningsTest.php
    |-- ImportFilmsFromAgileTest.php
    |-- SyncScreeningsToAcfTest.php
    |-- SyncOnSaveTest.php
    `-- fixtures/
        `-- agile-sample.json
```

## Bootstrap Modes

`tests/bootstrap.php` has two modes.

Unit mode is the default:

- defines a fake `ABSPATH` so plugin file guards pass
- uses Brain Monkey for WordPress function mocks
- does not boot WordPress

Integration mode is enabled with `GICINEMA_INTEGRATION_TESTS=1`:

- does not define `ABSPATH`; the WordPress test suite defines it
- does not initialize Brain Monkey
- loads ACF Pro before the GI Cinema plugin
- registers the Film Details ACF field group from theme JSON
- loads the GI Cinema plugin through the WordPress test bootstrap

## What Is Tested

### Unit tests

#### `ParseScreeningDatetimeTest.php`

Tests `gicinema__parse_screening_datetime()`, the strict parser that normalizes
known datetime formats to `Y-m-d H:i:s` in the WordPress timezone.

Coverage includes:

- normalized storage format: `Y-m-d H:i:s`
- ISO 8601 with UTC marker
- ISO 8601 with timezone offset
- ISO 8601 without timezone, Agile's current format
- ACF display format: `m/d/Y g:i a`
- ISO 8601 with milliseconds
- empty/null/non-string/unrecognized input rejection
- DST and standard-time UTC conversion

Deferred hardening note: PHP can silently normalize calendar-overflow dates such
as `2026-02-31`. That is documented in branch notes as future parser hardening.

#### `TimezoneShadowTest.php`

Tests `gicinema__is_timezone_shadow()`, the canonical helper for identifying
screenings offset by exactly 7 or 8 hours.

Coverage includes:

- 7-hour and 8-hour offset detection
- bidirectional detection
- non-shadow differences
- empty/null/invalid inputs
- day and month boundary crossings

#### `MergeScreeningsArraysTest.php`

Tests `gicinema__merge_screenings_arrays()`, the production merge path used by
`gicinema__sync_screenings()`.

Coverage includes:

- exact table/ACF matches kept without duplicates
- ACF-only non-shadow screenings kept
- 7-hour and 8-hour timezone-shadow ACF values skipped
- `gicinema_enable_tz_shadow_guard` filter disabling the guard

#### `UpdateFilmOnSaveWrapperTest.php`

Tests `gicinema__check_and_run_update_film_on_save()`, the `save_post` wrapper
around Film save syncing.

Coverage includes:

- non-film early return does not leak output buffers
- auto-draft early return does not leak output buffers
- skip-flag early return does not leak output buffers
- valid Film path closes its output buffer

### Integration tests

#### `UpdateAgileShowsArrayTest.php`

Tests the first stage of the Agile pipeline:

- valid Agile JSON is fetched through a stubbed HTTP response
- feed JSON is stored verbatim in the `agile_shows_array` transient
- successful fetch records updated timestamp, TTL, transient timeout, and log
  entry
- first bad response followed by retry success stores the retry response
- two bad non-error responses delete a stale transient
- first-request `WP_Error` preserves a stale transient
- retry `WP_Error` preserves a stale transient

#### `ImportScreeningsTest.php`

Tests Agile showtime import into the custom table:

- fixture `CurrentShowings[].StartDate` values are parsed
- rows are written to `gi_screenings`
- `screening`, `screening_date`, and `screening_time` preserve the expected
  wall-clock datetime

#### `ImportFilmsFromAgileTest.php`

Tests full Agile import from cached feed:

- creates or locates a Film post by Agile ID
- writes all mapped Film fields from the fixture
- imports screenings into the table
- syncs ACF screenings
- verifies final normalized screening datetimes
- verifies running the same cached feed twice does not create duplicate Film
  posts, active table rows, or ACF screening rows

#### `SyncScreeningsToAcfTest.php`

Tests custom table to ACF sync:

- seeds `gi_screenings`
- runs `gicinema__sync_screenings()`
- verifies ACF screenings normalize back to the same canonical datetimes

#### `SyncOnSaveTest.php`

Tests Film editor save behavior:

- writes ACF screenings to a Film post
- calls the save-sync function
- verifies active custom-table rows match the saved ACF datetimes
- verifies ACF still normalizes to the same canonical datetimes

## Test Data Philosophy

Tests use realistic cinema scheduling values:

- evening showtimes around 7:30 pm
- June dates for daylight time behavior
- January dates for standard time behavior
- Agile-style ISO strings without timezone offsets
- ACF display-format strings where relevant
- exact 7/8-hour timezone-shadow examples from the historical bug pattern

## Why These Tests Matter

The plugin has two major datetime flows:

```text
Agile import / Sync All:
custom table + ACF -> merge -> write ACF
uses gicinema__sync_screenings()

Film editor save:
ACF -> normalize -> write custom table
uses gicinema__sync_screenings_on_save()
```

The tests protect both directions of that sync and guard against regressions
that could reintroduce timezone-shifted duplicate screenings.

## Known Gaps

Good next candidates:

- existing Film update path, not only new Film creation
- ACF save edge cases, including empty screenings and inactive row reactivation
- Delete Superfluous cleanup behavior, after product rules are clarified
- parser hardening for calendar-overflow dates

## Continuous Integration

Future CI should run the unit suite first because it does not need WordPress.
Integration CI will need WordPress test-suite setup, a database service, ACF Pro
availability, and the Film Details field group JSON.

## Contributing

When adding or changing datetime-related behavior:

1. Add or update the narrowest unit test that describes the rule.
2. Add integration coverage when behavior crosses WordPress, ACF, or the custom
   table.
3. Run the unit suite.
4. Run the integration suite in DDEV when the change touches import, ACF, or
   custom-table sync behavior.
5. Update this README when adding test files or changing setup.
