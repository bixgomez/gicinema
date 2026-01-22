<?php
defined('ABSPATH') || exit;

$data = '';
$data .= '<div class="ipanorama-page-info">' . PHP_EOL;
$data .= '<p><b>' . esc_html__('This is the LITE version of the plugin.', 'ipanorama') . '</b></p>' . PHP_EOL;
$data .= '<p><i>' . esc_html__('It has only one limitation: you can only create and use 1 virtual tour.', 'ipanorama') . '</i></p>' . PHP_EOL;
$data .= '<p><i>' . esc_html__('If you’re looking for more features and enhanced functionality, we invite you to consider upgrading to', 'ipanorama') . ' ' . '<a href="https://1.envato.market/getipanorama360" target="_blank">' . esc_html__('the pro version!', 'ipanorama') . '</a></i></p>'. PHP_EOL;
$data .= '<div class="ipanorama-page-info-close"><i class="xfa fa-times"></i></div>' . PHP_EOL;
$data .= '</div>' . PHP_EOL;

echo wp_kses_post($data);

?>