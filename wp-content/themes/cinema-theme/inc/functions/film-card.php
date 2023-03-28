<?php
require_once get_template_directory() . '/inc/functions/validate-date.php';

function filmCard($filmPostId) {
  $filmCardArgs = array(
    'posts_per_page' => 1,
    'post_type' => 'film',
    'p' => $filmPostId
  );
  $filmCardQuery = new WP_Query( $filmCardArgs );
  if ($filmCardQuery->have_posts()) :
    while ($filmCardQuery->have_posts()) :
      $filmCardQuery->the_post();
      $link = get_permalink($filmPostId);
      $shortName = get_field('short_name', $filmPostId);
      $displayName = strlen($shortName) ? $shortName : get_the_title();
      $country = get_field('country', $filmPostId);
      $director = get_field('film_director', $filmPostId);
      $format = get_field('format', $filmPostId);
      $length = get_field('film_length', $filmPostId);
      $screenings = get_field('film_screenings', $filmPostId);
      $firstScreening = get_field('screening_first', $filmPostId);
      $lastScreening = get_field('screening_last', $filmPostId);
      $trailer = get_field('trailer_url', $filmPostId);
      $ticketPurchaseLink = get_field('ticket_purchase_link', $filmPostId);
      $year = get_field('film_year', $filmPostId);
      $description = get_field('description', $filmPostId);
      $description = wpautop($description, false);
      $addlInfo = get_field('additional_info', $filmPostId);
      $addlInfo = wpautop($addlInfo, false);
      ?>
      <div class="film-teaser">
        <div class="film-teaser--sidebar">
          <div class="film-teaser--poster">
            <?php
            if (has_post_thumbnail($filmPostId)) {
              echo get_the_post_thumbnail($filmPostId);
            } else {
              $poster = get_field('poster_url', $filmPostId);
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
            <?php
                $firstScreeningDisp = date('M j', strtotime($firstScreening));
                $lastScreeningDisp = date('M j', strtotime($lastScreening));
                echo 'Playing ' . $firstScreeningDisp;
                if ($lastScreeningDisp != $firstScreeningDisp) {
                    echo ' through ' . $lastScreeningDisp;
                }
                ?>
          </div>
        </div>
        <div class="film-teaser--description">
          <p>
            <?php 
              echo $description;
              echo $addlInfo;
            ?>
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
