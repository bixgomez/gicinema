<?php
defined('ABSPATH') || exit;

$plugin_url = plugin_dir_url(dirname(__FILE__));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<?php
wp_enqueue_style('ipanorama-preview-css', $plugin_url . 'assets/css/preview.min.css', [], IPANORAMA_PLUGIN_VERSION);
wp_enqueue_script('ipanorama-loader-js', $plugin_url . 'assets/js/loader.min.js', ['jquery'], IPANORAMA_PLUGIN_VERSION, false);
wp_localize_script('ipanorama-loader-js', 'ipanorama_globals', $this->getLoaderGlobals($this->virtualtour_version));
wp_head();
?>
</head>
<body>
<?php
    $atts = array('id'=>$this->virtualtour_id);

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $this->shortcode($atts);

    if($this->previewPageSettings->wpFooter) {
        wp_footer();
    }
?>
</body>
</html>