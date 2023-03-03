<?php
require get_template_directory() . '/inc/cinema-functions/get-film.php';

function filmsByDate($date) {
	if ($date) :
		if (validateDate($date)) :
			echo $date . '<br/>';
			echo getFilmsforDate($date) . '<br/>';
		else : return null; endif;
	else : return null; endif;
}

function getFilmsforDate($date) {
	global $wpdb;
	$screenings_table_name = $wpdb->prefix . 'gi_screenings';

	// echo $screenings_table_name . '<br/>';
	// echo $date . '<br/>';

	$result = $wpdb->get_results("SELECT film_id, screening_date FROM $screenings_table_name WHERE screening_date = '$date' ORDER BY screening_time");
	if ($result) :
		foreach ($result as $row) :
			getFilm($row->film_id);
		endforeach;
	else:
		return null;
	endif;
}

function validateDate($date, $format = 'Y-m-d') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
