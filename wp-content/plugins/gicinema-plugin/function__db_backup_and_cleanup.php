<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__db_backup_and_cleanup() {
  global $wpdb;
  $messages = [];

  $backupDirPath = ABSPATH . '../gicinema_dbs';
  $weekAgo = time() - (7 * 24 * 60 * 60);

  // Create the directory if it doesn't exist
  if (!file_exists($backupDirPath)) {
    if (!mkdir($backupDirPath, 0700, true)) { // 0700 for better security
      $messages[] = "✗ Failed to create backup directory";
      return implode("<br>", $messages);
    }
    $messages[] = "✓ Created backup directory";
  }

  // Clean up old backups
  foreach (glob($backupDirPath . '/*.{sql,sql.gz}', GLOB_BRACE) as $file) {
    if (filemtime($file) < $weekAgo) {
      unlink($file);
      $messages[] = "✓ Deleted old backup: " . basename($file);
    }
  }

  // Generate backup file path
  $backupFilePath = $backupDirPath . '/gicinema-db--' . date('Y-m-d--H-i-s') . '.sql.gz';

  // Create the actual backup (we'll add this next)
  $result = create_database_backup($backupFilePath, $wpdb);

  if ($result['success']) {
    $messages[] = "✓ Backup created: " . number_format(filesize($backupFilePath)) . " bytes";
  } else {
    $messages[] = "✗ Backup failed: " . $result['error'];
  }

  return implode("<br>", $messages);
}

function create_database_backup($backupFilePath, $wpdb) {
  try {
    // Get all database tables
    $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

    if (empty($tables)) {
      return ['success' => false, 'error' => 'No tables found in database'];
    }

    // Open compressed file for writing
    $file = gzopen($backupFilePath, 'w');
    if (!$file) {
      return ['success' => false, 'error' => 'Could not create backup file'];
    }

    // Write SQL header
    gzwrite($file, "-- WordPress Database Backup\n");
    gzwrite($file, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");

    // Export each table
    foreach ($tables as $table) {
      $tableName = $table[0];

      // Get table structure
      $createTable = $wpdb->get_var("SHOW CREATE TABLE `$tableName`", 1);
      gzwrite($file, "DROP TABLE IF EXISTS `$tableName`;\n");
      gzwrite($file, $createTable . ";\n\n");

      // Get table data
      $rows = $wpdb->get_results("SELECT * FROM `$tableName`", ARRAY_A);
      if (!empty($rows)) {
        foreach ($rows as $row) {
          $values = array_map([$wpdb, 'prepare'], array_fill(0, count($row), '%s'), array_values($row));
          gzwrite($file, "INSERT INTO `$tableName` VALUES (" . implode(',', $values) . ");\n");
        }
        gzwrite($file, "\n");
      }
    }

    gzclose($file);
    return ['success' => true, 'error' => null];
  } catch (Exception $e) {
    return ['success' => false, 'error' => $e->getMessage()];
  }
}
