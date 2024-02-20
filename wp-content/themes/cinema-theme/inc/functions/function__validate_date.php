<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function validateDate($date, $format = 'Y-m-d') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
