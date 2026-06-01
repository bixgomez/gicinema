<?php
/**
 * PHPUnit test bootstrap for gicinema plugin.
 *
 * Two distinct modes:
 *   - Unit tests (default): no WordPress; Brain Monkey mocks WP functions.
 *   - Integration tests (GICINEMA_INTEGRATION_TESTS=1): boots the real
 *     WordPress test library, which loads WordPress core and the plugin.
 */

// Composer autoloader (needed in both modes).
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Plugin root directory (used by the integration loader below).
define('GICINEMA_PLUGIN_DIR', dirname(__DIR__));

// Load Advanced Custom Fields PRO before the plugin (integration only). ACF Pro
// lives as a sibling plugin of gicinema-plugin. The plugin under test calls ACF
// functions (get_field/update_field/have_rows/get_sub_field/add_row) in the
// screening sync paths, so ACF must be present for stage 3/4 integration tests.
function _gicinema_load_acf_plugin() {
    $acf_main = dirname(GICINEMA_PLUGIN_DIR) . '/advanced-custom-fields-pro/acf.php';
    if (file_exists($acf_main)) {
        require_once $acf_main;
    }
}

// Register the "Film Details" ACF field group (which contains the `screenings`
// repeater) from the theme's acf-json export. In production ACF auto-loads this
// from the active theme, but the test environment does not activate the theme,
// so we register it explicitly. Without it, have_rows()/get_sub_field()/add_row()
// on the repeater would do nothing and stage 3/4 tests would assert nothing.
// Hooked to acf/init (fires once ACF is ready).
function _gicinema_register_test_field_groups() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    $group_file = dirname(GICINEMA_PLUGIN_DIR, 2)
        . '/themes/cinema-theme/acf-json/group_5bfe5b155c062.json';
    if (!file_exists($group_file)) {
        return;
    }
    $group = json_decode(file_get_contents($group_file), true);
    if (is_array($group)) {
        acf_add_local_field_group($group);
    }
}

// Load the whole plugin once WordPress mu-plugins are loaded (integration only).
function _manually_load_gicinema_plugin() {
    require GICINEMA_PLUGIN_DIR . '/gicinema.php';
}

$gicinema_integration = (getenv('GICINEMA_INTEGRATION_TESTS') === '1'
    || isset($_SERVER['GICINEMA_INTEGRATION_TESTS']));

if ($gicinema_integration) {
    // ---- Integration tests: boot the real WordPress test library. ----
    // Do NOT define ABSPATH or init Brain Monkey here; the WordPress test
    // suite defines ABSPATH itself (pointing at the throwaway WP core).
    $_tests_dir = getenv('WP_TESTS_DIR');
    if (!$_tests_dir) {
        $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
    }

    if (!file_exists($_tests_dir . '/includes/functions.php')) {
        echo "WordPress test suite not found. Integration tests require the WordPress test suite.\n";
        echo "See: https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/\n";
        exit(1);
    }

    require_once $_tests_dir . '/includes/functions.php';
    // Load ACF Pro first (priority 0), then the plugin under test (default 10).
    tests_add_filter('muplugins_loaded', '_gicinema_load_acf_plugin', 0);
    tests_add_filter('muplugins_loaded', '_manually_load_gicinema_plugin');
    // Register the Film Details field group once ACF has initialized.
    tests_add_filter('acf/init', '_gicinema_register_test_field_groups');
    require $_tests_dir . '/includes/bootstrap.php';
} else {
    // ---- Unit tests: no WordPress; mock with Brain Monkey. ----
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__DIR__) . '/');
    }
    if (class_exists('\Brain\Monkey')) {
        \Brain\Monkey\setUp();
    }
}
