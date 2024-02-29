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
    'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
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
