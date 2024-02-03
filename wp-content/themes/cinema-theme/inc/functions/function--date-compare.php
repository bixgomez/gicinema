<?php

// Define a comparison function for dates
function date_compare($a, $b) {
  $dateTimeA = new DateTime($a);
  $dateTimeB = new DateTime($b);
  return $dateTimeA <=> $dateTimeB;
}
