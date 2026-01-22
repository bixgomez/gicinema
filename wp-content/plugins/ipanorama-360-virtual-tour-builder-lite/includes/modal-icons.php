<?php
defined('ABSPATH') || exit;
?>
<div id="ipanorama-modal-{{ modalData.id }}" class="ipanorama-modal ipanorama-no-max-width" tabindex="-1">
	<div class="ipanorama-modal-dialog">
		<div class="ipanorama-modal-header">
			<div class="ipanorama-modal-close" al-on.click="modalData.deferred.resolve('close');">&times;</div>
			<div class="ipanorama-modal-title"><i class="xfa fa-info-circle"></i><?php esc_html_e('Select an icon', 'ipanorama'); ?></div>
		</div>
		<div class="ipanorama-modal-data ipanorama-modal-loading">
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-icons">
					<div class="ipanorama-modal-icon" al-repeat="icon in modalData.icons" al-value="icon" al-on.click="modalData.fn.onClickIcon(modalData, $event, $element, $value)" al-on.dblclick="modalData.fn.onDblClickIcon(modalData)" title="{{modalData.fn.getIconName(modalData, icon)}}">
						<i class="xfa {{modalData.fn.getIcon(modalData, icon)}}"></i><span>{{modalData.fn.getIconName(modalData, icon)}}</span>
					</div>
				</div>
			</div>
		</div>
		<div class="ipanorama-modal-footer">
			<div class="ipanorama-modal-text"><?php esc_html_e('Selected icon:', 'ipanorama'); ?> <b>{{modalData.selectedIcon}}</b></div>
			<div class="ipanorama-modal-btn ipanorama-modal-btn-close" al-on.click="modalData.deferred.resolve('close');"><?php esc_html_e('Close', 'ipanorama'); ?></div>
			<div class="ipanorama-modal-btn ipanorama-modal-btn-create" al-on.click="modalData.deferred.resolve(true);"><?php esc_html_e('OK', 'ipanorama'); ?></div>
		</div>
	</div>
</div>