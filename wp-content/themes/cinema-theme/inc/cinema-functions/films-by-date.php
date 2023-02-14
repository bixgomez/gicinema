<?php

function filmsByDate($date) {
	if ($date) :
		if (validateDate($date)) :
			echo getFilmsforDate($date);
		else : return null; endif;
	else : return null; endif;
}

function getFilmsforDate($date) {
	global $wpdb;
	$screenings_table_name = $wpdb->prefix . 'gi_screenings';
	$result = $wpdb->get_results("SELECT film_id FROM $screenings_table_name WHERE screening_date = '$date' ORDER BY screening_time");
	if ($result) :
		foreach ($result as $row) :
			return 'Film ID: ' . $row->film_id . ', ';
		endforeach;
	else:
		return null;
	endif;
	return $date;
}

function validateDate($date, $format = 'Y-m-d') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
