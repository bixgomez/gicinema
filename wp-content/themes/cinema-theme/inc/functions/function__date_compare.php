<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Define a comparison function for dates
function date_compare($a, $b) {
  try {
      $dateTimeA = new DateTime($a);
  } catch (Exception $e) {
      // Handle the error for $a, for example by logging or setting $dateTimeA to null
      // echo "Error parsing date string: " . $e->getMessage();
      return -1; // or some other value indicating an error
  }

  try {
      $dateTimeB = new DateTime($b);
  } catch (Exception $e) {
      // Handle the error for $b
      // echo "Error parsing date string: " . $e->getMessage();
      return 1; // or some other value indicating an error
  }

  return $dateTimeA <=> $dateTimeB;
}
