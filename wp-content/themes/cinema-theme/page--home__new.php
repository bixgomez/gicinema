<?php
/**
 * Template Name: Home Page (new)
 *
 * This is the template that displays the home page.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on this WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Cinema_Theme
 */

require_once get_template_directory() . '/inc/functions/film-card.php';

get_header();
?>

<div class="home-page-content">

    <?php 
    global $post;
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();            
            if ( has_blocks( $post->post_content ) ) {
                echo '<h2 class="section-title">Special Events and Series</h2>'; 
                echo '<div class="blocks blocks--event">'; 
                the_content(); 
                echo '</div>'; 
            }
        } // end while
    } // end if
    wp_reset_query();
    ?>

    <h2 class="section-title">Now Playing</h2>

    <?php 
    // Films showing in the next 7 days, one per line.
    // First, get 7 dates starting with today
    $nowPlayingDays = [];
    $period = new DatePeriod(
        new DateTime(date_i18n("Y-m-d 00:00:00")), // Start date of the period
        new DateInterval('P1D'), // Define the intervals as Periods of 1 Day
        6 // Apply the interval 6 times on top of the starting date
    );
    foreach ($period as $day) {
        $nowPlayingDays[] = '\''.$day->format('Y-m-d').'\'';
    }
    $nowPlayingDaysAsString = implode (',', $nowPlayingDays);
    ?>

    <?php
    // Get all the "now playing" movies that have screenings on those dates
    global $wpdb;
    $screenings_table_name = $wpdb->prefix . 'gi_screenings';
    $nowPlayingScreeningsQuery = "
        SELECT film_id, screening
        FROM {$screenings_table_name} 
        WHERE screening_date IN ($nowPlayingDaysAsString)
        AND status = 1
        ORDER BY screening
    ";

    $result = $wpdb->get_results($nowPlayingScreeningsQuery);
    if ( count($result) ) :
        $nowPlayingFilmIds = [];
        foreach( $result as $key => $row) :
            echo '<span class="debug">' . $row->film_id . ': ' . $row->screening . '<br></span>';
            if (!in_array($row->film_id, $nowPlayingFilmIds)) :
                $nowPlayingFilmIds[] = $row->film_id;
            endif;
        endforeach;

    endif;
    ?>

    <?php 
    // Display their full teasers, in "next screening" order 
    echo '<div class="film-cards film-cards--now-playing">';
    foreach ($nowPlayingFilmIds as $nowPlayingFilmId) :
        $args = array (
            'post_type' => 'film',
            'posts_per_page' => '1',
            'meta_key' => 'agile_film_id',
            'meta_value' => $nowPlayingFilmId
        );
        $getThePostId = new WP_Query( $args );
        if ( $getThePostId->have_posts() ) :
            while ( $getThePostId->have_posts() ) : $getThePostId->the_post();
                $filmPostId = get_the_ID();
                filmCard(filmPostId:$filmPostId, classes:'now-playing');
            endwhile;
        endif;
        wp_reset_query();
    endforeach;
    echo '</div>';
    ?>

    <h2 class="section-title">Coming Soon</h2>

    <?php 
    // Get all the movies that have upcoming screenings that are NOT in the first set
    // First, get 100 dates starting with today
    $comingSoonDays = [];
    $period = new DatePeriod(
        new DateTime(date_i18n("Y-m-d 00:00:00")), // Start date of the period
        new DateInterval('P1D'), // Define the intervals as Periods of 1 Day
        100 // Apply the interval 100 times on top of the starting date
    );
    foreach ($period as $day) {
        $comingSoonDays[] = '\''.$day->format('Y-m-d').'\'';
    }
    $comingSoonDaysAsString = implode (',', $comingSoonDays);
    ?>

    <?php
    // Next, get all the "coming soon" movies that have screenings on those dates
    global $wpdb;
    $screenings_table_name = $wpdb->prefix . 'gi_screenings';
    $comingSoonScreeningsQuery = "
        SELECT film_id, screening
        FROM {$screenings_table_name} 
        WHERE screening_date IN ($comingSoonDaysAsString)
        AND status = 1
        ORDER BY screening
    ";

    $result = $wpdb->get_results($comingSoonScreeningsQuery);
    if ( count($result) ) :
        $comingSoonFilmIds = [];
        foreach( $result as $key => $row) :
            echo '<span class="debug">' . $row->film_id . ': ' . $row->screening . '<br></span>';
            if (!in_array($row->film_id, $comingSoonFilmIds)) :
                $comingSoonFilmIds[] = $row->film_id;
            endif;
        endforeach;
        $comingSoonFilmIds = array_diff($comingSoonFilmIds, $nowPlayingFilmIds);

    endif;
    ?>

    <?php 
    // Display their "half teasers", in "next screening" order
    echo '<div class="film-cards film-cards--coming_soon">';
    foreach ($comingSoonFilmIds as $comingSoonFilmId) :
        $args = array (
            'post_type' => 'film',
            'posts_per_page' => '1',
            'meta_key' => 'agile_film_id',
            'meta_value' => $comingSoonFilmId
        );
        $getThePostId = new WP_Query( $args );
        if ( $getThePostId->have_posts() ) :
            while ( $getThePostId->have_posts() ) : $getThePostId->the_post();
                $filmPostId = get_the_ID();
                filmCard(filmPostId:$filmPostId, classes:'coming_soon');
            endwhile;
        endif;
        wp_reset_query();
    endforeach;
    echo '</div>';
    ?>

</div>

<?php
get_footer();
