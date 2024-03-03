<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__db_backup_and_cleanup() {
  $backupDirPath = ABSPATH . '../gicinema_dbs'; // Assuming ABSPATH is the WordPress root directory
  $weekAgo = time() - (7 * 24 * 60 * 60); // Timestamp for 7 days ago

  // Create the directory if it doesn't exist
  if (!file_exists($backupDirPath)) {
      mkdir($backupDirPath, 0755, true);
  }

  // Delete old backup files
  foreach (glob($backupDirPath . '/*.{sql,sql.gz}', GLOB_BRACE) as $file) {
      if (filemtime($file) < $weekAgo) {
          unlink($file);
      }
  }

  // Perform the database backup
  $backupFilePath = $backupDirPath . '/gicinema-db--' . date('Y-m-d--H-i-s') . '.sql.gz';

  $command = sprintf(
      'mysqldump --user=%s --password=%s --host=%s %s | gzip > %s',
      escapeshellarg(DB_USER),
      escapeshellarg(DB_PASSWORD),
      escapeshellarg(DB_HOST),
      escapeshellarg(DB_NAME),
      escapeshellarg($backupFilePath)
  );
  system($command);
}
