<?php
/**
 * Exercise the private feed through WordPress REST dispatch and real storage.
 */
class ScreeningsApiTest extends WP_UnitTestCase {
  private $table_name;

  public function setUp(): void {
    parent::setUp();
    global $wpdb;
    $this->table_name = $wpdb->prefix . 'gi_screenings';
    update_option('timezone_string', 'America/Los_Angeles');
    gicinema__create_custom_table();
    $wpdb->query("DELETE FROM {$this->table_name}");
    gicinema__register_screenings_reader_role();
    wp_set_current_user(0);
    rest_get_server();
  }

  public function tearDown(): void {
    wp_set_current_user(0);
    parent::tearDown();
  }

  private function request($method = 'GET') {
    return rest_get_server()->dispatch(new WP_REST_Request($method, '/gicinema/v1/screenings'));
  }

  private function authorize_reader() {
    $user_id = self::factory()->user->create(['role' => 'gicinema_screenings_reader']);
    wp_set_current_user($user_id);
    return $user_id;
  }

  private function film($runtime = '95', $status = 'publish', $type = 'film') {
    $post_id = self::factory()->post->create([
      'post_type' => $type,
      'post_status' => $status,
      'post_title' => 'Movie &amp; Friends',
    ]);
    update_post_meta($post_id, 'film_length', $runtime);
    return $post_id;
  }

  private function screening($post_id, $start, $status = 1) {
    global $wpdb;
    $wpdb->insert($this->table_name, [
      'post_id' => $post_id,
      'film_id' => $post_id,
      'screening' => $start,
      'screening_date' => substr($start, 0, 10),
      'screening_time' => substr($start, 11),
      'status' => $status,
    ]);
    return (int) $wpdb->insert_id;
  }

  public function test_private_access_and_read_only_reader_role() {
    $this->assertSame(401, $this->request()->get_status());
    wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
    $this->assertSame(403, $this->request()->get_status());
    $this->authorize_reader();
    $this->assertSame(200, $this->request()->get_status());
    $this->assertFalse(current_user_can('edit_posts'));
    $this->assertFalse(current_user_can('manage_options'));
    $this->assertSame(404, $this->request('POST')->get_status());
    $this->assertSame(404, $this->request('DELETE')->get_status());
    wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
    $this->assertSame(200, $this->request()->get_status());
  }

  public function test_feed_includes_today_and_future_published_active_screenings_in_order() {
    $this->authorize_reader();
    $today = current_datetime()->setTime(0, 0, 0);
    $film = $this->film();
    $future = $today->modify('+2 days')->format('Y-m-d') . ' 19:30:00';
    $future_id = $this->screening($film, $future);
    $today_id = $this->screening($film, $today->format('Y-m-d H:i:s'));
    $this->screening($film, $today->modify('-1 second')->format('Y-m-d H:i:s'));
    $this->screening($film, $today->modify('+1 day')->format('Y-m-d H:i:s'), 0);
    $this->screening($this->film('95', 'draft'), $future);
    $this->screening($this->film('95', 'private'), $future);
    $this->screening($this->film('95', 'trash'), $future);
    $this->screening($this->film('95', 'publish', 'post'), $future);
    $this->screening(99999999, $future);

    $response = $this->request();
    $this->assertSame(200, $response->get_status());
    $data = $response->get_data();
    $this->assertSame('America/Los_Angeles', $data['timezone']);
    $this->assertSame($today->format('Y-m-d'), $data['from_date']);
    $this->assertSame([$today_id, $future_id], array_column($data['screenings'], 'screening_id'));
    $this->assertSame('Movie & Friends', $data['screenings'][1]['movie_title']);
    $this->assertSame(substr($future, 0, 10), $data['screenings'][1]['show_date']);
    $this->assertSame('19:30:00', $data['screenings'][1]['show_time']);
    $this->assertSame(95, $data['screenings'][1]['duration_minutes']);
    $this->assertSame((new DateTimeImmutable($future, wp_timezone()))->format(DATE_ATOM), $data['screenings'][1]['starts_at']);
    $this->assertSame('private, no-store', $response->get_headers()['Cache-Control']);
    $this->assertSame($data['screenings'], $this->request()->get_data()['screenings']);
  }

  public function test_timezone_offsets_follow_daylight_saving_time() {
    $this->authorize_reader();
    $year = (int) current_datetime()->format('Y') + 1;
    $film = $this->film();
    $this->screening($film, "$year-01-15 19:30:00");
    $this->screening($film, "$year-07-15 19:30:00");
    $rows = $this->request()->get_data()['screenings'];
    $this->assertSame("$year-01-15T19:30:00-08:00", $rows[0]['starts_at']);
    $this->assertSame("$year-07-15T19:30:00-07:00", $rows[1]['starts_at']);
  }

  public function test_unknown_or_invalid_runtime_is_null_without_dropping_the_screening() {
    $this->authorize_reader();
    $date = current_datetime()->modify('+1 day')->format('Y-m-d');
    foreach (['', '0', '-2', '95 minutes', '1.5', '95'] as $runtime) {
      $this->screening($this->film($runtime), "$date 19:30:00");
    }
    $rows = $this->request()->get_data()['screenings'];
    $this->assertSame([null, null, null, null, null, 95], array_column($rows, 'duration_minutes'));
  }

  public function test_empty_feed_is_successful_and_malformed_datetime_fails_without_partial_data() {
    $this->authorize_reader();
    $this->assertSame([], $this->request()->get_data()['screenings']);
    $year = (int) current_datetime()->format('Y') + 1;
    $this->screening($this->film(), "$year-02-31 19:30:00");
    $response = $this->request();
    $this->assertSame(503, $response->get_status());
    $this->assertSame('gicinema_screening_invalid', $response->get_data()['code']);
  }

  public function test_reader_application_password_authenticates_with_wordpress() {
    $user_id = $this->authorize_reader();
    $user = get_userdata($user_id);
    add_filter('wp_is_application_passwords_available', '__return_true');
    add_filter('application_password_is_api_request', '__return_true');
    try {
      $credential = WP_Application_Passwords::create_new_application_password($user_id, ['name' => 'Volunteer feed test']);
      $this->assertFalse(is_wp_error($credential));
      wp_set_current_user(0);
      $authenticated = wp_authenticate_application_password(null, $user->user_login, $credential[0]);
      $this->assertInstanceOf(WP_User::class, $authenticated);
      $this->assertSame($user_id, $authenticated->ID);
      wp_set_current_user($authenticated->ID);
      $this->assertSame(200, $this->request()->get_status());
    } finally {
      WP_Application_Passwords::delete_all_application_passwords($user_id);
      remove_filter('wp_is_application_passwords_available', '__return_true');
      remove_filter('application_password_is_api_request', '__return_true');
    }
  }

  public function test_database_failure_is_an_error_instead_of_an_empty_schedule() {
    global $wpdb;
    $this->authorize_reader();
    $backup = $this->table_name . '_api_test_backup';
    $wpdb->query("RENAME TABLE {$this->table_name} TO {$backup}");
    $previous = $wpdb->suppress_errors(true);
    try {
      $response = $this->request();
      $this->assertSame(503, $response->get_status());
      $this->assertSame('gicinema_screenings_unavailable', $response->get_data()['code']);
    } finally {
      $wpdb->query("RENAME TABLE {$backup} TO {$this->table_name}");
      $wpdb->suppress_errors($previous);
    }
  }
}
