<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_page_display__import_films_from_agile() {

  echo '<div class="wrap wrap--gicinema">';
  gicinema_render_page_info('gicinema--import-films-from-agile');
  gicinema_render_cron_info('gicinema--import-films-from-agile');

  // Handle pasted JSON shortcut (sets transient and then runs import)
  if (isset($_POST['paste_agile_json']) && !empty($_POST['agile_json_input'])) {
    check_admin_referer('import_films_action', 'import_nonce');
    $raw = wp_unslash($_POST['agile_json_input']);
    // Strip UTF-8 BOM if present
    $clean = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    json_decode($clean);
    if (json_last_error() === JSON_ERROR_NONE) {
      set_transient('agile_shows_array', $clean, HOUR_IN_SECONDS);
      // Track timestamp and TTL for admin display
      update_option('gicinema_agile_shows_array_updated', time());
      update_option('gicinema_agile_shows_array_ttl', HOUR_IN_SECONDS);
      echo "<div class='notice notice-success'><p>Pasted JSON accepted and cached for 1 hour.</p></div>";
    } else {
      echo "<div class='notice notice-error'><p>Invalid JSON pasted. Please verify and try again.</p></div>";
    }
    require_once "function__import_films_from_agile.php";
    ob_start();
    gicinema__import_films_from_agile();
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Import from Agile finished.</strong></p><div class='gicinema-notice-content' style='max-height:420px; overflow:auto;'>{$html}</div></div>";

    // Check if the standard import form was submitted
  } elseif (isset($_POST['confirm_import']) && $_POST['confirm_import'] == 'yes') {
    require_once "function__import_films_from_agile.php";
    ob_start();
    gicinema__import_films_from_agile();
    $html = ob_get_clean();
    echo "<div class='notice notice-success'><p><strong>Import from Agile finished.</strong></p><div class='gicinema-notice-content' style='max-height:420px; overflow:auto;'>{$html}</div></div>";
  } else {
    // Display warning and confirmation form
?>

    <div class="warning">
      <p><strong>Warning:</strong> This action will import all film posts from Agile. This action is irreversible.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('import_films_action', 'import_nonce'); ?>
      <input type="hidden" name="confirm_import" value="yes">
      <input type="submit" class="button button-primary" value="Confirm Import From Agile">
    </form>

    <?php
    // Recent import attempts log (last 10) — show above manual fallback section
    $log = get_option('gicinema_import_log');
    if (is_array($log) && count($log)) {
      echo '<h3>Recent Import Attempts</h3>';
      echo '<table class="widefat striped" style="max-width:820px">';
      echo '<thead><tr><th style="width:200px;">Time</th><th style="width:120px;">Context</th><th style="width:120px;">Refreshed</th><th>Shows Count</th></tr></thead><tbody>';
      foreach (array_reverse($log) as $row) {
        $t = isset($row['time']) ? (int) $row['time'] : 0;
        $time_str = $t ? wp_date('Y-m-d H:i:s T', $t) : 'unknown';
        $ctx = isset($row['context']) ? esc_html((string)$row['context']) : 'unknown';
        $ref = !empty($row['refreshed']) ? 'yes' : 'no';
        $cnt = isset($row['count']) ? intval($row['count']) : 0;
        echo '<tr>'
          . '<td>' . esc_html($time_str) . '</td>'
          . '<td>' . $ctx . '</td>'
          . '<td>' . esc_html($ref) . '</td>'
          . '<td>' . esc_html((string)$cnt) . '</td>'
          . '</tr>';
      }
      echo '</tbody></table>';
    }
    ?>

    <h3>Manual Fallback: Paste Agile Feed JSON</h3>
    <p>If the server cannot reach the Agile JSON feed, paste the JSON here (copied from a machine that can access the URL). It will be cached for 1 hour and used for import.</p>
    <p>
      Open this feed on a computer that can access it and copy all text:
      <br>
      <a href="https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest" target="_blank" rel="noopener noreferrer">Agile JSON Feed (opens in new tab)</a>
    </p>
    <p>
      URL for copy/paste:
      <input type="text" readonly style="width:100%; font-family:monospace;" value="https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest" onclick="this.select();">
    </p>
    <form method="post">
      <?php wp_nonce_field('import_films_action', 'import_nonce'); ?>
      <textarea name="agile_json_input" rows="10" style="width:100%; font-family:monospace;"></textarea>
      <p>
        <button class="button">Validate, Cache, and Import</button>
        <input type="hidden" name="paste_agile_json" value="1">
      </p>
    </form>
<?php
  }

  echo '</div>';
}
