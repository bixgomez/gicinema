<?php
defined('ABSPATH') || exit;

$list_table = new iPanorama_List_Table_Items();
$list_table->prepare_items();
?>
<!-- /begin ipanorama app -->
<div class="ipanorama-root" id="ipanorama-app-items">
	<?php require 'page-info.php'; ?>
	<?php require 'page-feedback.php'; ?>
	<div class="ipanorama-page-header">
		<div class="ipanorama-title">
            <i class="xfa fa-cubes"></i>
            <span>iPanorama 360<sup><?php echo esc_attr(IPANORAMA_PLUGIN_PLAN); ?></sup></span>
            <span> - </span>
            <?php esc_html_e('Items', 'ipanorama'); ?>
        </div>
		<div class="ipanorama-actions">
			<a class="ipanorama-blue" href="?page=ipanorama_item" title="<?php esc_html_e('Create a new item', 'ipanorama'); ?>"><?php esc_html_e('Add Item', 'ipanorama'); ?></a>
		</div>
	</div>
	<div class="ipanorama-messages" id="ipanorama-messages">
	</div>
	<div class="ipanorama-app">
		<?php $list_table->views(); ?>
		<form method="post">
			<?php $list_table->search_box(esc_html__('Search Items', 'ipanorama'),'item'); ?>
            <input type="hidden" name="page" value="<?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            echo sanitize_key( filter_var( wp_unslash( $_REQUEST['page'] ), FILTER_DEFAULT ) ) ?>
            ">
			<?php $list_table->display(); ?>
		</form>
	</div>
</div>
<!-- /end ipanorama app -->