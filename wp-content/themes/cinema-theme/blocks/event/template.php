<?php
/**
 * Block Name: Event Block
 *
 * Description: Displays a block of formatted "event" text.
 *
 * Resources:
 * https://alphaparticle.com/blog/custom-block-icons-with-acf-blocks/
 * https://joeyfarruggio.com/wordress/register-acf-blocks/
 */

// The block attributes
$block = $args['block'];

// The block ID.
$block_id = $args['block_id'];

// The block class names.
$class_name = $args['class_name'];

// The block fields.
$title = get_field( 'title' );
$text = get_field( 'text' );
$link = get_field( 'link' );
$link_title = $link[ 'title' ];
$link_url = $link[ 'url' ];

?>

<div class="<?php echo $class_name ?>">
    <?php echo '<h2 class="title title--event"><a href="' . $link_url . '">' . $title . '</a></h2>'; ?>
    <?php echo '<div class="text text--event">' . $text . '</div>'; ?>
    <?php echo '<div class="link link--event"><a href="' . $link_url . '">' . $link_title . '</a></div>'; ?>
</div>
