<?php

add_action( 'init', 'series_cpt' );

function series_cpt() {
  register_post_type( 'series', array(
    'labels' => array(
      'name' => 'Film Series',
      'singular_name' => 'Series',
      'all_items'    => __( 'All Series', 'text_domain' ),
      'add_new'      => __( 'Add New Series', 'text_domain' ),
      'add_new_item' => __( 'Add New Series', 'text_domain' ),
      'edit_item' => __( 'Edit Series', 'text_domain' ),
    ),
    'description' => 'Allowing you to group films into a series.',
    'public' => true,
    'show_in_rest' => true,
    'menu_position' => 20,
    'menu_icon' => 'dashicons-tickets-alt',
    'supports' => array( 'title', 'editor', 'custom-fields' )
  ));
}
