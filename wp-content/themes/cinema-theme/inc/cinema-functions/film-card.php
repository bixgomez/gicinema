<?php
require_once get_template_directory() . '/inc/cinema-functions/validate-date.php';

function filmCard($filmPostId) {
  $args = array(
    'posts_per_page' => 1,
    'post_type' => 'film',
    'p' => $filmPostId
  );
  $getFilm = new WP_Query( $args );
  if ($getFilm->have_posts()) :
    while ($getFilm->have_posts()) :
      $getFilm->the_post();
      $link = get_permalink($filmPostId);
      $shortName = get_field('short_name');
      $displayName = strlen($shortName) ? $shortName : get_the_title();
      $country = get_field('country');
      $director = get_field('film_director');
      $format = get_field('format');
      $length = get_field('film_length');
      $screenings = get_field('film_screenings');
      $trailer = get_field('trailer_url');
      $ticketPurchaseLink = get_field('ticket_purchase_link');
      $year = get_field('film_year');
      $description = get_field('description');
      $description = wpautop($description, false);
      ?>        
      <div class="film-teaser">
        <div class="film-teaser--sidebar">
          <div class="film-teaser--poster">
            <?php
            if (has_post_thumbnail($filmPostId)) {
              echo get_the_post_thumbnail($post_id);
            } else {
              $poster = get_field('poster_url', $post_id);
              if ($poster) {
                echo '<div class="film-poster"><img src="' . $poster . '"></div>';
              }
            }
            ?>
          </div>
          <div class="film-teaser--links">
            <div class="film-teaser--trailer">
              <a class="film-trailer" href="https://youtu.be/<?php echo $trailer; ?>" target="_blank">View Trailer</a>
            </div>
            <div class="film-teaser--buy-tickets">
              <a class="film-trailer" href="<?php echo $ticketPurchaseLink; ?>" target="_blank">Buy Tickets</a>
            </div>
          </div>
        </div>
        <h2 class="film-teaser--title">
          <a class="film-title" href="<?php echo $link; ?>"><?php echo $displayName; ?></a>
        </h2>
        <div class="film-teaser--film-info">
          <div class="film-teaser--director">
            <?php echo $director; ?> · <?php echo $year; ?>
          </div>
          <div class="film-teaser--format">
            <?php echo $length . 'min'; ?> · <?php echo $format; ?>
          </div>
          <div class="film-teaser--screening-range">
            Playing Mar 2
          </div>
        </div>
        <div class="film-teaser--description">
          <p>
            <?php echo $description; ?>
          </p>
        </div>
        <div class="film-teaser--screenings">
          <div class="screenings">
            <p><?php echo $screenings; ?></p>
          </div>
        </div>
      </div>
      <?php
    endwhile;
  endif;
}
