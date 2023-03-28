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

get_header();
?>

<h1>NEW HOME PAGE</h1>

<hr />

<h2>Now Playing</h2>
<ul>
    <li>
        Films showing in the next 7 days, one per line.
    </li>

    <li>
        First, get 7 dates starting with today

        <?php 
        $days = [];
        $period = new DatePeriod(
            new DateTime(date_i18n("Y-m-d 00:00:00")), // Start date of the period
            new DateInterval('P1D'), // Define the intervals as Periods of 1 Day
            6 // Apply the interval 6 times on top of the starting date
        );
        foreach ($period as $day) {
            $days[] = '\''.$day->format('Y-m-d').'\'';
        }
        $daysString = implode (',', $days);
        print '<pre>';
        print_r($days);
        print_r($daysString);
        print '</pre>';
        ?>
    
    </li>

    <li>
        Next, get all the movies that have screenings on those dates
    
        <?php
        global $wpdb;
        $screenings_table_name = $wpdb->prefix . 'gi_screenings';
        $screenings_query = "
            SELECT film_id, screening
            FROM {$screenings_table_name} 
            WHERE screening_date IN ($daysString)
            ORDER BY screening
        ";
        echo '<pre>' . $screenings_query . '</pre>';
        $result = $wpdb->get_results($screenings_query);
        if ( count($result) ) :
            $nowPlayingFilmIds = [];
            foreach( $result as $key => $row) :
                echo $row->film_id . ': ' . $row->screening . '<br>';
                if (!in_array($row->film_id, $nowPlayingFilmIds)) :
                    $nowPlayingFilmIds[] = $row->film_id;
                endif;
            endforeach;
            print '<pre>';
            print_r($nowPlayingFilmIds);
            print '</pre>';
        endif;
        ?>

    </li>

    <li>
        Display their full teasers, in "next screening" order

        <?php 
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
                    filmCard($filmPostId);
                endwhile;
            endif;
            wp_reset_query();
        endforeach;
        ?>
    </li>
</ul>

<hr />

<h2>Coming Soon</h2>
    <li>Get all the movies that have upcoming screenings that are NOT in the first set</li>
    <li>Display their "half teasers", in "next screening" order</li>
<hr />

<h2>Membership</h2>
info & link

<h2>Donation</h2> 
info & link

<hr />

<?php
get_footer();
