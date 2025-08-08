<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__db_backup_and_cleanup() {
  $messages = [];

  $backupDirPath = ABSPATH . '../gicinema_dbs';
  $weekAgo = time() - (7 * 24 * 60 * 60);

  $messages[] = "Backup directory: " . $backupDirPath;

  // ... all your existing code ...

  // Continue with rest of function...
  $backupFilePath = $backupDirPath . '/gicinema-db--' . date('Y-m-d--H-i-s') . '.sql.gz';
  $messages[] = "Backup file will be: " . $backupFilePath;

  // Test the actual mysqldump command
  $command = sprintf(
    'mysqldump --user=%s --password=%s --host=%s %s | gzip > %s',
    escapeshellarg(DB_USER),
    escapeshellarg(DB_PASSWORD),
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_NAME),
    escapeshellarg($backupFilePath)
  );

  $messages[] = "Running command: " . $command;
  $result = system($command, $returnCode);
  $messages[] = "Command result code: " . $returnCode . " (0 = success)";

  if (file_exists($backupFilePath)) {
    $messages[] = "✓ Backup file created: " . filesize($backupFilePath) . " bytes";
  } else {
    $messages[] = "✗ Backup file NOT created";
  }

  return implode("<br>", $messages);
}