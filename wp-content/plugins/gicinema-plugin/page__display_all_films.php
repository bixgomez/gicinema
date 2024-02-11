<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema_page_add__display_all_films() {
  // Main menu page is added here

  // Add sub-menu page
  add_submenu_page(
      'gicinema--admin', // The slug name for the parent menu
      'Display All Films', // The text to be displayed in the title tags of the page when the menu is selected
      'Display All Films', // The text to be used for the menu
      'manage_options', // The capability required for this menu to be displayed to the user
      'gicinema--display-all-films', // The slug name to refer to this submenu by (should be unique for this submenu)
      'gicinema_page_display__all_films' // The function to be called to output the content for this page
  );
}
add_action('admin_menu', 'gicinema_page_add__display_all_films');

function gicinema_page_display__all_films() {

  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>All Films</h2>';

  // Arguments for the query
  $args = array(
      'post_type' => 'film', // Your custom post type name
      'posts_per_page' => -1, // Retrieve all posts
      'orderby' => 'date', // Order by date
      'order' => 'DESC' // Descending order
  );
  
  // The Query
  $the_query = new WP_Query($args);
  
  // Check if the Query returns any posts
  if ($the_query->have_posts()) {
  
      // Start the unordered list
      echo '<table class="table--films">';
  
      // The Loop
      while ($the_query->have_posts()) {
        echo '<tr class="tr--film">';

          $the_query->the_post();
          // Get the permalink of the current post
          $post_link = get_permalink();
          // Display the title of the post

          echo '<td>' . get_the_ID() . '</td>';
          echo '<td><a href="' . esc_url($post_link) . '" target="_blank">VIEW</a></td>';
          edit_post_link('EDIT', '<td>', '</span>', null, 'edit-post-link-class');
          echo '<td>' . get_the_date('Y-m-d') . '</td>';
          echo '<td>' . get_the_title() . '</td>';

        echo '</tr>';
      }
  
      // End the unordered list
      echo '</ul>';
  
      /* Restore original Post Data 
       * NB: Because we are using new WP_Query we aren't stomping on the 
       * original $wp_query and it does not need to be reset with 
       * wp_reset_query(). We just need to reset the post data with 
       * wp_reset_postdata().
       */
      wp_reset_postdata();
  } else {
      // No posts found
      echo '<p>No films found.</p>';
  }

  echo '</table>';
  echo '</div>';
}
