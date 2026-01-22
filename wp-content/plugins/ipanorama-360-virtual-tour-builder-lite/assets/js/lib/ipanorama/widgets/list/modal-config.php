<?php
// If this file is called directly, abort.
if(!defined('ABSPATH')) {
	exit;
}
// Note:
// 1) we can use only one level parameters
// 2) we can use boolean, text
?>
<div id="ipanorama-modal-{{modalData.id}}" class="ipanorama-modal" tabindex="-1">
	<div class="ipanorama-modal-dialog">
		<div class="ipanorama-modal-header">
			<div class="ipanorama-modal-close" al-on.click="modalData.deferred.resolve('close');">&times;</div>
			<div class="ipanorama-modal-title"><i class="fa fa-info-circle"></i><?php esc_html_e('Setup widget settings', 'ipanorama'); ?></div>
		</div>
		<div class="ipanorama-modal-data">
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the title control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Title control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.titleControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the previous scene control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Previous scene control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.prevSceneControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the next scene control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Next scene control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.nextSceneControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the audio control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Audio control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.audioControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the compass control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Compass control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.compassControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the fullscreen toggle control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Fullscreen toggle control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.fullscreenControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable the scene list control', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Scene list control', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.sceneListControl" data-default="true"></div>
			</div>
			
			<div class="ipanorama-control">
				<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable availability of access to move the scene to the next, if possible', 'ipanorama'); ?>"></div>
				<div class="ipanorama-label"><?php esc_html_e('Move to a next scene', 'ipanorama'); ?></div>
				<div al-toggle="modalData.config.moveToNextScene" data-default="true"></div>
			</div>
		</div>
		<div class="ipanorama-modal-footer">
			<div class="ipanorama-modal-btn ipanorama-modal-btn-close" al-on.click="modalData.deferred.resolve('close');"><?php esc_html_e('Close', 'ipanorama'); ?></div>
			<div class="ipanorama-modal-btn ipanorama-modal-btn-create" al-on.click="modalData.deferred.resolve(true);"><?php esc_html_e('OK', 'ipanorama'); ?></div>
		</div>
	</div>
</div>