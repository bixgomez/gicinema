<?php

add_action( 'init', 'format_cpt' );

function format_cpt() {
  register_post_type( 'format', array(
    'labels' => array(
      'name' => 'Formats',
      'singular_name' => 'Format',
      'all_items'    => __( 'All Formats', 'text_domain' ),
      'add_new'      => __( 'Add New Format', 'text_domain' ),
      'add_new_item' => __( 'Add New Format', 'text_domain' ),
      'edit_item' => __( 'Edit Format', 'text_domain' ),
    ),
    'description' => 'Film/video formats.',
    'public' => true,
    'show_in_rest' => true,
    'menu_position' => 20,
    'menu_icon' => 'dashicons-media-video',
    'supports' => array( 'title', 'editor', 'custom-fields' )
  ));
}
