<?php
/**
 * Block Name: Film Teaser
 *
 * Description: Displays a film teaser/card.
 *
 * Resources:
 * https://alphaparticle.com/blog/custom-block-icons-with-acf-blocks/
 * https://joeyfarruggio.com/wordress/register-acf-blocks/
 */

// The block attributes
$block = $args['block'];

// The block data.
$data = $args['data'];

// The block ID.
$block_id = $args['block_id'];

// The block class names.
$class_name = $args['class_name'];

// Set the film id.
if ( $data['film']) {
    $film_id = $data['film'];
}

// Set up query
$film_args = array(
    'post_type' => 'film',
    'p' => $film_id,
);
$film_query = new WP_Query($film_args);

// Display query (for debugging)
// echo '<pre>' . $film_query->request . '</pre>';

// Display current film ID (for debugging)
// echo '<br>$film_id = ' . $film_id;
?>

<?php // If we have any results, display the film teaser card. ?>
<?php if ($film_query->have_posts()): ?>

    <?php
    $this_link = get_permalink($film_id);
    $this_country = get_field('country', $film_id);
    $this_description = get_field('description', $film_id);
    $this_additional_info = get_field('additional_info', $film_id);
    $first_screening = get_field('screening_first', $film_id);
    $last_screening = get_field('screening_last', $film_id);
    $ticket_purchase_link = get_field('ticket_purchase_link', $film_id);
    $this_description = get_field('description', $film_id);
    $this_description = wpautop($this_description, false);
    $this_addl_info = get_field('additional_info', $film_id);
    $this_addl_info = wpautop($this_addl_info, false);
    $this_director = get_field('film_director', $film_id);
    $this_year = get_field('film_year', $film_id);
    $this_country = get_field('country', $film_id);
    $this_length = get_field('film_length', $film_id);
    $this_format = get_field('format', $film_id);
    $this_trailer = get_field('trailer_url', $film_id);
    if( $this_trailer ) {
        $this_trailer_link = '<a class="film-trailer" href="https://youtu.be/' . $this_trailer . '" target="_blank">View Trailer</a>';
    }
    $this_poster = get_the_post_thumbnail($film_id);
    ?>

    <div id="<?php echo $block_id; ?>" class="<?php echo $class_name; ?>">
        <div class="film-teaser--sidebar">
            <div class="film-teaser--poster">
                <?php echo $this_poster; ?>
            </div>
            <div class="film-teaser--links">
                <div class="film-teaser--trailer">
                    <?php echo $this_trailer_link; ?>
                </div>
                <div class="film-teaser--buy-tickets">
                    <a class="film-trailer" href="<?php echo $ticket_purchase_link; ?>">Buy Tickets</a>
                </div>
            </div>
        </div>
        <h2 class="film-teaser--title">
            <a href="<?php echo $this_link; ?>"><?php echo get_the_title($film_id); ?></a>
        </h2>
        <div class="film-teaser--film-info">
            <div class="film-teaser--director">
                <?php
                echo $this_director;
                if ($this_director && $this_year) { echo ' · '; }
                echo $this_year;
                if ($this_country) { echo ' · '; }
                echo $this_country;
                ?>
            </div>
            <div class="film-teaser--format">
                <?php
                echo $this_length;
                if ($this_length && $this_format) { echo ' · '; }
                echo $this_format;
                ?>
            </div>
            <div class="film-teaser--screening-range">
                <?php
                $first_screening_disp = date('M j', strtotime($first_screening));
                $last_screening_disp = date('M j', strtotime($last_screening));
                echo 'Playing ' . $first_screening_disp;
                if ($last_screening_disp != $first_screening_disp) {
                    echo ' through ' . $last_screening_disp;
                }
                ?>
            </div>
        </div>
        <div class="film-teaser--description">
            <?php echo $this_description; ?>
            <?php echo $this_addl_info; ?>
        </div>
        <div class="film-teaser--screenings">
            <?php do_shortcode('[film_showtimes]'); ?>
        </div>
    </div>

<?php endif; ?>