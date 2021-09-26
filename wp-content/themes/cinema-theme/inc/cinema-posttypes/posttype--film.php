<?php

add_action( 'init', 'film_cpt', 0 );

function film_cpt() {

  $labels = array(
    'name'                  => _x( 'Films', 'Post Type General Name', 'text_domain' ),
    'singular_name'         => _x( 'Film', 'Post Type Singular Name', 'text_domain' ),
    'all_items'             => __( 'All Films', 'text_domain' ),
    'add_new'               => __( 'Add New Film', 'text_domain' ),
    'add_new_item'          => __( 'Add New Film', 'text_domain' ),
    'edit_item'             => __( 'Edit Film', 'text_domain' ),
    'menu_name'             => __( 'Films', 'text_domain' ),
    'name_admin_bar'        => __( 'Film', 'text_domain' ),
    'archives'              => __( 'Film Archives', 'text_domain' ),
    'attributes'            => __( 'Film Attributes', 'text_domain' ),
    'parent_item_colon'     => __( 'Parent Film:', 'text_domain' ),
    'new_item'              => __( 'New Film', 'text_domain' ),
    'update_item'           => __( 'Update Film', 'text_domain' ),
    'view_item'             => __( 'View Film', 'text_domain' ),
    'search_items'          => __( 'Search Film', 'text_domain' ),
    'not_found'             => __( 'Film not found', 'text_domain' ),
    'not_found_in_trash'    => __( 'Film not found in Trash', 'text_domain' ),
    'featured_image'        => __( 'Featured Image', 'text_domain' ),
    'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
    'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
    'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
    'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this film', 'text_domain' ),
    'items_list'            => __( 'Films list', 'text_domain' ),
    'items_list_navigation' => __( 'Films list navigation', 'text_domain' ),
    'filter_items_list'     => __( 'Filter films list', 'text_domain' ),
  );

  $rewrite = array(
    'slug'                  => 'film',
    'with_front'            => true,
    'pages'                 => false,
    'feeds'                 => true,
  );

  $args = array(
    'label'                 => __( 'Film', 'text_domain' ),
    'description'           => __( 'Any film that you will screen at your cinema.', 'text_domain' ),
    'labels'                => $labels,
    'supports'              => array( 'title', 'editor', 'custom-fields' ),
    'hierarchical'          => false,
    'public'                => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 20,
    'menu_icon'             => 'dashicons-editor-video',
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => true,
    'can_export'            => true,
    'has_archive'           => true,
    'exclude_from_search'   => false,
    'publicly_queryable'    => true,
    'rewrite'               => $rewrite,
    'capability_type'       => 'post',
  );

  register_post_type( 'film', $args );
}

/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */

// Upon creating or saving a film, update screenings fields.
function save_film_stuff( $post_id )
{

  // Set the default timezone (TODO: create a global setting for this.)
	// NOTE: Never, ever do this!
	// https://github.com/elementor/elementor/issues/9609
  // date_default_timezone_set('America/Los_Angeles');

  $ourTimeZone = 'America/Los_Angeles';

  // TODO: Make debug work here.
  $debug = 1;

  if ($debug) {
    error_log('-------------------------');
    error_log('DEBUG OUTPUT');
    error_log('-------------------------');
  }

  if ($_POST['post_type'] != 'film') {
    return;
  }

  $visibility_based_on = get_field('visibility_based_on', $post_id);

  if ($visibility_based_on == 'screenings') :

    if ($debug) {
      error_log("visibility_based_on screenings");
      error_log('-------------------------');
    }

    $screenings = get_field('screenings', $post_id);

    if ($screenings) :

      // Initiate timestamp arrays.
      $screenings_timestamps = array();
      $display_date_timestamps = array();

      foreach ($screenings as $key => $thisScreening) {
        $thisScreeningDate = $thisScreening['screening_date'];
        $thisScreeningTime = $thisScreening['screening_time'];
        $thisScreeningDateTime = $thisScreeningDate . ' ' . $thisScreeningTime;

        if ($debug) { error_log("thisScreeningDateTime = $thisScreeningDateTime"); }

        // Set screening date ONLY timestamp (with timezone)
        $this_display_date_timestamp = strtotime($thisScreeningDate . ' ' . $ourTimeZone);

        // Set screening date AND TIME timestamp (with timezone)
        $this_screening_timestamp = strtotime($thisScreeningDateTime . ' ' . $ourTimeZone);

        if ($debug) {
          error_log("this_display_date_timestamp = $this_display_date_timestamp");
          error_log("this_screening_timestamp = $this_screening_timestamp");
        }

        // Add full screening date/time timestamp to array.
        array_push($screenings_timestamps, $this_screening_timestamp);

        // Add DAY ONLY timestamp to array.
        if (!in_array($this_display_date_timestamp, $display_date_timestamps)) {
          array_push($display_date_timestamps, $this_display_date_timestamp);
        }

        if ($debug) { error_log(''); }
      }

      if ($debug) { error_log('-------------------------'); }

      // Sort the screening timestamps in default (ascending) order.
      sort($screenings_timestamps);
      sort($display_date_timestamps);

      // Convert arrays to lists
      $screenings_timestamps_csv = implode(",", $screenings_timestamps);
      $display_date_timestamps_csv = implode(",", $display_date_timestamps);

      $last_element = (count($screenings_timestamps) - 1);
      $this_first_screening = $screenings_timestamps[0];
      $this_last_screening = $screenings_timestamps[$last_element];

      // Save first screening, last screening, and all screenings as custom field values.
      update_post_meta($post_id, 'first_screening', $this_first_screening);
      update_post_meta($post_id, 'last_screening', $this_last_screening);
      update_post_meta($post_id, 'all_screenings', $display_date_timestamps_csv);

      foreach ($screenings as $key => $thisScreening) {
        $this_row = $key + 1;
        $this_screening = $screenings_timestamps[$key];

        if ($debug) {
          error_log("this_screening (timestamp) = $this_screening");
        }

        $this_screening_date_and_time = date("Y-m-d H:i:s", $this_screening);
        if ($debug) {
          error_log("this_screening_date_and_time (UTC) = $this_screening_date_and_time");
        }

        $datetime = new DateTime($this_screening_date_and_time);

        $this_screening_date = $datetime->setTimezone(new DateTimeZone($ourTimeZone))->format('Ymd');
        $this_screening_time = $datetime->setTimezone(new DateTimeZone($ourTimeZone))->format('H:i:s');

        delete_sub_row(array('screenings', $this_row, 'screening_date'), 1);
        delete_sub_row(array('screenings', $this_row, 'screening_time'), 1);

        if ($debug) {
          error_log("this_screening_date = $this_screening_date");
          error_log("this_screening_time = $this_screening_time");
        }

        update_sub_field(array('screenings', $this_row, 'screening_date'), $this_screening_date);
        update_sub_field(array('screenings', $this_row, 'screening_time'), $this_screening_time);
      }

    endif;

  elseif ($visibility_based_on == 'date_from_to') :

    if ($debug) {
      error_log("visibility_based_on date_from_to");
    }

    // Convert display from/to dates to linux timestamps.
    $this_display_from = strtotime(get_field('display_from', $post_id) . ' ' . $ourTimeZone);
    $this_display_to = strtotime(get_field('display_to', $post_id) . ' ' . $ourTimeZone);

    // Initiate an array of display dates.
    $display_dates_array = array($this_display_from);
    $i = $this_display_from;
    while ($i < $this_display_to):
      $i = strtotime('+1 day', $i);
      array_push($display_dates_array, $i);
    endwhile;

    // Convert array to simple comma-separated list.
    $display_dates_list = implode(',', $display_dates_array);

    // Save first, last, and all display dates as custom field values.
    update_post_meta($post_id, 'first_screening', $this_display_from);
    update_post_meta($post_id, 'last_screening', $this_display_to);
    update_post_meta($post_id, 'all_screenings', $display_dates_list);

  endif;

}

add_action( 'acf/save_post', 'save_film_stuff', 20 );
