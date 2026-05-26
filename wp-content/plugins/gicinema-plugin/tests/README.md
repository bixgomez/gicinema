# GI Cinema Plugin Tests

Modern PHPUnit-based test suite for the Grand Illusion Cinema WordPress plugin.

## Setup

### 1. Install Dependencies

From the plugin root directory:

```bash
composer install
```

This installs:
- PHPUnit 9.6
- Yoast PHPUnit Polyfills (for PHP 8+ compatibility)
- Brain Monkey (for mocking WordPress functions in unit tests)

**That's it!** The current test suite (33 unit tests) requires no additional setup. Brain Monkey mocks all WordPress functions, so no WordPress installation is needed.

### 2. WordPress Test Suite (OPTIONAL - for future integration tests)

**Note:** The current test suite does not require WordPress. This section is only relevant if you plan to write integration tests in the future.

Integration tests (not yet implemented) would require the WordPress test suite. If/when needed:

```bash
# DDEV users
ddev wp scaffold plugin-tests gicinema-plugin
# Choose 's' to skip/keep the existing bootstrap.php

# Or manually
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

For now, you can safely skip this step.

## Running Tests

### Run all tests (currently just unit tests)

```bash
composer test
```

or specifically:

```bash
composer test:unit
```

**Current test suite:**
- 33 unit tests
- No WordPress installation required
- Fast execution (~34ms)
- Uses Brain Monkey to mock WordPress functions

### Future: Integration tests (not yet implemented)

When integration tests are written, they would run via:

```bash
export GICINEMA_INTEGRATION_TESTS=1
composer test:integration
```

This would require the WordPress test suite (see optional setup above).

### Run specific test file

```bash
vendor/bin/phpunit tests/unit/ParseScreeningDatetimeTest.php
```

### Run with coverage report (requires Xdebug)

```bash
vendor/bin/phpunit --coverage-html coverage/
```

## Test Organization

```
tests/
├── bootstrap.php              # Test bootstrap and setup
├── unit/                      # Unit tests (no WordPress dependencies)
│   ├── ParseScreeningDatetimeTest.php  # Datetime parser tests
│   └── TimezoneShadowTest.php          # Timezone shadow detection tests
├── integration/               # Integration tests (require WordPress)
│   └── (future tests)
└── README.md                  # This file
```

## What's Tested

### Unit Tests

#### `ParseScreeningDatetimeTest.php`

Tests the strict datetime parser (`gicinema__parse_screening_datetime()`) that ensures all datetime values are normalized to `Y-m-d H:i:s` format in the WordPress timezone.

**Test coverage:**
- Pattern 1: Already normalized (Y-m-d H:i:s)
- Pattern 2: ISO 8601 with UTC marker (Z)
- Pattern 3: ISO 8601 with timezone offset
- Pattern 4: ISO 8601 without timezone (Agile API format)
- Pattern 5: ACF display format (m/d/Y g:i a)
- Pattern 6: ISO 8601 with milliseconds
- Invalid format rejection (empty, null, non-string, invalid dates, unrecognized formats)
- DST vs Standard Time offset handling
- Context parameter acceptance

#### `TimezoneShadowTest.php`

Tests the timezone-shadow duplicate detection (`gicinema__is_timezone_shadow()`) that identifies screenings offset by exactly 7 or 8 hours (Pacific timezone offset from UTC).

**Test coverage:**
- 7-hour offset detection (DST period)
- 8-hour offset detection (Standard Time period)
- Bidirectional detection (both directions)
- Non-shadow difference rejection (1h, 6h, 9h, same time)
- Edge cases (empty, null, invalid values)
- Across day/month boundaries

### Integration Tests (Future - Not Yet Implemented)

Integration tests would test the full data flow:
- Agile import workflow
- ACF sync operations
- Film post save hook
- Superfluous screening cleanup
- Display formatting

**Status:** Not yet implemented. The current test suite focuses on unit testing the datetime parser foundation.

## Test Data Philosophy

Tests use realistic datetime values that match production scenarios:
- **Summer screenings** (June): Tests DST (UTC-7) handling
- **Winter screenings** (January): Tests Standard Time (UTC-8) handling
- **Evening showtimes** (19:30): Matches typical cinema schedules
- **Timezone offsets**: Tests the historical ±7/±8 hour duplicate bug

## Why These Tests Matter

The datetime parser is the foundation of the plugin's timezone safety. It prevents:
1. Timezone-shifted duplicate screenings (±7/±8 hour offsets)
2. Ambiguous datetime interpretation
3. PHP timezone vs WordPress timezone confusion
4. Data corruption during Agile imports and ACF saves

All production code paths (Agile import, ACF save, ACF read, sync operations, cleanup operations) use this parser, so comprehensive test coverage is critical.

## Continuous Integration

(Future) Add GitHub Actions workflow:

```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: php-actions/composer@v6
      - run: composer test:unit
```

## Contributing

When adding new datetime-related functionality:
1. Write unit tests first (TDD)
2. Ensure tests cover edge cases (DST transitions, month boundaries, invalid inputs)
3. Run full test suite before committing
4. Update this README if adding new test files
