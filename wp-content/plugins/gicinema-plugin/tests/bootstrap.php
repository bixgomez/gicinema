<?php
/**
 * PHPUnit test bootstrap for gicinema plugin.
 *
 * Loads WordPress test suite and plugin files for testing.
 */

// Define ABSPATH to prevent WordPress security checks from exiting
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

// Composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Load Brain Monkey for mocking WordPress functions in unit tests
if (class_exists('\Brain\Monkey')) {
    \Brain\Monkey\setUp();
}

// Plugin root directory
define('GICINEMA_PLUGIN_DIR', dirname(__DIR__));

// Load plugin files needed for testing
// Note: For unit tests, we load only what's needed. For integration tests,
// we'll load via WordPress test suite which will load the whole plugin.

/**
 * Manually load the plugin for integration tests.
 *
 * This function is called by the WordPress test suite bootstrap.
 */
function _manually_load_gicinema_plugin() {
    require GICINEMA_PLUGIN_DIR . '/gicinema.php';
}

// Check if WordPress test suite is available
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// For integration tests, load WordPress test suite
if (getenv('GICINEMA_INTEGRATION_TESTS') === '1' || isset($_SERVER['GICINEMA_INTEGRATION_TESTS'])) {
    if (file_exists($_tests_dir . '/includes/functions.php')) {
        require_once $_tests_dir . '/includes/functions.php';

        tests_add_filter('muplugins_loaded', '_manually_load_gicinema_plugin');

        require $_tests_dir . '/includes/bootstrap.php';
    } else {
        echo "WordPress test suite not found. Integration tests require WordPress test suite.\n";
        echo "See: https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/\n";
        exit(1);
    }
}
