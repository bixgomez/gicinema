<?php
defined('ABSPATH') || exit;
?>
<div id="ipanorama-modal-{{ modalData.id }}" class="ipanorama-modal" tabindex="-1">
	<div class="ipanorama-modal-dialog">
		<div class="ipanorama-modal-header">
			<div class="ipanorama-modal-close" al-on.click="modalData.deferred.resolve('close');">&times;</div>
			<div class="ipanorama-modal-title"><i class="xfa fa-info-circle"></i><?php esc_html_e('Select a hide effect', 'ipanorama'); ?></div>
		</div>
		<div class="ipanorama-modal-data">
			<div class="ipanorama-modal-effects">
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">General</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounce">Bounce</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-pulse">Pulse</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rubberBand">Rubber Band</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-shake">Shake</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-headShake">Head Shake</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-swing">Swing</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tada">Tada</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-wobble">Wobble</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-jello">Jello</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-hinge">Hinge</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Bounce</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceOut">BounceOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceOutDown">BounceOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceOutLeft">BounceOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceOutRight">BounceOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceOutUp">BounceOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Fade</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeOut">fadeOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeOutDown">fadeOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeOutLeft">fadeOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeOutRight">fadeOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeOutUp">fadeOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Rotate</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateOut">rotateOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateOutDownLeft">rotateOutDownLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateOutDownRight">rotateOutDownRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateOutUpLeft">rotateOutUpLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateOutUpRight">rotateOutUpRight</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Zoom</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomOut">zoomOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomOutDown">zoomOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomOutLeft">zoomOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomOutRight">zoomOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomOutUp">zoomOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Slide</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideOutDown">slideOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideOutLeft">slideOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideOutRight">slideOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideOutUp">slideOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Perspective</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveOutDown">perspectiveOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveOutLeft">perspectiveOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveOutRight">perspectiveOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveOutUp">perspectiveOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Tin</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinOutDown">tinOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinOutLeft">tinOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinOutRight">tinOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinOutUp">tinOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Space</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceOutDown">spaceOutDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceOutLeft">spaceOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceOutRight">spaceOutRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceOutUp">spaceOutUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Flip</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-flip">Flip</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-flipOutX">flipOutX</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-flipOutY">flipOutY</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Advanced</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-lightSpeedOut">LightSpeedOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rollOut">RollOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-vanishOut">VanishOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-swashOut">SwashOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-foolishOut">FoolishOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-holeOut">HoleOut</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bombOutLeft">BombOutLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bombOutRight">BombOutRight</div>
				</div>
			</div>
			</div>
		</div>
		<div class="ipanorama-modal-footer">
			<div class="ipanorama-modal-text"><?php esc_html_e('Selected effect:', 'ipanorama'); ?> <b>{{modalData.selectedClass}}</b></div>
			<div class="ipanorama-modal-btn ipanorama-modal-btn-close" al-on.click="modalData.deferred.resolve('close');"><?php esc_html_e('Close', 'ipanorama'); ?></div>
			<div class="ipanorama-modal-btn ipanorama-modal-btn-create" al-on.click="modalData.deferred.resolve(true);"><?php esc_html_e('OK', 'ipanorama'); ?></div>
		</div>
	</div>
</div>