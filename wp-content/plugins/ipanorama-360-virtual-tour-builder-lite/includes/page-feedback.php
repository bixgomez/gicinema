<?php
defined('ABSPATH') || exit;

$data = '';
$data .= '<div class="ipanorama-page-feedback">' . PHP_EOL;
$data .= '<p>' . esc_html__('We’d love to hear your thoughts! If you’re enjoying the iPanorama 360 plugin, please consider giving us a rating.', 'ipanorama') . '</p>' . PHP_EOL;
$data .= '<p>';
$data .=  esc_html__('Your feedback means so much to us! If you’re really happy, we’d appreciate a 5-star rating!', 'ipanorama') . PHP_EOL;
$data .= '<a href="https://wordpress.org/plugins/ipanorama-360-virtual-tour-builder-lite/#reviews" target="_blank">' . esc_html__('Just click here to share your thoughts.', 'ipanorama') . '</a>'. PHP_EOL;
$data .= '</p>';
$data .= '<div class="ipanorama-page-feedback-close"><i class="xfa fa-times"></i></div>' . PHP_EOL;
$data .= '</div>' . PHP_EOL;

echo wp_kses_post($data);
?>