<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__db_backup_and_cleanup.php";

// Secure download handler via admin-post endpoint (separate request → clean headers)
function gicinema_handle_download_backup() {
    if (!current_user_can('manage_options')) {
      wp_die('Unauthorized');
    }
    check_admin_referer('download_backup_action', 'download_nonce');
    $backupDirPath = ABSPATH . '../gicinema_dbs';
    $baseReal = realpath($backupDirPath);
    $fileParam = isset($_POST['file']) ? wp_unslash($_POST['file']) : '';
    $fileBase = basename($fileParam); // prevent traversal
    $fullPath = realpath($backupDirPath . '/' . $fileBase);
    if (!$baseReal || !$fullPath || strpos($fullPath, $baseReal) !== 0 || !is_file($fullPath)) {
      wp_die('Invalid file path');
    }
    $mime = 'application/octet-stream';
    if (str_ends_with($fullPath, '.sql.gz') || str_ends_with($fullPath, '.gz')) {
      $mime = 'application/gzip';
    } elseif (str_ends_with($fullPath, '.sql')) {
      $mime = 'text/plain; charset=UTF-8';
    }
    nocache_headers();
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $fileBase . '"');
    header('Content-Length: ' . filesize($fullPath));
    readfile($fullPath);
    exit;
}
add_action('admin_post_gicinema_download_backup', 'gicinema_handle_download_backup');

// Submenu registration is centralized in inc/admin-nav.php
function gicinema_page_display__db_backup_and_cleanup() {
    echo '<div class="wrap wrap--gicinema">';

    // Check if the form was submitted (render notice immediately after nav)
    if (isset($_POST['confirm_backup']) && $_POST['confirm_backup'] == 'yes') {
      $result = gicinema__db_backup_and_cleanup();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
    }

    // Always render page info + cron after nav (and after any notice)
    gicinema_render_page_info('gicinema--backup-database');
    gicinema_render_cron_info('gicinema--backup-database');

    // Always show the confirmation form
?>
    <div class="notice notice-error inline">
      <p><strong>Warning:</strong> This action will back up the current database and delete old backups according to the retention policy. This action is irreversible.</p>
    </div>
    <form method="post">
      <?php wp_nonce_field('backup_database_action', 'backup_nonce'); ?>
      <input type="hidden" name="confirm_backup" value="yes">
      <input type="submit" class="button button-primary" value="Confirm Database Backup and Cleanup">
    </form>
<?php

    // List existing backups (securely)
    $backupDirPath = ABSPATH . '../gicinema_dbs';
    $files = glob($backupDirPath . '/*.{sql,sql.gz}', GLOB_BRACE);
    if ($files && is_array($files) && count($files)) {
      // Build info and sort by mtime desc
      $items = [];
      foreach ($files as $f) {
        $isFile = is_file($f);
        if (!$isFile) {
          continue;
        }
        $base = basename($f);
        $mtime = filemtime($f);
        $size = filesize($f);
        $ext = (str_ends_with($base, '.sql.gz') ? 'sql.gz' : (str_ends_with($base, '.sql') ? 'sql' : pathinfo($base, PATHINFO_EXTENSION)));
        $items[] = ['name' => $base, 'mtime' => $mtime, 'size' => $size, 'ext' => $ext];
      }
      usort($items, function ($a, $b) {
        return $b['mtime'] <=> $a['mtime'];
      });

      echo '<h3>Existing Backups</h3>';
      echo '<table class="widefat striped" style="max-width:980px">';
      echo '<thead><tr>'
        . '<th>Filename</th>'
        . '<th style="width:180px;">Created</th>'
        . '<th style="width:120px;">Size</th>'
        . '<th style="width:120px;">Type</th>'
        . '<th style="width:120px;">Action</th>'
        . '</tr></thead><tbody>';
      foreach ($items as $it) {
        $created = wp_date('Y-m-d H:i:s T', (int)$it['mtime']);
        $size_h = size_format((float)$it['size'], 2);
        echo '<tr>'
          . '<td>' . esc_html($it['name']) . '</td>'
          . '<td>' . esc_html($created) . '</td>'
          . '<td>' . esc_html($size_h) . '</td>'
          . '<td>' . esc_html($it['ext']) . '</td>'
          . '<td>'
          . '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline;">'
          . wp_nonce_field('download_backup_action', 'download_nonce', true, false)
          . '<input type="hidden" name="action" value="gicinema_download_backup">'
          . '<input type="hidden" name="file" value="' . esc_attr($it['name']) . '">'
          . '<button class="button">Download</button>'
          . '</form>'
          . '</td>'
          . '</tr>';
      }
      echo '</tbody></table>';
    } else {
      echo '<p style="margin-top:12px;color:#666;">No backups found yet.</p>';
    }

    echo '</div>';
}
