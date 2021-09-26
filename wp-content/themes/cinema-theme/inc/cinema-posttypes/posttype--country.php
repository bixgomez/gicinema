<?php

add_action( 'init', 'country_cpt' );

function country_cpt() {
  register_post_type( 'country', array(
    'labels' => array(
      'name' => 'Countries',
      'singular_name' => 'Country',
      'all_items'    => __( 'All Countries', 'text_domain' ),
      'add_new'      => __( 'Add New Country', 'text_domain' ),
      'add_new_item' => __( 'Add New Country', 'text_domain' ),
      'edit_item' => __( 'Edit Country', 'text_domain' ),
    ),
    'description' => 'Countries that made the films you will screen.',
    'public' => true,
    'show_in_rest' => false,
    'menu_position' => 20,
    'menu_icon' => 'dashicons-location',
    'supports' => array( 'title' )
  ));
}
