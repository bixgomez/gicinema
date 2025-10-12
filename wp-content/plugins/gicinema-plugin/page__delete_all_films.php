<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_all_films.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
  // Submenu registration is centralized in inc/admin-nav.php
  function gicinema_page_display__delete_all_films() {
    echo '<div class="wrap wrap--gicinema">';

    // Check if the form was submitted (render notice immediately after nav)
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
      $result = delete_all_film_posts();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
    }

    // Always render page info after nav (and after any notice)
    gicinema_render_page_info('gicinema--delete-all-films');

    // If not submitted, render the confirmation form
    if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] != 'yes') {
      // Display warning and confirmation form
?>

      <div class="warning">
        <p><strong>Warning:</strong> This action will permanently delete all Film posts. It cannot be undone. Make a fresh backup before proceeding.</p>
      </div>
      <?php
        // Estimate how many Film posts will be deleted
        $approx_total = 0;
        $approx_query = new WP_Query([
          'post_type'      => 'film',
          'posts_per_page' => -1,
          'fields'         => 'ids',
        ]);
        $approx_total = is_array($approx_query->posts) ? count($approx_query->posts) : 0;
      ?>
      <form method="post">
        <?php wp_nonce_field('delete_all_films_action', 'delete_films_nonce'); ?>
        <input type="hidden" name="confirm_delete" value="yes">
        <input type="submit" id="gicinema-delete-all-films-btn" class="button button-primary" value="Confirm Deletion">
      </form>
      <script>
        (function(){
          var btn = document.getElementById('gicinema-delete-all-films-btn');
          if (btn) {
            btn.addEventListener('click', function(ev){
              var total = <?php echo (int) $approx_total; ?>;
              var msg = 'This will permanently delete ' + total + ' Film post' + (total===1?'':'s') + ' and related metadata.\n\n'
                      + 'This cannot be undone. Ensure you have a recent backup.\n\n'
                      + 'Are you absolutely sure you want to proceed?';
              if (!window.confirm(msg)) {
                ev.preventDefault();
              }
            });
          }
        })();
      </script>
<?php
    } // end if not submitted

    echo '</div>';
  }
}
