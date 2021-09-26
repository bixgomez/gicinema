<?php
/* Plugin Name: GI Cinema Get Shows
 * Plugin URI:  https://grandillusioncinema.org/
 * Description: Retrieves the most recently added shows..
 * Version:     1.0.0
 * Author:      Richard Gilbert
 * Author URI:  https://grandillusioncinema.org/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

add_shortcode( 'external_data', 'external_data_callback' );

// add_action( 'admin_init', 'admin_callback_function' );

function external_data_callback() {

	$url = 'https://prod5.agileticketing.net/websales/feed.ashx?guid=52c1280f-be14-4579-8ddf-4b3dadbf96c7&showslist=true&withmedia=true&format=json&v=latest';

	$args = array(
    'method' => 'GET'
  );
  $response = wp_remote_get( $url, $args );
  if ( is_wp_error( $response ) ) {
    $error_msg = $response->get_error_message();
		return "Something went wrong: $error_message";
	}

	$results = json_decode( wp_remote_retrieve_body( $response ) );

	foreach( $results->ArrayOfShows as $show ) {
		echo '<div>';
		echo '<b>ID:</b> ' . $show->ID . '<br>';
		echo '<b>Name:</b> ' . $show->Name . '<br>';
		echo '<b>Duration (in minutes):</b> ' . $show->Duration . '<br>';
		echo '<b>Short description:</b> ' . $show->ShortDescription . '<br>';
		echo '<b>Main image:</b> ' . $show->EventImage . '<br>';
		echo '<b>Thumbnail image:</b> ' . $show->ThumbImage . '<br>';
		echo '<hr>';

		foreach( $show->CustomProperties as $customProp ) {
			if ( $customProp->Name == 'Release Year' ) {
				echo '<b>Release Year:</b> ' . $customProp->Value . '<br>';
			}

			if ( $customProp->Name == 'Format' ) {
				echo '<b>Format:</b> ' . $customProp->Value . '<br>';
			}

			if ( $customProp->Name == 'Director' ) {
				echo '<b>Director:</b> ' . $customProp->Value . '<br>';
			}
		}

		foreach( $show->AdditionalMedia as $addlMedia ) {
			if ( $addlMedia->Type == 'YouTube' ) {
				echo '<b>YouTube:</b> ' . $addlMedia->Value . '<br>';
			}
		}

		foreach( $show->CurrentShowings as $currentShowing ) {
			echo '<b>ID:</b> ' . $currentShowing->ID . '<br>';
			echo '<b>StartDate:</b> ' . $currentShowing->StartDate . '<br>';
			echo '<b>EndDate:</b> ' . $currentShowing->EndDate . '<br>';
			echo '<b>DisplayDate:</b> ' . $currentShowing->DisplayDate . '<br>';
			echo '<b>SalesMessage:</b> ' . $currentShowing->SalesMessage . '<br>';
      echo '<b>SalesState:</b> ' . $currentShowing->SalesState . '<br>';
      echo '<b>LegacyPurchaseLink:</b> <a href="' . $currentShowing->LegacyPurchaseLink . '">' . $currentShowing->SalesMessage . '</a><br><br>';
		}

		echo '</div><hr />';
	}
}
