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

require get_template_directory() . '/inc/cinema-functions/films-by-date.php';

get_header();
?>

<?php

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
$this_month = date( "m", strtotime( $this_month ) );

$this_month_display = date( 'F Y', strtotime( $the_month ) );

//$first_day_of_week = date('Y-m-d',strtotime('last sunday'));
//$first_day_of_movie_week = date('Y-m-d',strtotime('last friday'));
?>

<div class="box"></div>

    <!-- calendar -->
    <div class="content-layout">
        <main class="site-main" id="main">
            <h1 class="entry-title"><?php echo $this_month_display; ?></h1>

            <div class="debug">
				<?php echo "The month we're on is: " . $the_month ?> <br>
				<?php echo "The first day of this month is: " . $first_day_of_month ?> <br>
				<?php echo "The first of this month was a: " . $first_of_month_day ?> <br>
				<?php echo "There are " . $days_in_month . " days in this month." ?> <br>
				<?php echo "The last day of this month is: " . $last_day_of_month ?>
            </div>

            <div class="calendar-header">
                <div class="month-choice">
                    <a href="/calendar/?month=<?php echo $prev_month ?>#content"><?php echo $prev_month ?></a>
                </div>
                <div class="month-choice">
					<?php if ( $the_month != $this_month OR 1 == 1 ) : ?>
                        <a href="/calendar/?month=<?php echo $this_month ?>#content">This month</a>
					<?php endif ?>
                </div>
                <div class="month-choice">
                    <a href="/calendar/?month=<?php echo $next_month ?>#content"><?php echo $next_month ?></a>
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

				for ( $k = 1; $k <= $days_in_month; $k ++ ) {
                    $n = $k < 10 ? '0'.$k : $k;
					echo '<li class="day empty">';
                    $curr_date = $curr_year . '-' . $curr_month . '-' . $n;
                    // echo $curr_date . '<br/>';
                    echo filmsByDate($curr_date);
                    echo '</li>';
				}
				?>

            </ul>
        </main>
    </div>

<?php
get_footer();
