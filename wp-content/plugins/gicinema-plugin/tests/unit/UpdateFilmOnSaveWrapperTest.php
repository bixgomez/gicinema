<?php
/**
 * Unit tests for the save_post wrapper around Film save syncing.
 *
 * These tests guard the output-buffer cleanup paths in
 * gicinema__check_and_run_update_film_on_save().
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class UpdateFilmOnSaveWrapperTest extends TestCase {

    /**
     * Output-buffer level at the start of each test.
     *
     * @var int
     */
    private $base_ob_level;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        $this->base_ob_level = ob_get_level();

        Functions\when('add_action')->justReturn(true);

        require_once GICINEMA_PLUGIN_DIR . '/function__update_film_on_save.php';
    }

    protected function tearDown(): void {
        unset($_POST['acf']);

        while (ob_get_level() > $this->base_ob_level) {
            ob_end_clean();
        }

        Monkey\tearDown();
        parent::tearDown();
    }

    private function make_post($status = 'publish') {
        return (object) array(
            'post_status' => $status,
        );
    }

    public function test_non_film_return_does_not_leak_output_buffer() {
        Functions\when('get_post_type')->justReturn('post');

        gicinema__check_and_run_update_film_on_save(123, $this->make_post(), false);

        $this->assertSame($this->base_ob_level, ob_get_level());
    }

    public function test_auto_draft_return_does_not_leak_output_buffer() {
        Functions\when('get_post_type')->justReturn('film');

        gicinema__check_and_run_update_film_on_save(123, $this->make_post('auto-draft'), false);

        $this->assertSame($this->base_ob_level, ob_get_level());
    }

    public function test_skip_flag_return_does_not_leak_output_buffer() {
        Functions\when('get_post_type')->justReturn('film');
        Functions\when('wp_cache_get')->justReturn(true);
        Functions\when('wp_cache_delete')->justReturn(true);

        gicinema__check_and_run_update_film_on_save(123, $this->make_post(), false);

        $this->assertSame($this->base_ob_level, ob_get_level());
    }

    public function test_valid_film_path_closes_output_buffer() {
        Functions\when('get_post_type')->justReturn('film');
        Functions\when('wp_cache_get')->justReturn(false);

        gicinema__check_and_run_update_film_on_save(123, $this->make_post(), false);

        $this->assertSame($this->base_ob_level, ob_get_level());
    }
}
