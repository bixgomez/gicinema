<?php
defined('ABSPATH') || exit;
?>
<div id="ipanorama-modal-{{modalData.id}}" class="ipanorama-modal" tabindex="-1">
	<div class="ipanorama-modal-dialog">
		<div class="ipanorama-modal-header">
			<div class="ipanorama-modal-close" al-on.click="modalData.deferred.resolve('close');">&times;</div>
			<div class="ipanorama-modal-title"><i class="xfa fa-info-circle"></i><?php esc_html_e('Select a show effect', 'ipanorama'); ?></div>
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
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Bounce</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceIn">BounceIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceInDown">BounceInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceInLeft">BounceInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceInRight">BounceInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-bounceInUp">BounceInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Fade</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeIn">FadeIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeInDown">FadeInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeInLeft">FadeInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeInRight">FadeInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-fadeInUp">FadeInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Rotate</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateIn">RotateIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateInDownLeft">RotateInDownLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateInDownRight">RotateInDownRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateInUpLeft">RotateInUpLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rotateInUpRight">RotateInUpRight</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Zoom</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomIn">ZoomIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomInDown">ZoomInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomInLeft">ZoomInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomInRight">ZoomInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-zoomInUp">ZoomInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Slide</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideInDown">SlideInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideInLeft">SlideInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideInRight">SlideInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-slideInUp">SlideInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Perspective</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveInDown">PerspectiveInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveInLeft">PerspectiveInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveInRight">PerspectiveInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-perspectiveInUp">PerspectiveInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Tin</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinInDown">TinInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinInLeft">TinInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinInRight">TinInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-tinInUp">TinInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Space</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceInDown">SpaceInDown</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceInLeft">SpaceInLeft</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceInRight">SpaceInRight</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-spaceInUp">SpaceInUp</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Flip</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-flip">Flip</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-flipInX">FlipInX</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-flipInY">FlipInY</div>
				</div>
			</div>
			
			<div class="ipanorama-modal-group">
				<div class="ipanorama-modal-title">Advanced</div>
				<div class="ipanorama-modal-btn-group">
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-lightSpeedIn">LightSpeedIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-rollIn">RollIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-vanishIn">VanishIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-swashIn">SwashIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-foolishIn">FoolishIn</div>
					<div class="ipanorama-modal-effect" data-fx-name="ipnrm-fx-holeIn">HoleIn</div>
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