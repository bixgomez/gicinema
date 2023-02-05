<?php
/**
 * Template Name: Monthly Calendar
 *
 * This is the template that displays the calendar.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Cinema_Theme
 */

get_header();
?>

<?php
$first_day_of_month = date('Y-m-01');
$last_day_of_month = date('Y-m-t');
$first_day_of_week = date('Y-m-d',strtotime('last sunday'));
?>

<!-- calendar -->
<div class="content-layout">

  <main class="site-main">
      <h1 class="entry-title">Finally, a Calendar!</h1>

      <div class="debug">
	      <?php echo $first_day_of_month ?> <br>
	      <?php echo $first_day_of_week ?> <br>
	      <?php echo $last_day_of_month ?>
      </div>

      <ul class="calendar calendar--monthly">
          <li class="day heading">Sunday</li>
          <li class="day heading">Monday</li>
          <li class="day heading">Tuesday</li>
          <li class="day heading">Wednesday</li>
          <li class="day heading">Thursday</li>
          <li class="day heading">Friday</li>
          <li class="day heading">Saturday</li>
          <li class="day empty"></li>
          <li class="day empty"></li>
          <li class="day empty"></li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
          <li class="day">0</li>
      </ul>
  </main>

</div>

<?php
get_footer();
