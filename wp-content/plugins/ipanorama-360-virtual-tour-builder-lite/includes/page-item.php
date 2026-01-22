<?php
defined('ABSPATH') || exit;

$page = sanitize_key(filter_input(INPUT_GET, 'page', FILTER_DEFAULT));
$author = get_the_author_meta('display_name', $item->author);
$editor = get_the_author_meta('display_name', $item->editor);
$created = mysql2date(get_option('date_format'), $item->created) . ' at ' . mysql2date(get_option('time_format'), $item->created);
$modified = mysql2date(get_option('date_format'), $item->modified) . ' at ' . mysql2date(get_option('time_format'), $item->modified);
?>
<!-- /begin ipanorama app -->
<div class="ipanorama-root" id="ipanorama-app-item" style="display:none;">
	<?php require 'page-info.php'; ?>
	<input id="ipanorama-load-config-from-file" type="file" style="display:none;" />
	<div class="ipanorama-page-header">
		<div class="ipanorama-title">
            <i class="xfa fa-cubes"></i>
            <span>iPanorama 360<sup><?php echo esc_attr(IPANORAMA_PLUGIN_PLAN); ?></sup></span>
            <span> - </span>
            <?php esc_html_e('Item', 'ipanorama'); ?>
        </div>
		<div class="ipanorama-actions-menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <div class="ipanorama-actions-menu-items">
			    <a href="#" al-on.click="appData.fn.saveConfigToFile(appData)" title="<?php esc_html_e('Save config to a JSON file', 'ipanorama'); ?>"><?php esc_html_e('Save As...', 'ipanorama'); ?></a>
			    <a href="#" al-on.click="appData.fn.loadConfigFromFile(appData)" title="<?php esc_html_e('Load config from a JSON file', 'ipanorama'); ?>"><?php esc_html_e('Load As...', 'ipanorama'); ?></a>
            </div>
		</div>
	</div>
	<div class="ipanorama-messages" id="ipanorama-messages">
	</div>
	<div class="ipanorama-app" id="ipanorama-app">
		<div class="ipanorama-loader-wrap">
			<div class="ipanorama-loader">
				<div class="ipanorama-loader-bar"></div>
				<div class="ipanorama-loader-bar"></div>
				<div class="ipanorama-loader-bar"></div>
				<div class="ipanorama-loader-bar"></div>
			</div>
		</div>
		<div class="ipanorama-wrap">
			<div class="ipanorama-main-header">
				<input class="ipanorama-title" type="text" al-text="appData.config.title" placeholder="<?php esc_html_e('Title', 'ipanorama'); ?>">
			</div>
			<div class="ipanorama-workplace">
				<div class="ipanorama-view-wrap">
					<div class="ipanorama-view" id="ipanorama-view"></div>
					<div class="ipanorama-view-bottombar" al-attr.class.ipanorama-active="appData.ui.activeScene != null">
						<div class="ipanorama-view-info" id="ipanorama-view-info"></div>
						<span>
							<div class="ipanorama-compass-north" id="ipanorama-compass-north" ><?php esc_html_e('set the compass north', 'ipanorama'); ?></div>
							<div class="ipanorama-start-point" id="ipanorama-start-point" ><?php esc_html_e('set the start point', 'ipanorama'); ?></div>
						</span>
					</div>
				</div>
				<div class="ipanorama-main-menu">
					<div class="ipanorama-left-panel">
						<div class="ipanorama-list">
							<a class="ipanorama-item ipanorama-small ipanorama-lite" href="https://1.envato.market/getipanorama360" target="_blank" al-if="appData.plan=='lite'"><?php esc_html_e('Buy Pro version', 'ipanorama'); ?></a>
						</div>
					</div>
					<div class="ipanorama-right-panel">
						<div class="ipanorama-list">
							<div class="ipanorama-item ipanorama-green" al-on.click="appData.fn.preview(appData);" title="<?php esc_html_e('The config should be saved before preview', 'ipanorama'); ?>" al-if="appData.wp_item_id != null"><?php esc_html_e('Preview', 'ipanorama'); ?></div>
							<div class="ipanorama-item ipanorama-blue" al-on.click="appData.fn.saveConfig(appData);" title="<?php esc_html_e('Save config to database', 'ipanorama'); ?>"><?php esc_html_e('Save', 'ipanorama'); ?></div>
						</div>
					</div>
				</div>
				<div class="ipanorama-main-tabs ipanorama-clear-fix">
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.general" al-on.click="appData.fn.onTab(appData, 'general')"><?php esc_html_e('General', 'ipanorama'); ?><div class="ipanorama-status" al-if="appData.config.active"></div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.scenes" al-on.click="appData.fn.onTab(appData, 'scenes')"><?php esc_html_e('Scenes', 'ipanorama'); ?><div class="ipanorama-counter" al-if="appData.config.scenes.length>0">{{appData.config.scenes.length}}</div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.markers" al-on.click="appData.fn.onTab(appData, 'markers')" al-if="appData.ui.activeScene != null"><?php esc_html_e('Markers', 'ipanorama'); ?><div class="ipanorama-counter" al-if="appData.ui.activeScene.markers.length>0">{{appData.ui.activeScene.markers.length}}</div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.shapes" al-on.click="appData.fn.onTab(appData, 'shapes')" al-if="appData.ui.activeScene != null && appData.ui.activeScene.type != 'flat'"><?php esc_html_e('Shapes', 'ipanorama'); ?><div class="ipanorama-counter" al-if="appData.ui.activeScene.shapes.length>0">{{appData.ui.activeScene.shapes.length}}</div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.tracks" al-on.click="appData.fn.onTab(appData, 'tracks')"><?php esc_html_e('Tracks', 'ipanorama'); ?><div class="ipanorama-counter" al-if="appData.config.audio.tracks.length>0">{{appData.config.audio.tracks.length}}</div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.customCSS" al-on.click="appData.fn.onTab(appData, 'customCSS')"><?php esc_html_e('Custom CSS', 'ipanorama'); ?><div class="ipanorama-status" al-if="appData.config.customCSS.active"></div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.customJS" al-on.click="appData.fn.onTab(appData, 'customJS')"><?php esc_html_e('Custom JS', 'ipanorama'); ?><div class="ipanorama-status" al-if="appData.config.customJS.active"></div></div>
					<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.tabs.shortcode" al-on.click="appData.fn.onTab(appData, 'shortcode')" al-if="appData.wp_item_id"><?php esc_html_e('Shortcode', 'ipanorama'); ?></div>
				</div>
				<div class="ipanorama-main-data">
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.general">
						<div class="ipanorama-stage">
							<div class="ipanorama-main-panel ipanorama-main-panel-general">
								<div class="ipanorama-data ipanorama-active">
									<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.generalTab.virtualtour">
										<div class="ipanorama-block-header" al-on.click="appData.fn.onGeneralTab(appData,'virtualtour')">
											<div class="ipanorama-block-title"><?php esc_html_e('Virtual tour', 'ipanorama'); ?></div>
											<div class="ipanorama-block-state"></div>
										</div>
										<div class="ipanorama-block-data">
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable item', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Enable item', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.active"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Automatic scene loading after the plugin init action', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Auto load', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.autoLoad"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a theme for markers, tooltips & popovers', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Theme', 'ipanorama'); ?></div>
												<select class="ipanorama-select ipanorama-capitalize" al-select="appData.config.theme">
													<option al-option="null"><?php esc_html_e('none', 'ipanorama'); ?></option>
													<option al-repeat="theme in appData.themes" al-option="theme.id">{{theme.title}}</option>
												</select>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a widget for control elements', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Widget', 'ipanorama'); ?></div>
												<div class="ipanorama-input-group">
													<div class="ipanorama-input-group-cell ipanorama-pinch">
														<div class="ipanorama-btn ipanorama-default ipanorama-no-br" al-if="appData.ui.activeWidget && appData.ui.activeWidget.config" al-on.click="appData.fn.setupWidgetSettings(appData)" title="<?php esc_html_e('Setup widget settings', 'ipanorama'); ?>"><span><i class="xfa fa-cog"></i></span></div>
													</div>
													<div class="ipanorama-input-group-cell">
														<select class="ipanorama-select ipanorama-capitalize ipanorama-long ipanorama-no-blr" al-select="appData.config.widget.name">
															<option al-option="null"><?php esc_html_e('none', 'ipanorama'); ?></option>
															<option al-repeat="widget in appData.widgets" al-option="widget.id">{{widget.title}}</option>
														</select>
													</div>
												</div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a scene transition effect', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Scene transition effect', 'ipanorama'); ?></div>
												<div class="ipanorama-input-group">
													<div class="ipanorama-input-group-cell ipanorama-pinch">
														<div class="ipanorama-btn ipanorama-default ipanorama-no-br" al-if="appData.ui.activeTransition && appData.ui.activeTransition.config" al-on.click="appData.fn.setupSceneTransitionSettings(appData)" title="<?php esc_html_e('Setup scene transition effect settings', 'ipanorama'); ?>"><span><i class="xfa fa-cog"></i></span></div>
													</div>
													<div class="ipanorama-input-group-cell">
														<select class="ipanorama-select ipanorama-capitalize ipanorama-long ipanorama-no-blr" al-select="appData.config.sceneTransition.name">
															<option al-option="null"><?php esc_html_e('None', 'ipanorama'); ?></option>
															<option al-repeat="sceneTransition in appData.sceneTransitions" al-option="sceneTransition.id">{{sceneTransition.title}}</option>
														</select>
													</div>
												</div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a scene transition duration in ms', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Scene transition duration (ms)', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-integer="appData.config.sceneTransition.duration" placeholder="<?php esc_html_e('Default: 1500', 'ipanorama'); ?>">
											</div>
										</div>
									</div>
									<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.generalTab.container">
										<div class="ipanorama-block-header" al-on.click="appData.fn.onGeneralTab(appData,'container')">
											<div class="ipanorama-block-title"><?php esc_html_e('Container', 'ipanorama'); ?></div>
											<div class="ipanorama-block-state"></div>
										</div>
										<div class="ipanorama-block-data">
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('The container width will be auto-calculated', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Auto width', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.autoWidth"></div>
											</div>
											
											<div class="ipanorama-control" al-if="!appData.config.autoWidth">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets the container width (can be any valid CSS units, not just pixels)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Custom width', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-text="appData.config.containerWidth" placeholder="<?php esc_html_e('Default: auto', 'ipanorama'); ?>">
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('The container height will be auto-calculated', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Auto height', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.autoHeight"></div>
											</div>
											
											<div class="ipanorama-control" al-if="!appData.config.autoHeight">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets the container height (can be any valid CSS units, not just pixels)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Custom height', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-text="appData.config.containerHeight" placeholder="<?php esc_html_e('Default: auto', 'ipanorama'); ?>">
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets the inline background styles ON or OFF', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Inline background styles', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.background.inline"></div>
											</div>

                                            <div class="ipanorama-control">
                                                <div class="ipanorama-helper" title="<?php esc_html_e('Show/hide the white logo', 'ipanorama'); ?>"></div>
                                                <div class="ipanorama-label"><?php esc_html_e('Show/hide the white logo', 'ipanorama'); ?></div>
                                                <div al-toggle="appData.config.background.logo"></div>
                                            </div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Background color in hexadecimal format (#fff or #555555)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Background color', 'ipanorama'); ?></div>
												<div class="ipanorama-color" al-color="appData.config.background.color"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets a background image (jpeg or png format)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Background image', 'ipanorama'); ?></div>
												<div class="ipanorama-input-group">
													<div class="ipanorama-input-group-cell">
														<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.config.background.image.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
													</div>
													<div class="ipanorama-input-group-cell ipanorama-pinch">
														<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.config.background.image)" title="<?php esc_html_e('Select a background image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
													</div>
												</div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a size of the background image', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Background size', 'ipanorama'); ?></div>
												<div class="ipanorama-select" al-backgroundsize="appData.config.background.size"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('How the background image will be repeated of both horizontal or vertical', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Background repeat', 'ipanorama'); ?></div>
												<div class="ipanorama-select" al-backgroundrepeat="appData.config.background.repeat"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets a starting position of the background image', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Background position', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-text="appData.config.background.position" placeholder="<?php esc_html_e('Example: 50% 50%', 'ipanorama'); ?>">
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets additional css classes to the container', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Extra CSS classes', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-text="appData.config.class">
											</div>
										</div>
									</div>
									<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.generalTab.audio">
										<div class="ipanorama-block-header" al-on.click="appData.fn.onGeneralTab(appData,'audio')">
											<div class="ipanorama-block-title"><?php esc_html_e('Audio', 'ipanorama'); ?></div>
											<div class="ipanorama-block-state"></div>
										</div>
										<div class="ipanorama-block-data">
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable audio', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Enable audio', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.audio.enabled"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable HTML5 audio. This should be used for large audio files so that you don\'t have to wait for the full file to be downloaded and decoded before playing', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('HTML5 type', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.audio.html5"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable automatically start playback when the plugin is loaded', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Autoplay', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.audio.autoplay"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Automatically loop the sound forever', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Loop', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.audio.loop"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable HTML5 audio. This should be used for large audio files so that you don\'t have to wait for the full file to be downloaded and decoded before playing', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Mute', 'ipanorama'); ?></div>
												<div al-toggle="appData.config.audio.mute"></div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets audio volume (min = 0, max = 1)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Volume', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-float="appData.config.audio.volume" data-min="0" data-max="1" placeholder="<?php esc_html_e('Default 0.6', 'ipanorama'); ?>">
											</div>
										</div>
									</div>
									<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.generalTab.special">
										<div class="ipanorama-block-header" al-on.click="appData.fn.onGeneralTab(appData,'special')">
											<div class="ipanorama-block-title"><?php esc_html_e('Special', 'ipanorama'); ?></div>
											<div class="ipanorama-block-state"></div>
										</div>
										<div class="ipanorama-block-data">
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets a preload image for all scenes to get faster pano loading experience (jpeg or png format)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Preload image', 'ipanorama'); ?></div>
												<div class="ipanorama-input-group">
													<div class="ipanorama-input-group-cell">
														<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.config.preload.image.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
													</div>
													<div class="ipanorama-input-group-cell ipanorama-pinch">
														<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.config.preload.image)" title="<?php esc_html_e('Select a preload image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
													</div>
												</div>
												<div class="ipanorama-input-group">
													<div class="ipanorama-input-group-cell ipanorama-pinch">
														<div al-checkbox="appData.config.preload.noProgressEvent"></div>
													</div>
													<div class="ipanorama-input-group-cell">
														<?php esc_html_e('The plugin does not fire the progress event when loading the original image', 'ipanorama'); ?>
													</div>
												</div>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets the time period after which start loading the original image', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Preload timeout [ms]', 'ipanorama'); ?></div>
												<input class="ipanorama-number" al-integer="appData.config.preload.timeout" placeholder="<?php esc_html_e('Default: 1000', 'ipanorama'); ?>">
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a viewer mode (normal or stereo)', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('Viewer mode', 'ipanorama'); ?></div>
												<select class="ipanorama-select ipanorama-capitalize" al-select="appData.config.viewerMode">
													<option al-option="'normal'"><?php esc_html_e('Normal', 'ipanorama'); ?></option>
													<option al-option="'stereo'"><?php esc_html_e('Stereo', 'ipanorama'); ?></option>
												</select>
											</div>
											
											<div class="ipanorama-control">
												<div class="ipanorama-helper" title="<?php esc_html_e('Sets the last part of the virtual tour URL', 'ipanorama'); ?>"></div>
												<div class="ipanorama-label"><?php esc_html_e('URL Slug', 'ipanorama'); ?></div>
												<input class="ipanorama-text" type="text" al-text="appData.config.slug" data-regex="^([a-z0-9_-]+)$">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.scenes">
						<div class="ipanorama-stage">
							<div class="ipanorama-sidebar-panel" al-attr.class.ipanorama-hidden="!appData.ui.sidebar" al-style.width="appData.ui.sidebarWidth">
								<div class="ipanorama-data">
									<div class="ipanorama-scenes-wrap">
										<div class="ipanorama-scenes-toolbar">
											<div class="ipanorama-left-panel">
												<i class="xfa fa-plus-circle" al-on.click="appData.fn.addScene(appData)" title="<?php esc_html_e('add scene', 'ipanorama'); ?>"></i>
												<span al-if="appData.ui.activeScene != null">
												<i class="xfa fa-clone" al-on.click="appData.fn.copyScene(appData)" title="<?php esc_html_e('copy', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up fa-top" al-on.click="appData.fn.updownScene(appData, 'start')" title="<?php esc_html_e('move to the start', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up" al-on.click="appData.fn.updownScene(appData, 'up')" title="<?php esc_html_e('move up', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down" al-on.click="appData.fn.updownScene(appData, 'down')" title="<?php esc_html_e('move down', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down fa-bottom" al-on.click="appData.fn.updownScene(appData, 'end')" title="<?php esc_html_e('move to the end', 'ipanorama'); ?>"></i>
												</span>
											</div>
											<div class="ipanorama-right-panel">
												<i class="xfa fa-trash-o fa-color-red" al-if="appData.ui.activeScene != null" al-on.click="appData.fn.deleteScene(appData)" title="<?php esc_html_e('delete', 'ipanorama'); ?>"></i>
											</div>
										</div>
										<div class="ipanorama-scenes-list">
											<div class="ipanorama-scene"
											 tabindex="0"
											 al-attr.class.ipanorama-active="appData.fn.isSceneActive(appData, scene)"
											 al-on.click="appData.fn.onSceneItemClick(appData, scene)"
											 al-on.keydown="appData.fn.onSceneKeyDown(appData, scene, $event)"
											 al-repeat="scene in appData.config.scenes"
											 >
												<i class="xfa fa-cube"></i>
												<div class="ipanorama-label">{{scene.title.data ? scene.title.data : '...'}}</div>
												<div class="ipanorama-actions">
													<i class="ipanorama-btn xfa" al-attr.class.fa-toggle-on="scene.visible" al-attr.class.fa-toggle-off="!scene.visible" al-on.click="appData.fn.toggleSceneVisible(appData, scene)" title="<?php esc_html_e('show/hide', 'ipanorama'); ?>"></i>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="ipanorama-sidebar-resizer" al-on.mousedown="appData.fn.onSidebarResizeStart(appData, $event)">
									<div class="ipanorama-sidebar-hide" al-on.click="appData.fn.toggleSidebarPanel(appData)">
										<i class="xfa fa-chevron-right" al-if="!appData.ui.sidebar"></i>
										<i class="xfa fa-chevron-left" al-if="appData.ui.sidebar"></i>
									</div>
								</div>
							</div>
							<div class="ipanorama-main-panel">
								<div class="ipanorama-tabs ipanorama-clear-fix">
									<div class="ipanorama-tab ipanorama-active"><?php esc_html_e('Scene', 'ipanorama'); ?></div>
								</div>
								<div class="ipanorama-data ipanorama-active">
									<div al-if="appData.ui.activeScene == null">
										<div class="ipanorama-control">
											<div class="ipanorama-info"><?php esc_html_e('Please, select a scene to view settings', 'ipanorama'); ?></div>
										</div>
									</div>
									<div al-if="appData.ui.activeScene != null">
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.sceneSections.general">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onSceneSection(appData,'general')">
												<div class="ipanorama-block-title"><?php esc_html_e('General', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable scene', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Enable scene', 'ipanorama'); ?></div>
													<div al-toggle="appData.ui.activeScene.visible"></div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets a scene id (allow numbers, chars & specials: "_","-")', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Id', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-uuid="appData.ui.activeScene.id">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.generateId(appData, appData.rootScope, appData.ui.activeScene)" title="<?php esc_html_e('Generate a new ID', 'ipanorama'); ?>"><span><i class="xfa fa-refresh"></i></span></div>
																</div>
															</div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets a scene type', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Type', 'ipanorama'); ?></div>
															<div class="ipanorama-select ipanorama-long" al-scenetype="appData.ui.activeScene.type"></div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a scene title', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Title', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-br" al-on.click="appData.ui.activeScene.title.active = !appData.ui.activeScene.title.active;" title="<?php esc_html_e('Enable/disable title', 'ipanorama'); ?>"><span><i class="xfa" al-attr.class.fa-check="appData.ui.activeScene.title.active"></i></span></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-blr" type="text" al-text="appData.ui.activeScene.title.data">
														</div>
													</div>
												</div>
												
												<div al-if="appData.ui.activeScene.type != 'cubesix' && appData.ui.activeScene.type != 'gsv'">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a scene image (jpeg or png format)', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Image', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.main.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.main)" title="<?php esc_html_e('Select a scene image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
														</div>
													</div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div al-checkbox="appData.ui.activeScene.preload.active"></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<?php esc_html_e('Generates the scene preload image to get faster pano loading experience', 'ipanorama'); ?>
														</div>
													</div>
												</div>
												</div>
												
												<div al-if="appData.ui.activeScene.type == 'cubesix'">
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('Front image', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.front.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.front)" title="<?php esc_html_e('Select an image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
																</div>
															</div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-label"><?php esc_html_e('Back image', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.back.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.back)" title="<?php esc_html_e('Select an image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
																</div>
															</div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('Left image', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.left.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.left)" title="<?php esc_html_e('Select an image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
																</div>
															</div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-label"><?php esc_html_e('Right image', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.right.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.right)" title="<?php esc_html_e('Select an image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
																</div>
															</div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('Top image', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.top.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.top)" title="<?php esc_html_e('Select an image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
																</div>
															</div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-label"><?php esc_html_e('Bottom image', 'ipanorama'); ?></div>
															<div class="ipanorama-input-group ipanorama-long">
																<div class="ipanorama-input-group-cell">
																	<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.bottom.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
																</div>
																<div class="ipanorama-input-group-cell ipanorama-pinch">
																	<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.bottom)" title="<?php esc_html_e('Select an image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
																</div>
															</div>
														</div>
													</div>
												</div>
												</div>
												
												<div al-if="appData.ui.activeScene.type == 'gsv'">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a pano id for Google Street View', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Pano Id', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch ipanorama-rgap">
															<div class="ipanorama-btn ipanorama-default" al-on.click="appData.fn.toggleGSVSourceType(appData,'pano')" title="<?php esc_html_e('Use the pano id', 'ipanorama'); ?>"><span><i class="xfa" al-attr.class.fa-check="appData.ui.activeScene.gsv.pano.active"></i></span></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-b-radius" type="text" al-text="appData.ui.activeScene.gsv.pano.id" placeholder="<?php esc_html_e('Type a pano id', 'ipanorama'); ?>">
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a latitude & longitude for Google Street View', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Latitude & Longitude', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch ipanorama-rgap">
															<div class="ipanorama-btn ipanorama-default" al-on.click="appData.fn.toggleGSVSourceType(appData,'location')" title="<?php esc_html_e('Use the latitude & longitude', 'ipanorama'); ?>"><span><i class="xfa" al-attr.class.fa-check="appData.ui.activeScene.gsv.location.active"></i></span></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<input class="ipanorama-text ipanorama-long ipanorama-no-b-radius" type="text" al-text="appData.ui.activeScene.gsv.location.lat" placeholder="<?php esc_html_e('Type a latitude', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-b-radius" type="text" al-text="appData.ui.activeScene.gsv.location.lng" placeholder="<?php esc_html_e('Type a longitude', 'ipanorama'); ?>">
														</div>
													</div>
												</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets the camera’s starting lookAt position', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Camera lookAt', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch ipanorama-rgap">
															<div class="ipanorama-btn ipanorama-default" al-on.click="appData.fn.setCameraLookAt(appData)" title="<?php esc_html_e('Look at', 'ipanorama'); ?>"><span><i class="xfa fa-crosshairs"></i></span></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<input class="ipanorama-text ipanorama-long ipanorama-no-b-radius" type="text" al-float="appData.ui.activeScene.camera.lookAt.yaw" placeholder="<?php esc_html_e('yaw', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-b-radius" type="text" al-float="appData.ui.activeScene.camera.lookAt.pitch" placeholder="<?php esc_html_e('pitch', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch ipanorama-lgap">
															<div class="ipanorama-btn ipanorama-default" al-on.click="appData.fn.getCameraLookAt(appData)" title="<?php esc_html_e('Get yaw & pitch values from the current scene', 'ipanorama'); ?>"><span><i class="xfa fa-clone"></i></span></div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets the camera’s starting zoom (fov) value', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Camera zoom (fov)', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch ipanorama-rgap">
															<div class="ipanorama-btn ipanorama-default" al-on.click="appData.fn.setCameraZoom(appData)" title="<?php esc_html_e('Zoom', 'ipanorama'); ?>"><span><i class="xfa fa-crosshairs"></i></span></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-b-radius" type="text" al-float="appData.ui.activeScene.camera.zoom" placeholder="<?php esc_html_e('default', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch ipanorama-lgap">
															<div class="ipanorama-btn ipanorama-default" al-on.click="appData.fn.getCameraZoom(appData)" title="<?php esc_html_e('Get a zoom value from the current scene camera location', 'ipanorama'); ?>"><span><i class="xfa fa-clone"></i></span></div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Saving the camera position between scene changing', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Save camera position', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.camera.save"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Shared the camera position between same type scenes', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Shared camera position', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.camera.shared"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.sceneSections.data">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onSceneSection(appData,'data')">
												<div class="ipanorama-block-title"><?php esc_html_e('Data', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Adds a specific string data to the scene, we can use it in our custom code later', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('User data', 'ipanorama'); ?></div>
													<textarea class="ipanorama-long" al-textarea="appData.ui.activeScene.userData" placeholder="<?php esc_html_e('Type any string data; it can be JSON format as an example. You can use this data inside the custom code later.', 'ipanorama'); ?>"></textarea>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.sceneSections.additional">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onSceneSection(appData,'additional')">
												<div class="ipanorama-block-title"><?php esc_html_e('Additional', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a scene thumb image (jpeg or png format)', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Thumb image', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.images.thumb.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeScene.images.thumb)" title="<?php esc_html_e('Select a scene thumb image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable compass', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Compass', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.compass.enabled"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap ipanorama-rgap">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets the offset, in degrees, of the center of the panorama from North', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('North offset', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long" type="text" al-integer="appData.ui.activeScene.compass.northOffset" placeholder="<?php esc_html_e('Default 0', 'ipanorama'); ?>">
														</div>
													</div>
												</div>
												
												<!--
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets an audio stream to a scene', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Audio file', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeScene.audio.url" placeholder="<?php esc_html_e('Select audio', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectAudio(appData, appData.rootScope, appData.ui.activeScene.audio)" title="<?php esc_html_e('Select audio', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Autoplay audio after loading a scene', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Autoplay', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.audio.autoPlay"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets audio volume (min = 0, max = 1)', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Volume', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long" type="text" al-float="appData.ui.activeScene.audio.volume" data-min="0" data-max="1" placeholder="<?php esc_html_e('Default 0.5', 'ipanorama'); ?>">
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Loop audio', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Loop', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.audio.loop"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Stop previous playing audio', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Stop previous', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.audio.stopPrevious"></div>
														</div>
													</div>
												</div>
												-->
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.sceneSections.actions">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onSceneSection(appData,'actions')">
												<div class="ipanorama-block-title"><?php esc_html_e('Actions', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable auto rotate', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Auto rotate', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.autoRotate.enabled"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap ipanorama-rgap">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets the delay, in milliseconds, to start automatically rotating after user activity ceases', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Inactivity delay', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long" type="text" al-integer="appData.ui.activeScene.autoRotate.inactivityDelay" placeholder="<?php esc_html_e('Default 3000', 'ipanorama'); ?>">
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets auto rotate speed along X axis', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Speed X', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long" type="text" al-float="appData.ui.activeScene.autoRotate.speedX" placeholder="<?php esc_html_e('Default 0.5', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets auto rotate speed along Y axis', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Speed Y', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long ipanorama-can-lock" type="text" al-float="appData.ui.activeScene.autoRotate.speedY" placeholder="<?php esc_html_e('Default 0', 'ipanorama'); ?>" al-attr.readonly="appData.ui.activeScene.type == 'flat'">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets auto rotate speed along Z axis', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Speed Z', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long ipanorama-can-lock" type="text" al-float="appData.ui.activeScene.autoRotate.speedZ" placeholder="<?php esc_html_e('Default 0', 'ipanorama'); ?>" al-attr.readonly="appData.ui.activeScene.type != 'littleplanet'">
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable zoom', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('No zoom', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.noZoom"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable rotate', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('No rotate', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.noRotate"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable keyboard navigation', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('No keys', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeScene.noKeys"></div>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control" al-if="appData.ui.activeScene.type != 'littleplanet' && appData.ui.activeScene.type != 'flat'">
													<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable gyroscope', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('No gyroscope', 'ipanorama'); ?></div>
													<div al-toggle="appData.ui.activeScene.noGyroscope"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.markers">
						<div class="ipanorama-stage">
							<div class="ipanorama-sidebar-panel" al-attr.class.ipanorama-hidden="!appData.ui.sidebar" al-style.width="appData.ui.sidebarWidth">
								<div class="ipanorama-data" al-if="appData.ui.activeScene != null">
									<div class="ipanorama-markers-wrap">
										<div class="ipanorama-markers-toolbar">
											<div class="ipanorama-left-panel">
												<i class="xfa fa-plus-circle" al-on.click="appData.fn.addMarker(appData)" title="<?php esc_html_e('add marker', 'ipanorama'); ?>"></i>
												<span al-if="appData.ui.activeMarker != null">
												<i class="xfa fa-clone" al-on.click="appData.fn.copyMarker(appData)" title="<?php esc_html_e('copy', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up fa-top" al-on.click="appData.fn.updownMarker(appData, 'start')" title="<?php esc_html_e('move to the start', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up" al-on.click="appData.fn.updownMarker(appData, 'up')" title="<?php esc_html_e('move up', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down" al-on.click="appData.fn.updownMarker(appData, 'down')" title="<?php esc_html_e('move down', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down fa-bottom" al-on.click="appData.fn.updownMarker(appData, 'end')" title="<?php esc_html_e('move to the end', 'ipanorama'); ?>"></i>
												</span>
											</div>
											<div class="ipanorama-right-panel">
												<i class="xfa fa-trash-o fa-color-red" al-if="appData.ui.activeMarker != null" al-on.click="appData.fn.deleteMarker(appData)" title="<?php esc_html_e('delete', 'ipanorama'); ?>"></i>
											</div>
										</div>
										<div class="ipanorama-markers-list">
											<div class="ipanorama-marker"
											 al-attr.class.ipanorama-active="appData.fn.isMarkerActive(appData, marker)"
											 al-on.click="appData.fn.onMarkerItemClick(appData, marker)"
											 al-repeat="marker in appData.ui.activeScene.markers"
											 >
												<i class="ipanorama-btn xfa fa-thumb-tack" al-on.click="appData.fn.selectMarker(appData, marker, true)" title="<?php esc_html_e('look at', 'ipanorama'); ?>"></i>
												<div class="ipanorama-label">{{marker.title ? marker.title : '...'}}</div>
												<div class="ipanorama-actions">
													<i class="xfa fa-comment" al-if="marker.tooltip.active" title="<?php esc_html_e('tooltip enabled', 'ipanorama'); ?>"></i>
													<i class="xfa fa-address-card-o" al-if="marker.popover.active" title="<?php esc_html_e('popover enabled', 'ipanorama'); ?>"></i>
													<i class="xfa fa-external-link-square" al-if="marker.linkSceneGuid != null" title="<?php esc_html_e('linked to a scene', 'ipanorama'); ?>"></i>
													<i class="ipanorama-btn xfa fa-paint-brush" al-on.click="appData.fn.editMarker(appData, marker)" title="<?php esc_html_e('edit custom style', 'ipanorama'); ?>" al-if="marker.view.active"></i>
													<i class="ipanorama-btn xfa" al-attr.class.fa-toggle-on="marker.visible" al-attr.class.fa-toggle-off="!marker.visible" al-on.click="appData.fn.toggleMarkerVisible(appData, marker)" title="<?php esc_html_e('show/hide', 'ipanorama'); ?>"></i>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="ipanorama-sidebar-resizer" al-on.mousedown="appData.fn.onSidebarResizeStart(appData, $event)">
									<div class="ipanorama-sidebar-hide" al-on.click="appData.fn.toggleSidebarPanel(appData)">
										<i class="ipanorama-icon ipanorama-icon-next" al-if="!appData.ui.sidebar"></i>
										<i class="ipanorama-icon ipanorama-icon-prev" al-if="appData.ui.sidebar"></i>
									</div>
								</div>
							</div>
							<div class="ipanorama-main-panel" id="ipanorama-markers-main-panel">
								<div class="ipanorama-tabs ipanorama-clear-fix">
									<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.markerTabs.marker" al-on.click="appData.fn.onMarkerTab(appData, 'marker')"><?php esc_html_e('Marker', 'ipanorama'); ?></div>
									<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.markerTabs.tooltip" al-on.click="appData.fn.onMarkerTab(appData, 'tooltip')"><?php esc_html_e('Tooltip', 'ipanorama'); ?><div class="ipanorama-status" al-if="appData.ui.activeMarker != null && appData.ui.activeMarker.tooltip.active"></div></div>
									<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.markerTabs.popover" al-on.click="appData.fn.onMarkerTab(appData, 'popover')"><?php esc_html_e('Popover', 'ipanorama'); ?><div class="ipanorama-status" al-if="appData.ui.activeMarker != null && appData.ui.activeMarker.popover.active"></div></div>
								</div>
								<div class="ipanorama-data" al-attr.class.ipanorama-active="appData.ui.markerTabs.marker">
									<div al-if="appData.ui.activeMarker == null">
										<div class="ipanorama-control">
											<div class="ipanorama-info"><?php esc_html_e('Please, select a marker to view settings', 'ipanorama'); ?></div>
										</div>
									</div>
									<div al-if="appData.ui.activeMarker != null">
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.markerSections.general">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onMarkerSection(appData,'general')">
												<div class="ipanorama-block-title"><?php esc_html_e('General', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets the marker title', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Title', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long" type="text" al-text="appData.ui.activeMarker.title">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets the marker ID', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Id', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long ipanorama-can-lock" type="text" al-text="appData.ui.activeMarker.id" readonly="readonly">
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" al-if="appData.ui.activeScene.type != 'flat'" title="<?php esc_html_e('Sets the marker’s yaw position in degrees', 'ipanorama'); ?>"></div>
															<div class="ipanorama-helper" al-if="appData.ui.activeScene.type == 'flat'" title="<?php esc_html_e('Sets the marker’s "x" position in px', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label" al-if="appData.ui.activeScene.type != 'flat'"><?php esc_html_e('Yaw', 'ipanorama'); ?></div>
															<div class="ipanorama-label" al-if="appData.ui.activeScene.type == 'flat'"><?php esc_html_e('X', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeMarker.yaw">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" al-if="appData.ui.activeScene.type != 'flat'" title="<?php esc_html_e('Sets the marker’s pitch position in degrees', 'ipanorama'); ?>"></div>
															<div class="ipanorama-helper" al-if="appData.ui.activeScene.type == 'flat'" title="<?php esc_html_e('Sets the marker’s "y" position in px', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label" al-if="appData.ui.activeScene.type != 'flat'"><?php esc_html_e('Pitch', 'ipanorama'); ?></div>
															<div class="ipanorama-label" al-if="appData.ui.activeScene.type == 'flat'"><?php esc_html_e('Y', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeMarker.pitch">
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a marker image (jpeg or png format)', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Image', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeMarker.image.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeMarker.image)" title="<?php esc_html_e('Select a scene thumb image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.markerSections.view">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onMarkerSection(appData,'view')">
												<div class="ipanorama-block-title"><?php esc_html_e('Custom style', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable custom style for the selected marker', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Enable custom style', 'ipanorama'); ?></div>
													<div al-toggle="appData.ui.activeMarker.view.active"></div>
												</div>
												<div class="ipanorama-control">
													<div class="ipanorama-marker-canvas-wrap">
														<div class="ipanorama-btn" al-on.click="appData.fn.editMarker(appData, appData.ui.activeMarker)" title="<?php esc_html_e('edit custom style', 'ipanorama'); ?>"><i class="xfa fa-paint-brush"></i></div>
														<div class="ipanorama-marker-canvas">
															<div class="ipanorama-marker-wrap">
																<div id="ipanorama-marker-view" class="ipanorama-marker"
																	 al-style.width="appData.fn.getMarkerStyle(appData, appData.ui.activeMarker, 'width')"
																	 al-style.height="appData.fn.getMarkerStyle(appData, appData.ui.activeMarker, 'height')"
																>
																	<div class="ipanorama-marker-lbl"
																		 al-style.color="appData.fn.getIconStyle(appData, appData.ui.activeMarker.view.icon, 'color')"
																		 al-style.font-size="appData.fn.getIconStyle(appData, appData.ui.activeMarker.view.icon, 'font-size')"
																	>
																		<div class="ipanorama-marker-ico" al-if="appData.ui.activeMarker.view.icon.name"><i class="xfa {{appData.ui.activeMarker.view.icon.name}}"></i></div>
																		<div class="ipanorama-marker-txt" al-if="appData.ui.activeMarker.view.icon.text">{{appData.ui.activeMarker.view.icon.text}}</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.markerSections.data">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onMarkerSection(appData,'data')">
												<div class="ipanorama-block-title"><?php esc_html_e('Data', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Adds a specific string data to the marker, if we want to use it in custom code later', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('User data', 'ipanorama'); ?></div>
													<textarea class="ipanorama-long" al-textarea="appData.ui.activeMarker.userData" placeholder="<?php esc_html_e('Type any string data; it can be JSON format as an example. You can use this data inside the custom code later.', 'ipanorama'); ?>"></textarea>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.markerSections.additional">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onMarkerSection(appData,'additional')">
												<div class="ipanorama-block-title"><?php esc_html_e('Additional', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets additional CSS classes to the marker', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Extra CSS classes', 'ipanorama'); ?></div>
													<input class="ipanorama-number ipanorama-long" type="text" al-text="appData.ui.activeMarker.class">
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.markerSections.actions">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onMarkerSection(appData,'actions')">
												<div class="ipanorama-block-title"><?php esc_html_e('Actions', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Adds a specific url to the marker', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Go to an URL', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<input class="ipanorama-text ipanorama-long" type="text" al-text="appData.ui.activeMarker.link" placeholder="<?php esc_html_e('URL', 'ipanorama'); ?>">
													</div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div al-checkbox="appData.ui.activeMarker.linkNewWindow"></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<?php esc_html_e('Open url in a new window', 'ipanorama'); ?>
														</div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Specifies the scene ID to be switched to', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Go to a scene', 'ipanorama'); ?></div>
													<select class="ipanorama-select ipanorama-long" al-sceneguid="appData.ui.activeMarker.linkSceneGuid">
														<option value="none">none</option>
														<option al-repeat="scene in appData.config.scenes" value="{{scene.guid}}">{{(scene.title.data ? scene.title.data : scene.id)}}</option>
													</select>
												</div>
												
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Auto focus on the marker after the click event', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Auto focus', 'ipanorama'); ?></div>
															<div al-toggle="appData.ui.activeMarker.autoFocus.active"></div>
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets duration for auto focus action', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Duration [ms]', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long" al-integer="appData.ui.activeMarker.autoFocus.duration" placeholder="<?php esc_html_e('Default: 700', 'ipanorama'); ?>">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="ipanorama-data" al-attr.class.ipanorama-active="appData.ui.markerTabs.tooltip">
									<div class="ipanorama-data-block" al-attr.class.ipanorama-active="appData.ui.activeMarker == null">
										<div class="ipanorama-control">
											<div class="ipanorama-info"><?php esc_html_e('Please, select a marker to view settings', 'ipanorama'); ?></div>
										</div>
									</div>
									<div class="ipanorama-data-block" al-attr.class.ipanorama-active="appData.ui.activeMarker != null">
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.tooltipSections.general">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onTooltipSection(appData,'general')">
												<div class="ipanorama-block-title"><?php esc_html_e('General', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div al-if="appData.ui.activeMarker != null">
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable tooltip for the selected marker', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Enable tooltip', 'ipanorama'); ?></div>
														<div al-toggle="appData.ui.activeMarker.tooltip.active"></div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<?php
														$settings = array(
															'tinymce' => true,
															'textarea_name' => 'ipanorama-tooltip-text',
															'wpautop' => false,
															'editor_height' => 200, // In pixels, takes precedence and has no default value
															'drag_drop_upload' => true,
															'media_buttons' => true,
															'teeny' => true,
															'quicktags' => true
														);
														wp_editor('','ipanorama-tooltip-editor', $settings);
													?>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.tooltipSections.additional">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onTooltipSection(appData,'additional')">
												<div class="ipanorama-block-title"><?php esc_html_e('Additional', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div al-if="appData.ui.activeMarker != null">
													<div class="ipanorama-control">
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a tooltip event trigger', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Trigger', 'ipanorama'); ?></div>
																<div class="ipanorama-select ipanorama-long" al-tooltiptrigger="appData.ui.activeMarker.tooltip.trigger"></div>
															</div>
															<div class="ipanorama-input-group-cell ipanorama-lgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a tooltip placement', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Placement', 'ipanorama'); ?></div>
																<div class="ipanorama-select ipanorama-long" al-tooltipplacement="appData.ui.activeMarker.tooltip.placement"></div>
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Sets tooltip offset', 'ipanorama'); ?>"></div>
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap">
																<div class="ipanorama-label"><?php esc_html_e('Offset X [px]', 'ipanorama'); ?></div>
																<input class="ipanorama-number ipanorama-long" al-integer="appData.ui.activeMarker.tooltip.offset.x">
															</div>
															<div class="ipanorama-input-group-cell ipanorama-lgap">
																<div class="ipanorama-label"><?php esc_html_e('Offset Y [px]', 'ipanorama'); ?></div>
																<input class="ipanorama-number ipanorama-long" al-integer="appData.ui.activeMarker.tooltip.offset.y">
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control" al-if="appData.ui.activeMarker.tooltip.trigger == 'hover' || appData.ui.activeMarker.tooltip.trigger == 'clickbody'">
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap" al-if="appData.ui.activeMarker.tooltip.trigger != 'clickbody'">
																<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable tooltip following the cursor as you hover over the marker', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Follow the cursor', 'ipanorama'); ?></div>
																<div al-toggle="appData.ui.activeMarker.tooltip.followCursor"></div>
															</div>
															<div class="ipanorama-input-group-cell">
																<div class="ipanorama-helper" title="<?php esc_html_e('The tooltip won\'t hide when you hover over or click on them', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Interactive', 'ipanorama'); ?></div>
																<div al-toggle="appData.ui.activeMarker.tooltip.interactive"></div>
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Sets a tooltip width (auto from css or px)', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Width', 'ipanorama'); ?> {{appData.ui.activeMarker.tooltip.autoWidth ? '[auto]' : '[px]'}}</div>
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-pinch">
																<div class="ipanorama-btn ipanorama-default ipanorama-no-br" al-on.click="appData.ui.activeMarker.tooltip.autoWidth = !appData.ui.activeMarker.tooltip.autoWidth;" title="<?php esc_html_e('Enable/disable custom width in px', 'ipanorama'); ?>"><span><i class="xfa" al-attr.class.fa-check="!appData.ui.activeMarker.tooltip.autoWidth"></i></span></div>
															</div>
															<div class="ipanorama-input-group-cell">
																<input class="ipanorama-number ipanorama-long ipanorama-no-blr ipanorama-can-lock" al-integer="appData.ui.activeMarker.tooltip.width" al-attr.readonly="appData.ui.activeMarker.tooltip.autoWidth">
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Sets additional CSS classes to the tooltip', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Extra CSS classes', 'ipanorama'); ?></div>
														<input class="ipanorama-number ipanorama-long" type="text" al-text="appData.ui.activeMarker.tooltip.class">
													</div>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.tooltipSections.actions">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onTooltipSection(appData,'actions')">
												<div class="ipanorama-block-title"><?php esc_html_e('Actions', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div al-if="appData.ui.activeMarker != null">
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('The tooltip will be shown immediately when the scene is ready', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Show when the scene is ready', 'ipanorama'); ?></div>
														<div al-toggle="appData.ui.activeMarker.tooltip.showOnInit"></div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Select a show animation effect for the tooltip from the list or write your own', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Show animation', 'ipanorama'); ?></div>
																<div class="ipanorama-input-group ipanorama-long">
																	<div class="ipanorama-input-group-cell">
																		<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeMarker.tooltip.showAnimation">
																	</div>
																	<div class="ipanorama-input-group-cell ipanorama-pinch">
																		<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectShowAnimation(appData, appData.ui.activeMarker.tooltip)" title="<?php esc_html_e('Select an effect', 'ipanorama'); ?>"><span><i class="xfa fa-magic"></i></span></div>
																	</div>
																</div>
															</div>
															<div class="ipanorama-input-group-cell ipanorama-lgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Select a hide animation effect for the tooltip from the list or write your own', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Hide animation', 'ipanorama'); ?></div>
																<div class="ipanorama-input-group ipanorama-long">
																	<div class="ipanorama-input-group-cell">
																		<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeMarker.tooltip.hideAnimation">
																	</div>
																	<div class="ipanorama-input-group-cell ipanorama-pinch">
																		<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectHideAnimation(appData, appData.ui.activeMarker.tooltip)" title="<?php esc_html_e('Select an effect', 'ipanorama'); ?>"><span><i class="xfa fa-magic"></i></span></div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Sets animation duration for show and hide effects', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Duration [ms]', 'ipanorama'); ?></div>
														<input class="ipanorama-number ipanorama-long" al-integer="appData.ui.activeMarker.tooltip.duration" placeholder="<?php esc_html_e('Default: 500', 'ipanorama'); ?>">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="ipanorama-data" al-attr.class.ipanorama-active="appData.ui.markerTabs.popover">
									<div class="ipanorama-data-block" al-attr.class.ipanorama-active="appData.ui.activeMarker == null">
										<div class="ipanorama-control">
											<div class="ipanorama-info"><?php esc_html_e('Please, select a marker to view settings', 'ipanorama'); ?></div>
										</div>
									</div>
									<div class="ipanorama-data-block" al-attr.class.ipanorama-active="appData.ui.activeMarker != null">
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.popoverSections.general">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onPopoverSection(appData,'general')">
												<div class="ipanorama-block-title"><?php esc_html_e('General', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div al-if="appData.ui.activeMarker != null">
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable popover for the selected marker', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Enable popover', 'ipanorama'); ?></div>
														<div al-toggle="appData.ui.activeMarker.popover.active"></div>
													</div>
												</div>
												
												<div class="ipanorama-control">
													<?php
														$settings = array(
															'tinymce' => true,
															'media_buttons' => true,
															'textarea_name' => 'ipanorama-popover-text',
															'textarea_rows' => get_option('default_post_edit_rows', 10), // This is equivalent to rows="" in HTML
															'tabindex' => '',
															'editor_css' => '', //  additional styles for Visual and Text editor,
															'editor_class' => '', // sdditional classes to be added to the editor
															'wpautop' => false,
															'editor_height' => 200, // In pixels, takes precedence and has no default value
															'drag_drop_upload' => true,
															'teeny' => true,
															'quicktags' => true
														);
														wp_editor('','ipanorama-popover-editor', $settings);
													?>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.popoverSections.additional">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onPopoverSection(appData,'additional')">
												<div class="ipanorama-block-title"><?php esc_html_e('Additional', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div al-if="appData.ui.activeMarker != null">
													<div class="ipanorama-control">
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a popover desktop type', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Desktop type', 'ipanorama'); ?></div>
																<div class="ipanorama-select ipanorama-long" al-popovertype="appData.ui.activeMarker.popover.type"></div>
															</div>
															<div class="ipanorama-input-group-cell ipanorama-lgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a popover mobile type', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Mobile type', 'ipanorama'); ?></div>
																<div class="ipanorama-select ipanorama-long" al-popovertype="appData.ui.activeMarker.popover.mobileType"></div>
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Specifies a popover event trigger', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Trigger', 'ipanorama'); ?></div>
														<div class="ipanorama-select ipanorama-long" al-popovertrigger="appData.ui.activeMarker.popover.trigger"></div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Sets additional CSS classes to the popover', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Extra CSS classes', 'ipanorama'); ?></div>
														<input class="ipanorama-number ipanorama-long" type="text" al-text="appData.ui.activeMarker.popover.class">
													</div>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.popoverSections.actions">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onPopoverSection(appData,'actions')">
												<div class="ipanorama-block-title"><?php esc_html_e('Actions', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div al-if="appData.ui.activeMarker != null">
													<div class="ipanorama-control">
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('The popover will be shown immediately when the scene is ready', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Show when the scene is ready', 'ipanorama'); ?></div>
																<div al-toggle="appData.ui.activeMarker.popover.showOnInit"></div>
															</div>
															<div class="ipanorama-input-group-cell ipanorama-lgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('The popover will be closed after the outside click event', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Close outside', 'ipanorama'); ?></div>
																<div al-toggle="appData.ui.activeMarker.popover.closeOutside"></div>
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-input-group ipanorama-long">
															<div class="ipanorama-input-group-cell ipanorama-rgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Select a show animation effect for the popover from the list or write your own', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Show animation', 'ipanorama'); ?></div>
																<div class="ipanorama-input-group ipanorama-long">
																	<div class="ipanorama-input-group-cell">
																		<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeMarker.popover.showAnimation">
																	</div>
																	<div class="ipanorama-input-group-cell ipanorama-pinch">
																		<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectShowAnimation(appData, appData.ui.activeMarker.popover)" title="<?php esc_html_e('Select an effect', 'ipanorama'); ?>"><span><i class="xfa fa-magic"></i></span></div>
																	</div>
																</div>
															</div>
															<div class="ipanorama-input-group-cell ipanorama-lgap">
																<div class="ipanorama-helper" title="<?php esc_html_e('Select a hide animation effect for the popover from the list or write your own', 'ipanorama'); ?>"></div>
																<div class="ipanorama-label"><?php esc_html_e('Hide animation', 'ipanorama'); ?></div>
																<div class="ipanorama-input-group ipanorama-long">
																	<div class="ipanorama-input-group-cell">
																		<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeMarker.popover.hideAnimation">
																	</div>
																	<div class="ipanorama-input-group-cell ipanorama-pinch">
																		<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectHideAnimation(appData, appData.ui.activeMarker.popover)" title="<?php esc_html_e('Select an effect', 'ipanorama'); ?>"><span><i class="xfa fa-magic"></i></span></div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													
													<div class="ipanorama-control">
														<div class="ipanorama-helper" title="<?php esc_html_e('Sets animation duration for show and hide effects', 'ipanorama'); ?>"></div>
														<div class="ipanorama-label"><?php esc_html_e('Duration [ms]', 'ipanorama'); ?></div>
														<input class="ipanorama-number ipanorama-long" al-integer="appData.ui.activeMarker.popover.duration" placeholder="<?php esc_html_e('Default: 500', 'ipanorama'); ?>">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.shapes">
						<div class="ipanorama-stage">
							<div class="ipanorama-sidebar-panel" al-attr.class.ipanorama-hidden="!appData.ui.sidebar" al-style.width="appData.ui.sidebarWidth">
								<div class="ipanorama-data" al-if="appData.ui.activeScene != null">
									<div class="ipanorama-shapes-wrap">
										<div class="ipanorama-shapes-toolbar">
											<div class="ipanorama-left-panel">
												<i class="xfa fa-plus-circle" al-on.click="appData.fn.addShape(appData)" title="<?php esc_html_e('add shape', 'ipanorama'); ?>"></i>
												<span al-if="appData.ui.activeShape != null">
												<i class="xfa fa-clone" al-on.click="appData.fn.copyShape(appData)" title="<?php esc_html_e('copy', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up fa-top" al-on.click="appData.fn.updownShape(appData, 'start')" title="<?php esc_html_e('move to the start', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up" al-on.click="appData.fn.updownShape(appData, 'up')" title="<?php esc_html_e('move up', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down" al-on.click="appData.fn.updownShape(appData, 'down')" title="<?php esc_html_e('move down', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down fa-bottom" al-on.click="appData.fn.updownShape(appData, 'end')" title="<?php esc_html_e('move to the end', 'ipanorama'); ?>"></i>
												
												<i class="xfa fa-long-arrow-up fa-split"></i>
												<i class="xfa fa-arrows" al-attr.class.ipanorama-active="appData.ui.transformMode == 'translate'" al-on.click="appData.fn.onTransformMode(appData, 'translate')"></i>
												<i class="xfa fa-undo" al-attr.class.ipanorama-active="appData.ui.transformMode == 'rotate'" al-on.click="appData.fn.onTransformMode(appData, 'rotate')"></i>
												</span>
											</div>
											<div class="ipanorama-right-panel">
												<i class="xfa fa-trash-o fa-color-red" al-if="appData.ui.activeShape != null" al-on.click="appData.fn.deleteShape(appData)" title="<?php esc_html_e('delete', 'ipanorama'); ?>"></i>
											</div>
										</div>
										<div class="ipanorama-shapes-list">
											<div class="ipanorama-shape"
											 al-attr.class.ipanorama-active="appData.fn.isShapeActive(appData, shape)"
											 al-on.click="appData.fn.onShapeItemClick(appData, shape)"
											 al-repeat="shape in appData.ui.activeScene.shapes"
											 >
												<i class="ipanorama-btn xfa fa-thumb-tack" al-on.click="appData.fn.selectShape(appData, shape, true)" title="<?php esc_html_e('look at', 'ipanorama'); ?>"></i>
												<div class="ipanorama-label">{{shape.title ? shape.title : '...'}}</div>
												<div class="ipanorama-actions">
													<i class="ipanorama-btn xfa" al-attr.class.fa-toggle-on="shape.visible" al-attr.class.fa-toggle-off="!shape.visible" al-on.click="appData.fn.toggleShapeVisible(appData, shape)" title="<?php esc_html_e('show/hide', 'ipanorama'); ?>"></i>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="ipanorama-sidebar-resizer" al-on.mousedown="appData.fn.onSidebarResizeStart(appData, $event)">
									<div class="ipanorama-sidebar-hide" al-on.click="appData.fn.toggleSidebarPanel(appData)">
										<i class="ipanorama-icon ipanorama-icon-next" al-if="!appData.ui.sidebar"></i>
										<i class="ipanorama-icon ipanorama-icon-prev" al-if="appData.ui.sidebar"></i>
									</div>
								</div>
							</div>
							<div class="ipanorama-main-panel" id="ipanorama-shapes-main-panel">
								<div class="ipanorama-tabs ipanorama-clear-fix">
									<div class="ipanorama-tab" al-attr.class.ipanorama-active="appData.ui.markerTabs.marker" al-on.click="appData.fn.onShapeTab(appData, 'shape')"><?php esc_html_e('Shape', 'ipanorama'); ?></div>
								</div>
								<div class="ipanorama-data" al-attr.class.ipanorama-active="appData.ui.shapeTabs.shape">
									<div al-if="appData.ui.activeShape == null">
										<div class="ipanorama-control">
											<div class="ipanorama-info"><?php esc_html_e('Please, select a shape to view settings', 'ipanorama'); ?></div>
										</div>
									</div>
									<div al-if="appData.ui.activeShape != null">
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.shapeSections.general">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onShapeSection(appData,'general')">
												<div class="ipanorama-block-title"><?php esc_html_e('General', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets a shape title', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Title', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long" type="text" al-text="appData.ui.activeShape.title">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('The shape id', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Id', 'ipanorama'); ?></div>
															<input class="ipanorama-text ipanorama-long ipanorama-can-lock" type="text" al-text="appData.ui.activeShape.id" readonly="readonly">
														</div>
													</div>
												</div>
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a shape position in 3d space', 'ipanorama'); ?>"></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('X', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.pos.x">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('Y', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.pos.y">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-label"><?php esc_html_e('Z', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.pos.z">
														</div>
													</div>
												</div>
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a shape rotation in 3d space', 'ipanorama'); ?>"></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('X (deg)', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.rotate.x">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap ipanorama-rgap">
															<div class="ipanorama-label"><?php esc_html_e('Y (deg)', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.rotate.y">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-label"><?php esc_html_e('Z (deg)', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.rotate.z">
														</div>
													</div>
												</div>
												<div class="ipanorama-control">
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-rgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets a shape width', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Width', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.width">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-lgap">
															<div class="ipanorama-helper" title="<?php esc_html_e('Sets a shape height', 'ipanorama'); ?>"></div>
															<div class="ipanorama-label"><?php esc_html_e('Height', 'ipanorama'); ?></div>
															<input class="ipanorama-number ipanorama-long ipanorama-can-lock" al-float="appData.ui.activeShape.height">
														</div>
													</div>
												</div>
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets a shape image (jpeg or png format)', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Image', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeShape.image.url" placeholder="<?php esc_html_e('Select an image', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeShape.image)" title="<?php esc_html_e('Select a shape image', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="ipanorama-block" al-attr.class.ipanorama-block-folded="appData.ui.shapeSections.data">
											<div class="ipanorama-block-header" al-on.click="appData.fn.onShapeSection(appData,'data')">
												<div class="ipanorama-block-title"><?php esc_html_e('Data', 'ipanorama'); ?></div>
												<div class="ipanorama-block-state"></div>
											</div>
											<div class="ipanorama-block-data">
												<div class="ipanorama-control">
													<div class="ipanorama-helper" title="<?php esc_html_e('Adds a specific string data to the shape, if we want to use it in custom code later', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('User data', 'ipanorama'); ?></div>
													<textarea class="ipanorama-long" al-textarea="appData.ui.activeShape.userData" placeholder="<?php esc_html_e('Type any string data; it can be JSON format as an example. You can use this data inside the custom code later.', 'ipanorama'); ?>"></textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.tracks">
						<div class="ipanorama-stage">
							<div class="ipanorama-sidebar-panel" al-attr.class.ipanorama-hidden="!appData.ui.sidebar" al-style.width="appData.ui.sidebarWidth">
								<div class="ipanorama-data">
									<div class="ipanorama-tracks-wrap">
										<div class="ipanorama-tracks-toolbar">
											<div class="ipanorama-left-panel">
												<i class="xfa fa-plus-circle" al-on.click="appData.fn.addTrack(appData)" title="<?php esc_html_e('add track', 'ipanorama'); ?>"></i>
												<span al-if="appData.ui.activeTrack != null">
												<i class="xfa fa-clone" al-on.click="appData.fn.copyTrack(appData)" title="<?php esc_html_e('copy', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up fa-top" al-on.click="appData.fn.updownTrack(appData, 'start')" title="<?php esc_html_e('move to the start', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-up" al-on.click="appData.fn.updownTrack(appData, 'up')" title="<?php esc_html_e('move up', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down" al-on.click="appData.fn.updownTrack(appData, 'down')" title="<?php esc_html_e('move down', 'ipanorama'); ?>"></i>
												<i class="xfa fa-arrow-down fa-bottom" al-on.click="appData.fn.updownTrack(appData, 'end')" title="<?php esc_html_e('move to the end', 'ipanorama'); ?>"></i>
												</span>
											</div>
											<div class="ipanorama-right-panel">
												<i class="xfa fa-trash-o fa-color-red" al-if="appData.ui.activeTrack != null" al-on.click="appData.fn.deleteTrack(appData)" title="<?php esc_html_e('delete', 'ipanorama'); ?>"></i>
											</div>
										</div>
										<div class="ipanorama-tracks-list">
											<div class="ipanorama-track"
											 tabindex="0"
											 al-attr.class.ipanorama-active="appData.fn.isTrackActive(appData, track)"
											 al-on.click="appData.fn.onTrackItemClick(appData, track)"
											 al-on.keydown="appData.fn.onTrackKeyDown(appData, track, $event)"
											 al-repeat="track in appData.config.audio.tracks"
											 >
												<i class="xfa fa-music"></i>
												<div class="ipanorama-label">{{appData.fn.getTrackTitle(appData, track)}}</div>
												<div class="ipanorama-actions">
													<i class="xfa fa-external-link-square" al-if="track.linkSceneGuid != null" title="<?php esc_html_e('linked to a scene', 'ipanorama'); ?>"></i>
													<i class="ipanorama-btn xfa" al-attr.class.fa-toggle-on="track.enabled" al-attr.class.fa-toggle-off="!track.enabled" al-on.click="appData.fn.toggleTrackState(appData, track)" title="<?php esc_html_e('enable/disable', 'ipanorama'); ?>"></i>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="ipanorama-sidebar-resizer" al-on.mousedown="appData.fn.onSidebarResizeStart(appData, $event)">
									<div class="ipanorama-sidebar-hide" al-on.click="appData.fn.toggleSidebarPanel(appData)">
										<i class="xfa fa-chevron-right" al-if="!appData.ui.sidebar"></i>
										<i class="xfa fa-chevron-left" al-if="appData.ui.sidebar"></i>
									</div>
								</div>
							</div>
							<div class="ipanorama-main-panel">
								<div class="ipanorama-tabs ipanorama-clear-fix">
									<div class="ipanorama-tab ipanorama-active"><?php esc_html_e('Audio track', 'ipanorama'); ?></div>
								</div>
								<div class="ipanorama-data ipanorama-active">
									<div al-if="appData.ui.activeTrack == null">
										<div class="ipanorama-control">
											<div class="ipanorama-info"><?php esc_html_e('Please, select a track to view settings', 'ipanorama'); ?></div>
										</div>
									</div>
									<div al-if="appData.ui.activeTrack != null">
										<div class="ipanorama-control">
											<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable track', 'ipanorama'); ?>"></div>
											<div class="ipanorama-label"><?php esc_html_e('Enable track', 'ipanorama'); ?></div>
											<div al-toggle="appData.ui.activeTrack.enabled"></div>
										</div>
										
										<div class="ipanorama-control">
											<div class="ipanorama-input-group ipanorama-long">
												<div class="ipanorama-input-group-cell ipanorama-rgap">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets the track title', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Title', 'ipanorama'); ?></div>
													<input class="ipanorama-text ipanorama-long" type="text" al-text="appData.ui.activeTrack.title">
												</div>
												<div class="ipanorama-input-group-cell ipanorama-lgap">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets the track ID', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Id', 'ipanorama'); ?></div>
													<input class="ipanorama-text ipanorama-long ipanorama-can-lock" type="text" al-text="appData.ui.activeTrack.id" readonly="readonly">
												</div>
											</div>
										</div>
										
										<div class="ipanorama-control">
											<div class="ipanorama-input-group ipanorama-long">
												<div class="ipanorama-input-group-cell ipanorama-rgap">
													<div class="ipanorama-helper" title="<?php esc_html_e('Sets an audio stream', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Audio file', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell">
															<input class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" al-text="appData.ui.activeTrack.url" placeholder="<?php esc_html_e('Select audio', 'ipanorama'); ?>">
														</div>
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.selectAudio(appData, appData.rootScope, appData.ui.activeTrack)" title="<?php esc_html_e('Select audio', 'ipanorama'); ?>"><span><i class="xfa fa-folder"></i></span></div>
														</div>
													</div>
												</div>
												<div class="ipanorama-input-group-cell ipanorama-lgap">
													<div class="ipanorama-helper" title="<?php esc_html_e('Specifies the scene ID to be connected with the audio track', 'ipanorama'); ?>"></div>
													<div class="ipanorama-label"><?php esc_html_e('Scene', 'ipanorama'); ?></div>
													<div class="ipanorama-input-group ipanorama-long">
														<select class="ipanorama-select ipanorama-long" al-sceneguid="appData.ui.activeTrack.linkSceneGuid">
															<option value="none">none</option>
															<option al-repeat="scene in appData.config.scenes" value="{{scene.guid}}">{{(scene.title.data ? scene.title.data : scene.id)}}</option>
														</select>
													</div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div al-checkbox="appData.ui.activeTrack.autoplay"></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<?php esc_html_e('Autoplay the audio track after loading a scene', 'ipanorama'); ?>
														</div>
													</div>
													<div class="ipanorama-input-group ipanorama-long">
														<div class="ipanorama-input-group-cell ipanorama-pinch">
															<div al-checkbox="appData.ui.activeTrack.loop"></div>
														</div>
														<div class="ipanorama-input-group-cell">
															<?php esc_html_e('Automatically loop the audio track forever in a scene', 'ipanorama'); ?>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.customCSS" al-if="appData.ui.tabs.customCSS">
						<div class="ipanorama-stage">
							<div class="ipanorama-main-panel ipanorama-main-panel-general">
								<div class="ipanorama-data ipanorama-active">
									<div class="ipanorama-control">
										<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable custom styles', 'ipanorama'); ?>"></div>
										<div class="ipanorama-input-group">
											<div class="ipanorama-input-group-cell ipanorama-pinch">
												<div al-toggle="appData.config.customCSS.active"></div>
											</div>
											<div class="ipanorama-input-group-cell">
												<div class="ipanorama-label ipanorama-offset-top"><?php esc_html_e('Enable styles', 'ipanorama'); ?></div>
											</div>
										</div>
									</div>
									<div class="ipanorama-control">
										<pre id="ipanorama-notepad-css" class="ipanorama-notepad"></pre>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.customJS" al-if="appData.ui.tabs.customJS">
						<div class="ipanorama-stage">
							<div class="ipanorama-main-panel ipanorama-main-panel-general">
								<div class="ipanorama-data ipanorama-active">
									<div class="ipanorama-control">
										<div class="ipanorama-helper" title="<?php esc_html_e('Enable/disable custom javascript code', 'ipanorama'); ?>"></div>
										<div class="ipanorama-input-group">
											<div class="ipanorama-input-group-cell ipanorama-pinch">
												<div al-toggle="appData.config.customJS.active"></div>
											</div>
											<div class="ipanorama-input-group-cell">
												<div class="ipanorama-label ipanorama-offset-top"><?php esc_html_e('Enable javascript code', 'ipanorama'); ?></div>
											</div>
										</div>
									</div>
									<div class="ipanorama-control">
										<pre id="ipanorama-notepad-js" class="ipanorama-notepad"></pre>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ipanorama-section" al-attr.class.ipanorama-active="appData.ui.tabs.shortcode" al-if="appData.wp_item_id">
						<div class="ipanorama-main-panel ipanorama-main-panel-general">
							<div class="ipanorama-data ipanorama-active">
								<div class="ipanorama-control">
									<div class="ipanorama-info"><?php esc_html_e('Use a shortcode like the one below, copy and paste it into a post or page.', 'ipanorama'); ?></div>
								</div>
								
								<div class="ipanorama-control">
									<div class="ipanorama-label"><?php esc_html_e('Standard shortcode', 'ipanorama'); ?></div>
									<div class="ipanorama-input-group">
										<div class="ipanorama-input-group-cell">
											<input id="ipanorama-shortcode-1" class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" value='[ipano id="{{appData.wp_item_id}}"]' readonly="readonly">
										</div>
										<div class="ipanorama-input-group-cell ipanorama-pinch">
											<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.copyToClipboard(appData, '#ipanorama-shortcode-1')" title="<?php esc_html_e('Copy to clipboard', 'ipanorama'); ?>"><span><i class="xfa fa-clipboard"></i></span></div>
										</div>
									</div>
								</div>
								
								<p><?php esc_html_e('Next to that you can also add a few optional arguments to your shortcode:', 'ipanorama'); ?></p>
								<table class="ipanorama-table">
									<tbody>
										<tr>
											<th><?php esc_html_e('Variable', 'ipanorama'); ?></th>
											<th><?php esc_html_e('Value', 'ipanorama'); ?></th>
										</tr>
										<tr>
											<td><code>id</code></td>
											<td><?php esc_html_e('virtual tour ID', 'ipanorama'); ?></td>
										</tr>
										<tr>
											<td><code>slug</code></td>
											<td><?php esc_html_e('virtual tour slug identifier', 'ipanorama'); ?></td>
										</tr>
										<tr>
											<td><code>sceneid</code></td>
											<td><?php esc_html_e('start scene ID', 'ipanorama'); ?></td>
										</tr>
										<tr>
											<td><code>class</code></td>
											<td><?php esc_html_e('custom CSS class', 'ipanorama'); ?></td>
										</tr>
										<tr>
											<td><code>width</code></td>
											<td><?php esc_html_e('container width, can be specified in length values, like px, cm, etc', 'ipanorama'); ?></td>
										</tr>
										<tr>
											<td><code>height</code></td>
											<td><?php esc_html_e('container height, can be specified in length values, like px, cm, etc', 'ipanorama'); ?></td>
										</tr>
										<tr>
											<td><code>customdata</code></td>
											<td><?php esc_html_e('string custom data is applied to the container', 'ipanorama'); ?></td>
										</tr>
									</tbody>
								</table>
								
								<div class="ipanorama-control">
									<div class="ipanorama-info"><?php esc_html_e('You can add a virtual tour to a website or blog by embedding it. Copy the code below and paste it into your blog or website.', 'ipanorama'); ?></div>
								</div>
								
								<div class="ipanorama-control">
									<div class="ipanorama-label"><?php esc_html_e('Embed code with ID', 'ipanorama'); ?></div>
									<div class="ipanorama-input-group">
										<div class="ipanorama-input-group-cell">
											<input id="ipanorama-embedcode-1" class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" value='{{appData.embedCodeWithId}}' readonly="readonly">
										</div>
										<div class="ipanorama-input-group-cell ipanorama-pinch">
											<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.copyToClipboard(appData, '#ipanorama-embedcode-1')" title="<?php esc_html_e('Copy to clipboard', 'ipanorama'); ?>"><span><i class="xfa fa-clipboard"></i></span></div>
										</div>
									</div>
								</div>
								
								<div class="ipanorama-control" al-if="appData.config.slug">
									<div class="ipanorama-label"><?php esc_html_e('Embed code with URL slug', 'ipanorama'); ?></div>
									<div class="ipanorama-input-group">
										<div class="ipanorama-input-group-cell">
											<input id="ipanorama-embedcode-2" class="ipanorama-text ipanorama-long ipanorama-no-brr" type="text" value='{{appData.embedCodeWithSlug}}' readonly="readonly">
										</div>
										<div class="ipanorama-input-group-cell ipanorama-pinch">
											<div class="ipanorama-btn ipanorama-default ipanorama-no-bl" al-on.click="appData.fn.copyToClipboard(appData, '#ipanorama-embedcode-2')" title="<?php esc_html_e('Copy to clipboard', 'ipanorama'); ?>"><span><i class="xfa fa-clipboard"></i></span></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="ipanorama-modals" class="ipanorama-modals">
		</div>
	</div>
</div>
<!-- /end ipanorama app -->