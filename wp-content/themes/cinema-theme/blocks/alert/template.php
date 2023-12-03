<?php
/**
 * Block Name: Alert Block
 *
 * Description: Displays a block of formatted "alert" text.
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
if ($link) {
    $link_title = $link[ 'title' ];
    $link_url = $link[ 'url' ];
}

?>

<?php if($link || $title): ?>
<div class="<?php echo $class_name ?>">
    <?php 
    if ($link && $title) {
        echo '<h2 class="title title--alert"><a href="' . $link_url . '">' . $title . '</a></h2>';
    } elseif ($title) {
        echo '<h2 class="title title--alert">' . $title . '</h2>';
    }
    if ($text) {
        echo '<div class="text text--alert">' . $text . '</div>';
    }
    if ($link) {
        echo '<div class="link link--alert"><a href="' . $link_url . '">' . $link_title . '</a></div>';
    }
    ?>
</div>
<?php endif; ?>
