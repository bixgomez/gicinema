<?php

add_action( 'init', 'alert_cpt' );

function alert_cpt() {
  register_post_type( 'alert', array(
    'labels' => array(
      'name' => 'Alerts',
      'singular_name' => 'Alert',
      'all_items'    => __( 'All Alerts', 'text_domain' ),
      'add_new'      => __( 'Add New Alert', 'text_domain' ),
      'add_new_item' => __( 'Add New Alert', 'text_domain' ),
      'edit_item' => __( 'Edit Alert', 'text_domain' ),
    ),
    'description' => 'Special site-wide alerts and messages.',
    'public' => true,
    'show_in_rest' => true,
    'menu_position' => 20,
    'menu_icon' => 'dashicons-megaphone',
    'supports' => array( 'title', 'editor', 'custom-fields' )
  ));
}
