<?php
/**
 * Private screening feed for the volunteer management site.
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Register a role that can read screenings without editing site content.
 */
function gicinema__register_screenings_reader_role() {
  if (!get_role('gicinema_screenings_reader')) {
    add_role('gicinema_screenings_reader', 'Screenings API Reader', [
      'read' => true,
      'read_gicinema_screenings' => true,
    ]);
  }
}
add_action('init', 'gicinema__register_screenings_reader_role');

function gicinema__register_screenings_api() {
  register_rest_route('gicinema/v1', '/screenings', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'gicinema__get_screenings_api_response',
    'permission_callback' => 'gicinema__can_read_screenings_api',
  ]);
}
add_action('rest_api_init', 'gicinema__register_screenings_api');

/**
 * WordPress authenticates Application Passwords before checking permission.
 */
function gicinema__can_read_screenings_api() {
  if (current_user_can('read_gicinema_screenings') || current_user_can('manage_options')) {
    return true;
  }

  return new WP_Error(
    'gicinema_screenings_forbidden',
    'You do not have permission to read the screenings feed.',
    ['status' => rest_authorization_required_code()]
  );
}

/**
 * Return all active screenings of published films from local midnight onward.
 */
function gicinema__get_screenings_api_response() {
  global $wpdb;

  $now = current_datetime();
  $from = $now->setTime(0, 0, 0);
  $table_name = $wpdb->prefix . 'gi_screenings';
  $rows = $wpdb->get_results($wpdb->prepare(
    "SELECT s.screening_id, s.post_id, s.screening, p.post_title
       FROM {$table_name} s
       INNER JOIN {$wpdb->posts} p ON p.ID = s.post_id
       WHERE s.status = 1 AND p.post_type = %s AND p.post_status = %s
         AND s.screening >= %s
       ORDER BY s.screening ASC, s.screening_id ASC",
    'film',
    'publish',
    $from->format('Y-m-d H:i:s')
  ), ARRAY_A);

  if ($wpdb->last_error) {
    return new WP_Error(
      'gicinema_screenings_unavailable',
      'The screenings feed is temporarily unavailable.',
      ['status' => 503]
    );
  }

  update_meta_cache('post', array_unique(array_column($rows, 'post_id')));
  $screenings = [];

  foreach ($rows as $row) {
    $start = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $row['screening'], $now->getTimezone());
    if (!$start || $start->format('Y-m-d H:i:s') !== $row['screening']) {
      return new WP_Error(
        'gicinema_screening_invalid',
        'A screening has an invalid start time. Correct it before importing the feed.',
        ['status' => 503]
      );
    }

    $runtime = trim((string) get_post_meta((int) $row['post_id'], 'film_length', true));
    $duration = ctype_digit($runtime) && (int) $runtime > 0 ? (int) $runtime : null;

    $screenings[] = [
      'screening_id' => (int) $row['screening_id'],
      'movie_title' => html_entity_decode(wp_strip_all_tags($row['post_title']), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
      'show_date' => $start->format('Y-m-d'),
      'show_time' => $start->format('H:i:s'),
      'duration_minutes' => $duration,
      'starts_at' => $start->format(DATE_ATOM),
    ];
  }

  $response = new WP_REST_Response([
    'timezone' => wp_timezone_string(),
    'from_date' => $from->format('Y-m-d'),
    'generated_at' => $now->format(DATE_ATOM),
    'screenings' => $screenings,
  ]);
  $response->header('Cache-Control', 'private, no-store');
  return $response;
}
