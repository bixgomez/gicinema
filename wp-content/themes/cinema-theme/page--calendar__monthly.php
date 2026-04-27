<?php
/**
 * Template Name: Monthly Calendar
 *
 * This is the template that displays the calendar.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on this WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Cinema_Theme
 */

require_once get_template_directory() . '/inc/functions/films-by-date.php';
require_once get_template_directory() . '/inc/functions/display-film-for-date.php';

get_header();

$this_month = date( "Y-m" );

if ( isset( $_GET['month'] ) ) {
	$the_month = $_GET['month'];
} else {
	$the_month = date( 'Y-m' );
}

$the_month_time = strtotime( $the_month );

$first_day_of_month = date( 'Y-m-01', strtotime( $the_month ) );
$first_of_month_day = date( 'w', strtotime( $first_day_of_month ) );
$days_in_month      = date( 't', strtotime( $first_day_of_month ) );
$last_day_of_month  = date( 'Y-m-t' );

$prev_month = date( "Y-m", strtotime( "-1 months", $the_month_time ) );
$this_month = date( "Y-m", strtotime( $this_month ) );
$next_month = date( "Y-m", strtotime( "+1 months", $the_month_time ) );

$curr_year = date( "Y", strtotime( $the_month ) );
$curr_month = date( "m", strtotime( $the_month ) );

$this_year = date( "Y", strtotime( $this_month ) );

$this_month_display = date( 'F Y', strtotime( $the_month ) );
$prev_month_display = date( 'M Y', strtotime( $prev_month ) );
$next_month_display = date( 'M Y', strtotime( $next_month ) );
?>

<!-- calendar -->
<div class="content-layout">
    <main class="site-main" id="main">
        <h1 class="entry-title"><?php echo $this_month_display; ?></h1>
        <div class="calendar-header">
            <div class="month-choice">
                <a href="/calendar/?month=<?php echo $prev_month ?>"><?php echo $prev_month_display ?></a>
            </div>
            <div class="month-choice">
                <?php if ( $the_month != $this_month ) : ?>
                    <a href="/calendar/?month=<?php echo $this_month ?>">This month</a>
                <?php endif ?>
            </div>
            <div class="month-choice">
                <a href="/calendar/?month=<?php echo $next_month ?>"><?php echo $next_month_display ?></a>
            </div>
        </div>
        <ul class="calendar calendar--monthly">
            <li class="day heading">Sunday</li>
            <li class="day heading">Monday</li>
            <li class="day heading">Tuesday</li>
            <li class="day heading">Wednesday</li>
            <li class="day heading">Thursday</li>
            <li class="day heading">Friday</li>
            <li class="day heading">Saturday</li>
            <?php
            for ( $k = 0; $k < $first_of_month_day; $k ++ ) {
                echo '<li class="day empty"></li>';
            }

            $moviesInMonth = array();

            for ( $k = 1; $k <= $days_in_month; $k ++ ) {
                $n = $k < 10 ? '0'.$k : $k;
                $todays_date = wp_date("Y-m-d");
                $curr_date_a = $curr_year . '-' . $curr_month . '-' . $n;
                $curr_date_b = date_create_from_format("Y-m-d",$curr_date_a);
                $curr_date_full = date_format($curr_date_b,"l, F j, Y");
                $curr_date_num = date_format($curr_date_b,"j");

                if ($curr_date_a < $todays_date) {
                    $when = 'past';
                } elseif ($curr_date_a > $todays_date) {
                    $when = 'future';
                } else {
                    $when = 'present';
                }
                
                echo '<li class="day day--'. $when .'">';
                echo '<div class="date-display">';
                echo '<span class="date-display--day date-display--day__full">'.$curr_date_full.' </span>';
                echo '<span class="date-display--day date-display--day__num">'.$curr_date_num.'</span>';
                echo '</div>';
                echo '<div class="films-display">';
                $post_ids = filmsByDate($curr_date_a);
                if ( is_array($post_ids) ) {
                foreach ($post_ids as $post_id) :
                    displayFilmForDate(
                        $film_id = $post_id,
                        $date = $curr_date_a
                    );
                endforeach;
                }
                echo '</div>';
                echo '</li>';
            }
            ?>
        </ul>
    </main>
</div>

<div class="modal-outer">
    <div class="modal-inner">
        <button class="close-modal">Close</button>
        <div class="modal-content"></div>
    </div>
</div>

<?php
get_footer();
