<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once get_template_directory() . '/inc/functions/function__validate_date.php';

function filmsByDate($date) {
	if ($date) :
		if (validateDate($date)) :
			global $wpdb;
			$screenings_table_name = $wpdb->prefix . 'gi_screenings';
			$result = $wpdb->get_results("SELECT distinct post_id FROM $screenings_table_name WHERE screening_date = '$date' AND status = 1 ORDER BY screening_time");
			if ($result) :
				$post_ids = array();
				foreach ($result as $key=>$row) :
					$post_ids[] = $row->post_id;
					if ( count($post_ids) === count($result) ) :
						return $post_ids;
					endif;
				endforeach;
    	else: return null; endif;
		else : return null; endif;
	else : return null; endif;
}
