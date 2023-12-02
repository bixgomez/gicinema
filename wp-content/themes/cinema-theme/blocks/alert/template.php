<?php
/**
 * Block Name: Alert Block
 *
 * Description: Displays a block of text formatted as an alert.
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

// The block data.
$data = $args['data'];

// The block fields.
$title = get_field( 'title' );
$text = get_field( 'text' );
$link = get_field( 'link' );
$link_title = $link[ 'title' ];
$link_url = $link[ 'url' ];

// Set the additional classes.
if ( $data['additional_classes']) {
    $additional_classes = $data['additional_classes'];
} else {
    $additional_classes = '';
}

// Set the body text.
if ( $data['body']) {
    $body = $data['body'];
} else {
    $body = '';
}

?>

<div class="<?php echo $class_name ?> <?php echo $additional_classes ?>">
    <?php echo $title ?>
    <?php echo $text ?>
    <?php echo $link ?>
    <?php echo $link_title ?>
    <?php echo $link_url ?>
</div>