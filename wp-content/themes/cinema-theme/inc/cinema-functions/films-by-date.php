<?php
require get_template_directory() . '/inc/cinema-functions/get-film.php';
require_once get_template_directory() . '/inc/cinema-functions/validate-date.php';

function filmsByDate($date) {
	if ($date) :
		if (validateDate($date)) :
			// echo $date;
			echo getFilmsforDate($date);
		else : return null; endif;
	else : return null; endif;
}

function getFilmsforDate($date) {
	global $wpdb;
	$screenings_table_name = $wpdb->prefix . 'gi_screenings';
	$result = $wpdb->get_results("SELECT film_id, screening_date FROM $screenings_table_name WHERE screening_date = '$date' ORDER BY screening_time");
	if ($result) :
		$resultsList = array();
		foreach ($result as $row) :
			if ( !in_array($row->film_id, $resultsList) ) {
				getFilm(
					$film_id = $row->film_id,
					$format = "title_showtimes",
					$date = $date
				);
			}
			$resultList = array_push($resultsList, $row->film_id);
		endforeach;
	else:
		return null;
	endif;
}
