<?php

function this_week_page_updater() {

    global $wpdb;
    $screenings_table_name = $wpdb->prefix . 'gi_screenings';
    $this_week_page_slug = 'this-week';
    $this_week_page = get_page_by_path($this_week_page_slug);
    $this_week_page_content = '<h2>This Week at the GI</h2>';

    // https://stackoverflow.com/questions/470617/how-do-i-get-the-current-date-and-time-in-php
    date_default_timezone_set('America/Los_Angeles');
    $todays_date = date('Y-m-d');
    $week_start = rangeWeek($todays_date)['start'];
    $week_end = rangeWeek($todays_date)['end'];
    $this_week_dates = getDatesFromRange($week_start, $week_end);

    echo "<pre>";
    print_r($this_week_dates);
    echo "</pre>";

    $this_week_page_content .= '<ul class="list list--week">';

    foreach ($this_week_dates as $this_week_date) {
        $result = $wpdb->get_results("SELECT * FROM $screenings_table_name WHERE screening_date = '$this_week_date' ORDER BY screening_time");

        $screening_date_display = strtotime($this_week_date);
        $screening_date_display = date("l, F j, Y", $screening_date_display);

        if ($result) {
            // echo '<h5>Adding date to page</h5>';

            echo '<hr>';
            echo 'Date: ' . $this_week_date . '<br>';

            $this_week_page_content .= '<li>' . $screening_date_display . '<ul>';
            foreach ($result as $row) {
                echo 'Film ID: ' . $row->film_id . ', ';
                echo $row->screening_date . ', ';
                echo $row->screening_time . '<br>';

                $getFilmInfo = get_posts(array(
                    'numberposts'   => -1,
                    'post_type'     => 'film',
                    'meta_key'      => 'agile_film_id',
                    'meta_value'    => $row->film_id
                ));

                if ($getFilmInfo) {
                    // echo '<h5>Adding films to date</h5>';
                    echo $getFilmInfo[0]->post_title;
                    $screening_time_display = strtotime($row->screening_time);
                    $screening_time_display = date("g:i a", $screening_time_display);
                    $this_week_page_content .= '<li>'. $screening_time_display . ': ' . $getFilmInfo[0]->post_title . '</li>';
                    echo '<pre>';
                    print_r($getFilmInfo);
                    echo '</pre>';
                }
                else {
                    echo '<h5>No films?</h5>';
                }

            }
            $this_week_page_content .= '</ul></li>';
        }
    }
    $this_week_page_content .= '</ul><!-- class="list list--week" -->';

    // Update page with content.
    if ($this_week_page) {
        $data = array(
            'ID' => $this_week_page->ID,
            'post_content' => $this_week_page_content
        );
        wp_update_post( $data );
    } else {
        return null;
    }
}

// Returns film ids, dates, and times for given date.
function findFilmsOnDate($date) {

}

// https://hotexamples.com/examples/-/-/getDatesFromRange/php-getdatesfromrange-function-examples.html
function getDatesFromRange($start, $end) {
    $interval = new DateInterval('P1D');
    $realEnd = new DateTime($end);
    $realEnd->add($interval);
    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
    foreach ($period as $date) {
        $array[] = $date->format('Y-m-d');
    }
    return $array;
}

// https://stackoverflow.com/questions/5552862/finding-date-range-for-current-week-month-and-year
function rangeMonth ($datestr) {
    date_default_timezone_set (date_default_timezone_get());
    $dt = strtotime ($datestr);
    return array (
        "start" => date ('Y-m-d', strtotime ('first day of this month', $dt)),
        "end" => date ('Y-m-d', strtotime ('last day of this month', $dt))
    );
}
function rangeWeek ($datestr) {
    date_default_timezone_set (date_default_timezone_get());
    $dt = strtotime ($datestr);
    return array (
        "start" => date ('N', $dt) == 1 ? date ('Y-m-d', $dt) : date ('Y-m-d', strtotime ('last monday', $dt)),
        "end" => date('N', $dt) == 7 ? date ('Y-m-d', $dt) : date ('Y-m-d', strtotime ('next sunday', $dt))
    );
}
