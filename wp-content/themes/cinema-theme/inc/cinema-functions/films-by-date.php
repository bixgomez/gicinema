<?php
require_once get_template_directory() . '/inc/cinema-functions/validate-date.php';

function filmsByDate($date) {
	if ($date) :
		if (validateDate($date)) :
			global $wpdb;
			$screenings_table_name = $wpdb->prefix . 'gi_screenings';
			$result = $wpdb->get_results("SELECT film_id FROM $screenings_table_name WHERE screening_date = '$date' ORDER BY screening_time");
			if ($result) :
				$filmIds = array();
				foreach ($result as $key=>$row) :
					$filmIds[] = $row->film_id;
					if ( count($filmIds) === count($result) ) :
						return $filmIds;
					endif;
				endforeach;
    	else: return null; endif;
		else : return null; endif;
	else : return null; endif;
}
