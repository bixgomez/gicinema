<?php

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

require_once get_template_directory() . '/inc/functions/film-card.php';

$filmId = $_POST['filmId'];
$filmPostId = $filmId;

filmCard(filmPostId:$filmPostId);
