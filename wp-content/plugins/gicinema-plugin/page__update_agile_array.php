<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__update_agile_array() {

  echo '<div class="wrap wrap--gicinema">';
  gicinema_render_page_info('gicinema--update-agile-array');
  gicinema_render_cron_info('gicinema--update-agile-array');

  // Check if the form was submitted
  if (isset($_POST['confirm_update']) && $_POST['confirm_update'] == 'yes') {
    require_once "function__update_agile_shows_array.php";
    ob_start();
    gicinema__update_agile_shows_array();
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Agile feed update finished.</strong></p><div class='gicinema-notice-content'>{$html}</div></div>";

    // Show recent feed update attempts ABOVE the transient dump for visibility (submit view)
    $log = get_option('gicinema_update_feed_log');
    if (is_array($log) && count($log)) {
      echo '<h3>Recent Feed Update Attempts</h3>';
      echo '<table class="widefat striped" style="max-width:820px">';
      echo '<thead><tr><th style="width:200px;">Time</th><th style="width:120px;">Context</th><th style="width:90px;">Retried</th><th style="width:90px;">Success</th><th style="width:90px;">HTTP</th><th>Bytes</th></tr></thead><tbody>';
      foreach (array_reverse($log) as $row) {
        $t = isset($row['time']) ? (int) $row['time'] : 0;
        $time_str = $t ? wp_date('Y-m-d H:i:s T', $t) : 'unknown';
        $ctx = isset($row['context']) ? esc_html((string)$row['context']) : 'unknown';
        $ret = !empty($row['retried']) ? 'yes' : 'no';
        $succ = !empty($row['success']) ? 'yes' : 'no';
        $code = isset($row['code']) ? intval($row['code']) : 0;
        $bytes = isset($row['bytes']) ? intval($row['bytes']) : 0;
        echo '<tr>'
          . '<td>' . esc_html($time_str) . '</td>'
          . '<td>' . $ctx . '</td>'
          . '<td>' . esc_html($ret) . '</td>'
          . '<td>' . esc_html($succ) . '</td>'
          . '<td>' . esc_html((string)$code) . '</td>'
          . '<td>' . esc_html((string)$bytes) . '</td>'
          . '</tr>';
      }
      echo '</tbody></table>';
    }

    // Show the resulting transient contents (raw JSON) for verification
    $body = get_transient('agile_shows_array');
    if ($body !== false) {
      $len = is_string($body) ? strlen($body) : 0;
      $updated_ts = (int) get_option('gicinema_agile_shows_array_updated');
      $ttl = (int) get_option('gicinema_agile_shows_array_ttl');
      $timeout = get_option('_transient_timeout_agile_shows_array');
      $updated_str = $updated_ts ? wp_date('Y-m-d H:i:s T', $updated_ts) : 'unknown';
      $expires_str = $timeout ? wp_date('Y-m-d H:i:s T', (int)$timeout) : 'unknown';
      echo '<h3 style="margin-top:16px;">agile_shows_array transient (raw)</h3>';
      echo '<p style="margin:6px 0;color:#666;">Updated: ' . esc_html($updated_str) . '</p>';
      echo '<p style="margin:0 0 8px;color:#666;">Expires: ' . esc_html($expires_str) . '</p>';
      echo '<p style="margin:6px 0 8px;color:#666;">Bytes: ' . intval($len) . '</p>';
      echo '<pre class="gicinema-transient-dump" style="white-space:pre-wrap;word-break:break-word;max-height:420px;overflow:auto;border:1px solid #ccd0d4;padding:8px;background:#fff;">' . esc_html((string)$body) . '</pre>';
    } else {
      echo "<div class='notice notice-warning'><p>No agile_shows_array transient is currently set.</p></div>";
    }
  } else {
    // Display warning and confirmation form
?>
    <form method="post">
      <?php wp_nonce_field('update_agile_array_action', 'update_nonce'); ?>
      <input type="hidden" name="confirm_update" value="yes">
      <input type="submit" class="button button-primary" value="Update Agile Shows Array">
    </form>
    <?php
  // Show recent feed update attempts ABOVE the transient dump for visibility
  $log = get_option('gicinema_update_feed_log');
  if (is_array($log) && count($log)) {
    echo '<h3>Recent Feed Update Attempts</h3>';
    echo '<table class="widefat striped" style="max-width:820px">';
    echo '<thead><tr><th style="width:200px;">Time</th><th style="width:120px;">Context</th><th style="width:90px;">Retried</th><th style="width:90px;">Success</th><th style="width:90px;">HTTP</th><th>Bytes</th></tr></thead><tbody>';
    foreach (array_reverse($log) as $row) {
      $t = isset($row['time']) ? (int) $row['time'] : 0;
      $time_str = $t ? wp_date('Y-m-d H:i:s T', $t) : 'unknown';
      $ctx = isset($row['context']) ? esc_html((string)$row['context']) : 'unknown';
      $ret = !empty($row['retried']) ? 'yes' : 'no';
      $succ = !empty($row['success']) ? 'yes' : 'no';
      $code = isset($row['code']) ? intval($row['code']) : 0;
      $bytes = isset($row['bytes']) ? intval($row['bytes']) : 0;
      echo '<tr>'
        . '<td>' . esc_html($time_str) . '</td>'
        . '<td>' . $ctx . '</td>'
        . '<td>' . esc_html($ret) . '</td>'
        . '<td>' . esc_html($succ) . '</td>'
        . '<td>' . esc_html((string)$code) . '</td>'
        . '<td>' . esc_html((string)$bytes) . '</td>'
        . '</tr>';
    }
    echo '</tbody></table>';
  }

  // Also display the current transient (if any) when simply viewing the page
  $current = get_transient('agile_shows_array');
  if ($current !== false) {
    $len2 = is_string($current) ? strlen($current) : 0;
    $updated_ts2 = (int) get_option('gicinema_agile_shows_array_updated');
    $timeout2 = get_option('_transient_timeout_agile_shows_array');
    $updated_str2 = $updated_ts2 ? wp_date('Y-m-d H:i:s T', $updated_ts2) : 'unknown';
    $expires_str2 = $timeout2 ? wp_date('Y-m-d H:i:s T', (int)$timeout2) : 'unknown';
    echo '<h3 style="margin-top:16px;">Current agile_shows_array transient (raw)</h3>';
    echo '<p style="margin:6px 0;color:#666;">Updated: ' . esc_html($updated_str2) . '</p>';
    echo '<p style="margin:0 0 8px;color:#666;">Expires: ' . esc_html($expires_str2) . '</p>';
    echo '<p style="margin:6px 0 8px;color:#666;">Bytes: ' . intval($len2) . '</p>';
    echo '<pre class="gicinema-transient-dump" style="white-space:pre-wrap;word-break:break-word;max-height:420px;overflow:auto;border:1px solid #ccd0d4;padding:8px;background:#fff;">' . esc_html((string)$current) . '</pre>';
  } else {
    echo "<div class='notice notice-info' style='margin-top:12px;'><p>No existing agile_shows_array transient found. Click the button above to fetch and cache the feed.</p></div>";
  }
    ?>
<?php
  }

  echo '</div>';
}
