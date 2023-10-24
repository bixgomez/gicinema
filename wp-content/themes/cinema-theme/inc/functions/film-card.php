<?php
require_once get_template_directory() . '/inc/functions/validate-date.php';

function filmCard($filmPostId, $classes='film') {
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
      <div class="film-card <?php echo $classes; ?>">
        
        <h2 class="film-card--title">
          <a class="film-title" href="<?php echo $link; ?>"><?php echo $displayName; ?></a>
        </h2>

        <div class="film-card--film-info">

          <?php
          // Setting director and year to null if either is "Various".
          // Yes, this undermines the code in the next block, but so be it for now.
          $director = ( $director == "Various" ) ? null : $director;
          $year = ( $year == "Various" ) ? null : $year;
          ?>
          
          <?php if (strlen($director) || strlen($year)) : ?>
            <div>
              <?php 
              echo $director;
              echo ( $director == "Various" ) ? ' directors' : null;
              echo (strlen($director) && strlen($year)) ? ' · ' : null;
              echo $year;
              echo ( $year == "Various" ) ? ' years' : null;
              echo null;
              ?>
            </div>
          <?php endif ?>
          
          <?php if (strlen($length) || strlen($format)) : ?>
            <div>
              <?php 
              echo (strlen($length)) ? $length . 'min' : null;
              echo (strlen($length) && strlen($format)) ? ' · ' : null;
              echo $format; 
              ?>
            </div>
          <?php endif ?>
          
          <?php 
          $firstScreeningDisp = date('M j', strtotime($firstScreening));
          $lastScreeningDisp = date('M j', strtotime($lastScreening));
          ?>

          <?php if (strlen($firstScreeningDisp) || strlen($lastScreeningDisp)) : ?>
            <div class="film-card--screening-range">
              <?php
              echo 'Playing ' . $firstScreeningDisp;
              if ($lastScreeningDisp != $firstScreeningDisp) {
                  echo ' through ' . $lastScreeningDisp;
              }
              ?>
            </div>
          <?php endif ?>

        </div>

        <div class="film-card--sidebar">
          <div class="film-card--poster">
            <a class="film-title" href="<?php echo $link; ?>">
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
            </a>
          </div>

          <div class="film-card--links">

            <?php if ($trailer != '') : ?>
              <?php 
              if (!str_contains($trailer, 'http')) :
                $trailer = 'https://youtu.be/' . $trailer . '';
              endif;
              ?>
              <div class="film-card--trailer">
                <a class="film-button" href="<?php echo $trailer; ?>" target="_blank"><span>View </span>Trailer</a>
              </div>
            <?php endif ?>

            <?php if ($ticketPurchaseLink != '') : ?>
              <div class="film-card--buy-tickets">
                <a class="film-button" href="<?php echo $ticketPurchaseLink; ?>" target="_blank"><span>Buy </span>Tickets</a>
              </div>
            <?php endif ?>
          </div>
        </div>

        <?php if ( strlen($screenings) ) : ?>
          <div class="film-card--screenings">
            <div class="screenings">
              <p><?php echo $screenings; ?></p>
            </div>
          </div>
        <?php endif ?>

        <div class="film-card--description">
            <?php 
              echo $description;
              echo $addlInfo;
            ?>
        </div>
      </div>
      <?php
    endwhile;
  endif;
}
