<?php

function getFilm($film_id, $format="title") {

  // echo '<br/>' . $film_id;

  $args = array(
    'posts_per_page' => 1,
    'post_type' => 'film',
    'meta_key' => 'agile_film_id',
    'meta_value' => $film_id
  );
  $get_film_post = new WP_Query( $args );

  if ($get_film_post->have_posts())  {
    // var_dump( $get_film_post->posts );
    while ($get_film_post->have_posts()) {
      $get_film_post->the_post();
      $this_link = get_permalink();
      echo '<a href="' . $this_link . '">' . get_the_title() . '</a><br/>';
    }
  } else {
    echo '<br/>not numeric';
  }
}
