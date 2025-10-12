<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__truncate_screenings_table.php";

if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
  // Submenu registration is centralized in inc/admin-nav.php
  function gicinema_page_display__truncate_screenings_table() {
    echo '<div class="wrap wrap--gicinema">';

    // Check if the form was submitted (render notice immediately after nav)
    if (isset($_POST['confirm_truncation']) && $_POST['confirm_truncation'] == 'yes') {
      $result = gicinema__truncate_screenings_table();
      echo "<div class='notice notice-success'><p>{$result}</p></div>";
    }

    // Always render page info after nav (and after any notice)
    gicinema_render_page_info('gicinema--truncate-screenings-table');

    // If not submitted, show confirmation form
    if (!isset($_POST['confirm_truncation']) || $_POST['confirm_truncation'] != 'yes') {
      // Display warning and confirmation form
?>

      <div class="warning">
        <p><strong>Warning:</strong> This will permanently truncate the custom screenings table. It cannot be undone. Film posts remain; all screenings rows will be removed.</p>
      </div>
      <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'gi_screenings';
        $approx_rows = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table_name`");
      ?>
      <form method="post">
        <?php wp_nonce_field('truncate_table_action', 'truncate_nonce'); ?>
        <input type="hidden" name="confirm_truncation" value="yes">
        <input type="submit" id="gicinema-truncate-table-btn" class="button button-primary" value="Confirm Truncation">
      </form>
      <script>
        (function(){
          var btn = document.getElementById('gicinema-truncate-table-btn');
          if (btn) {
            btn.addEventListener('click', function(ev){
              var rows = <?php echo (int) $approx_rows; ?>;
              var msg = 'This will remove ' + rows + ' row' + (rows===1?'':'s') + ' from the custom screenings table (<?php echo esc_js($table_name); ?>).\n\n'
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
    }

    echo '</div>';
  }
}
