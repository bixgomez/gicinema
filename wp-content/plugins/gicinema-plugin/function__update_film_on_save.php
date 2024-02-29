<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once "function__sync_screenings_on_save.php";

// Add action hooks
add_action('current_screen', 'gicinema__grab_screenings_value');
add_action('save_post', 'gicinema__check_and_run_update_film_on_save', 10, 3);
add_action('admin_notices', 'display_film_saved_admin_notice', 100);



function gicinema__check_and_run_update_film_on_save($post_id, $post, $update) {
    ob_start(); // Start buffering output
    
    // Check if this is a 'film' post type
    if (get_post_type($post_id) !== 'film') {
        return;
    }

    // Optionally, prevent running on auto-drafts (if required)
    if ($post->post_status == 'auto-draft') {
        return;
    }

    // Check if the skip flag is set.
    if (wp_cache_get('skip_gicinema_update')) {
      // Clear the flag.
      wp_cache_delete('skip_gicinema_update');
      return;
    }

    // Now, call the function
    gicinema__update_film_on_save($post_id);

    ob_end_clean(); // Discard the output buffer
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

  // Now, call the sync screenings function
  gicinema__sync_screenings_on_save($post_id, $on_save = true);

  // Assemble the message to display on save.
  $saved_message = 'The film ' . $post_id . ' (Agile ID ' . $agile_film_id . ') has been successfully saved.<br>';

  // Display saved message when we return to the admin screen.
  set_transient('film_saved_admin_notice', $saved_message, 60);
}



function display_film_saved_admin_notice() {
  // Check if our transient is set, and display the error message
  if ($notice = get_transient('film_saved_admin_notice')) {
      echo "<div class='notice notice-success is-dismissible'><p>{$notice}</p></div>";
      delete_transient('film_saved_admin_notice');
  }
}



function gicinema__grab_screenings_value($current_screen) {
  // Check if we are on the post edit screen.
  if ($current_screen->id == 'film' && $current_screen->base == 'post') {  
    if (isset($_GET['post'])) {
      $post_id = $_GET['post'];
      $post = get_post($post_id);
      // Check if the post type is 'film'.
      if ($post->post_type == 'film') {
        // Fetch and save the screenings repeater field value to a transient.
        $screenings_value = get_field('screenings', $post_id);
        set_transient('gicinema__screenings_value_' . $post_id, $screenings_value, 600);
      }
    }
  }
}
