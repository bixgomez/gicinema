<?php
/**
 * Force WordPress to allow automatic core updates
 * even on a Git-managed site.
 */

add_filter( 'automatic_updates_is_vcs_checkout', '__return_false' );

