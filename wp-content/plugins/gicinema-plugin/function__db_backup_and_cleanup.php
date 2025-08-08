<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema__db_backup_and_cleanup() {
  // CSRF Protection - only when called via admin form
  if (isset($_POST['confirm_backup'])) {
    if (!isset($_POST['backup_nonce']) || !wp_verify_nonce($_POST['backup_nonce'], 'backup_database_action')) {
      return "Security check failed - unauthorized request";
    }
  }

  global $wpdb;
  $messages = [];

  $backupDirPath = ABSPATH . '../gicinema_dbs';

  // Create the directory if it doesn't exist
  if (!file_exists($backupDirPath)) {
    if (!mkdir($backupDirPath, 0700, true)) {
      $messages[] = "✗ Failed to create backup directory";
      return implode("<br>", $messages);
    }
    $messages[] = "✓ Created backup directory";
  }

  // Advanced cleanup with retention policy
  $deleted = cleanup_old_backups($backupDirPath);
  if ($deleted > 0) {
    $messages[] = "✓ Cleaned up {$deleted} old backup(s)";
  }

  // Generate backup file path
  $backupFilePath = $backupDirPath . '/gicinema-db--' . date('Y-m-d--H-i-s') . '.sql.gz';

  // Create the actual backup
  $result = create_database_backup($backupFilePath, $wpdb);

  if ($result['success']) {
    $messages[] = "✓ Backup created: " . number_format(filesize($backupFilePath)) . " bytes";
  } else {
    $messages[] = "✗ Backup failed: " . $result['error'];
  }

  return implode("<br>", $messages);
}

function cleanup_old_backups($backupDirPath) {
  $now = time();
  $deletedCount = 0;

  // Get all backup files
  $files = glob($backupDirPath . '/*.{sql,sql.gz}', GLOB_BRACE);
  if (empty($files)) return 0;

  // Parse backup files
  $allBackups = [];
  foreach ($files as $file) {
    if (preg_match('/gicinema-db--(\d{4}-\d{2}-\d{2})--\d{2}-\d{2}-\d{2}\.sql/', basename($file), $matches)) {
      $date = $matches[1];
      $timestamp = strtotime($date);

      $allBackups[] = [
        'file' => $file,
        'timestamp' => $timestamp,
        'date' => $date,
        'age_days' => ($now - $timestamp) / (24 * 60 * 60)
      ];
    }
  }

  // Track what we're keeping
  $keepFiles = [];

  // STEP 1: Keep ALL backups less than 7 days old
  foreach ($allBackups as $backup) {
    if ($backup['age_days'] < 7) {
      $keepFiles[] = $backup['file'];
    }
  }

  // STEP 2: For older backups, apply retention rules
  // Group older backups by date for easier processing
  $olderBackups = [];
  foreach ($allBackups as $backup) {
    if ($backup['age_days'] >= 7) {
      if (!isset($olderBackups[$backup['date']])) {
        $olderBackups[$backup['date']] = [];
      }
      $olderBackups[$backup['date']][] = $backup;
    }
  }

  // Sort by date
  ksort($olderBackups);

  // Find first backup of each year (keep forever)
  $yearsSeen = [];
  foreach ($olderBackups as $date => $dayBackups) {
    $year = date('Y', strtotime($date));
    if (!isset($yearsSeen[$year])) {
      $yearsSeen[$year] = true;
      $keepFiles[] = $dayBackups[0]['file']; // Keep first backup of this day
    }
  }

  // Find first backup of each month (keep for 1 year)
  $monthsSeen = [];
  foreach ($olderBackups as $date => $dayBackups) {
    $monthKey = date('Y-m', strtotime($date));
    $backup = $dayBackups[0];

    // Only apply monthly retention to backups 7+ days old but less than 1 year old
    if ($backup['age_days'] >= 7 && $backup['age_days'] < 365) {
      if (!isset($monthsSeen[$monthKey])) {
        $monthsSeen[$monthKey] = true;
        $keepFiles[] = $backup['file'];
      }
    }
  }

  // Find first backup of each week (keep for 1 month)
  $weeksSeen = [];
  foreach ($olderBackups as $date => $dayBackups) {
    $timestamp = strtotime($date);
    $weekKey = date('Y-W', $timestamp);
    $backup = $dayBackups[0];

    // Only apply weekly retention to backups 7+ days old but less than 1 month old
    if ($backup['age_days'] >= 7 && $backup['age_days'] < 30) {
      if (!isset($weeksSeen[$weekKey])) {
        $weeksSeen[$weekKey] = true;
        $keepFiles[] = $backup['file'];
      }
    }
  }

  // Remove duplicates
  $keepFiles = array_unique($keepFiles);

  // Delete everything not in the keep list
  foreach ($files as $file) {
    if (!in_array($file, $keepFiles)) {
      if (unlink($file)) {
        $deletedCount++;
      }
    }
  }

  return $deletedCount;
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
