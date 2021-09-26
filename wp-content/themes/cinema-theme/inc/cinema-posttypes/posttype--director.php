<?php

add_action( 'init', 'director_cpt' );

function director_cpt() {
  register_post_type( 'director', array(
    'labels' => array(
      'name' => 'Directors',
      'singular_name' => 'Director',
      'all_items'    => __( 'All Directors', 'text_domain' ),
      'add_new'      => __( 'Add New Director', 'text_domain' ),
      'add_new_item' => __( 'Add New Director', 'text_domain' ),
      'edit_item' => __( 'Edit Director', 'text_domain' ),
    ),
    'description' => 'Directors that made the films you will screen.',
    'public' => true,
    'show_in_rest' => false,
    'menu_position' => 20,
    'menu_icon' => 'dashicons-groups',
    'supports' => array( 'editor', 'thumbnail' )
  ));
}

/**
* Save post metadata when a post is saved.
*
* @param int $post_id The post ID.
* @param post $post The post object.
* @param bool $update Whether this is an existing post being updated or not.
*/

function save_director_stuff( $post_id ) {

  if ($_POST['post_type'] != 'director') {
    return;
  }

  $directorFirstName = get_field('director__first_name', $post_id );
  $directorLastName = get_field('director__last_name', $post_id );

  // print_r($directorFirstName);
  // print_r($directorLastName);

  // echo $post_title;
  // echo $post_url;

  $title = $directorFirstName . ' ' . $directorLastName;

  $data = array(
    'ID'         => $post_id,
    'post_title' => $title,
    'post_name'  => sanitize_title( $title ),
  );

  wp_update_post( $data );

}

add_action( 'acf/save_post', 'save_director_stuff', 1 );
add_action( 'acf/save_post', 'save_director_stuff', 10 );
add_action( 'acf/save_post', 'save_director_stuff', 20 );
