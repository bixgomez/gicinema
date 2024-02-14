<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

// Add action hooks
add_action('save_post', 'gicinema__check_and_run_update_film_on_save', 10, 3);
add_action('admin_notices', 'display_film_saved_admin_notice', 100);

function gicinema__check_and_run_update_film_on_save($post_id, $post, $update) {
    // Check if this is a 'film' post type
    if (get_post_type($post_id) !== 'film') {
        return;
    }

    // Optionally, prevent running on auto-drafts (if required)
    if ($post->post_status == 'auto-draft') {
        return;
    }

    // Now, call your function
    gicinema__update_film_on_save($post_id);
}

function display_film_saved_admin_notice() {
  // Check if our transient is set, and display the error message
  if ($notice = get_transient('film_saved_admin_notice')) {
      echo "<div class='notice notice-success is-dismissible'><p>{$notice}</p></div>";
      delete_transient('film_saved_admin_notice');
  }
}

function gicinema__update_film_on_save($post_id) {

  // Check if this is an ACF save (to prevent infinite loop)
  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if(isset($_POST['_acf_post_id']) && $_POST['_acf_post_id'] == 'new_post') return;
  if(!isset($_POST['acf'])) return;

  // Ensure the function runs only for custom post type 'film'
  if(get_post_type($post_id) !== 'film') return;

  // Retrieve the ACF fields
  $agile_film_id = get_field('agile_film_id', $post_id);
  $screenings = get_field('screenings', $post_id);

  // Assemble the message to display on save.
  $saved_message = 'The film ' . $post_id . ' (Agile ID ' . $agile_film_id . ') has been successfully saved.';

  // If you need to display something about the screenings, you can iterate over them if it's a repeater field
  if($screenings) {
    foreach($screenings as $row) {
      $screening = $row['screening'];

      // Splitting screening into separate date and time strings
      list($screening_date, $screening_time, $ampm) = explode(" ", $screening);
      $screening_time = $screening_time . ' ' . $ampm;

      $saved_message .= '<br>' . $screening . ' - ' . $screening_date . ' - ' . $screening_time;
    }
  }

  // Display saved message when we return to the admin screen.
  set_transient('film_saved_admin_notice', $saved_message, 60);

}
