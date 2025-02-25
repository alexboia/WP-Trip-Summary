<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */
	defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
	var abp01_nonce = '<?php echo $data->nonce; ?>';//abp01_nonce_settings
	var abp01_ajaxSaveAction = '<?php echo $data->ajaxSaveAction; ?>';
	var abp01_ajaxBaseUrl = '<?php echo $data->ajaxUrl; ?>';
	var abp01_predefinedTileLayers = <?php echo json_encode($data->settings->allowedPredefinedTileLayers); ?>;
</script>

<div id="abp01-settings-page" class="abp01-bootstrap abp01-page">
	<form id="abp01-settings-form" method="post">
		<div id="abp01-settings-form-beacon"></div>
		<h2 class="abp01-page-title"><?php echo esc_html__('Trip Summary Settings', 'abp01-trip-summary'); ?></h2>
		<div class="container-fluid px-4">
			<div class="row gx-5">
				<div class="col col-md-3 abp01-page-sidebar abp01-rounded-container">
					<h4><?php echo esc_html__('Menu', 'abp01-trip-summary'); ?></h4>
					<div id="abp01-log-file-lists-container" class="abp01-page-side-bar-content">
						<ul class="nav nav-pills nav-fill flex-column">
							<li class="nav-item">
								<button class="nav-link active" 
									id="abp01-general-settings-tab"
									type="button"
									role="tab"
									aria-selected="true"
									data-bs-toggle="tab"
									data-bs-target="#abp01-general-settings"><span class="dashicons dashicons-admin-generic"></span>&nbsp;<?php echo esc_html__('General Settings', 'abp01-trip-summary'); ?></button>
							</li>
							<li class="nav-item">
								<button class="nav-link" 
									id="abp01-viewer-settings-tab"
									type="button"
									role="tab"
									aria-selected="true"
									data-bs-toggle="tab"
									data-bs-target="#abp01-viewer-settings"><span class="dashicons dashicons-welcome-view-site"></span>&nbsp;<?php echo esc_html__('Viewer settings', 'abp01-trip-summary'); ?></button>
							</li>
							<li class="nav-item">
								<button class="nav-link" 
									id="abp01-map-settings-tab"
									type="button"
									role="tab"
									aria-selected="true"
									data-bs-toggle="tab"
									data-bs-target="#abp01-map-settings"><span class="dashicons dashicons-admin-site"></span>&nbsp;<?php echo esc_html__('Map Settings', 'abp01-trip-summary'); ?></button>
							</li>
						</ul>
					</div>
				</div>
				<div class="col col-md-9 abp01-page-workspace">
					<div class="abp01-rounded-container abp01-page-workspace-inner">
						<h4><?php echo esc_html__('Options', 'abp01-trip-summary'); ?></h4>
						<div class="tab-content abp01-page-workspace-content">
							<div id="abp01-general-settings" 
								role="tabpanel"
								aria-labelledby="abp01-general-settings-tab"
								class="tab-pane active abp01-tab-content-container">

								<p class="abp01-settings-info lead">
									<?php echo esc_html__('These settings control various aspects that do not belong to any one topic', 'abp01-trip-summary'); ?>
								</p>

								<div class="mb-3 row">
									<label for="abp01-unitSystem" class="col-sm-3 col-form-label"><?php echo esc_html__('Unit system', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<select name="unitSystem" id="abp01-unitSystem" class="form-control form-select abp01-select">
											<?php foreach ($data->settings->allowedUnitSystems as $s => $lbl): ?>
												<?php if ($data->settings->unitSystem == $s): ?>
												<option value="<?php echo esc_attr($s) ?>" selected="selected"><?php echo esc_html($lbl) ?></option>
												<?php else: ?>
													<option value="<?php echo esc_attr($s) ?>"><?php echo esc_html($lbl) ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>

							<div id="abp01-viewer-settings" 
								role="tabpanel"
								aria-labelledby="abp01-viewer-settings-tab"
								class="tab-pane abp01-tab-content-container">

								<p class="abp01-settings-info lead">
									<?php echo esc_html__('These settings configure the trip summary viewer', 'abp01-trip-summary'); ?>
								</p>

								<div class="mb-3 row">
									<label for="abp01-showTeaser" class="col-sm-3 col-form-label"><?php echo esc_html__('Show teaser?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input name="showTeaser" 
											id="abp01-showTeaser" 
											type="checkbox" 
											class="abp01-checkbox"
											value="true" <?php echo $data->settings->showTeaser ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>
								
								<div class="mb-3 row">
									<label for="abp01-topTeaserText" class="col-sm-3 col-form-label"><?php echo esc_html__('Top teaser text', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<textarea name="topTeaserText" 
											id="abp01-topTeaserText" 
											class="form-control abp01-textarea-input"><?php echo esc_html($data->settings->topTeaserText); ?></textarea>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-bottomTeaserText" class="col-sm-3 col-form-label"><?php echo esc_html__('Bottom teaser text', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<textarea name="bottomTeaserText" 
											id="abp01-bottomTeaserText" 
											class="form-control abp01-textarea-input"><?php echo esc_html($data->settings->bottomTeaserText); ?></textarea>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-initialViewerTab" class="col-sm-3 col-form-label"><?php echo esc_html__('Initial viewer tab', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<select name="initialViewerTab" id="abp01-initialViewerTab" class="form-control form-select abp01-select">
											<?php foreach ($data->settings->allowedViewerTabs as $s => $lbl): ?>
												<?php if ($data->settings->initialViewerTab == $s): ?>
													<option value="<?php echo esc_attr($s) ?>" selected="selected"><?php echo esc_html($lbl) ?></option>
												<?php else: ?>
													<option value="<?php echo esc_attr($s) ?>"><?php echo esc_html($lbl) ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										</select>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-viewerItemLayout" class="col-sm-3 col-form-label"><?php echo esc_html__('Chose how multi-value items are laid out', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9">
										<select name="viewerItemLayout" id="abp01-viewerItemLayout" class="form-control form-select abp01-select">
											<?php foreach ($data->settings->allowedItemLayouts as $l => $lbl): ?>
												<?php if ($data->settings->viewerItemLayout == $l): ?>
													<option value="<?php echo esc_attr($l) ?>" selected="selected"><?php echo esc_html($lbl) ?></option>
												<?php else: ?>
													<option value="<?php echo esc_attr($l) ?>"><?php echo esc_html($lbl) ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										</select>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-viewerItemValueDisplayCount" class="col-sm-3 col-form-label"><?php echo esc_html__('Chose how many values of a multi-valued item are displayed', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9">
										<input id="abp01-viewerItemValueDisplayCount" 
											class="form-control abp01-number-input"
											min="<?php echo esc_attr($data->optionsLimits->minViewerItemValueDisplayCount); ?>"
											name="viewerItemValueDisplayCount" 
											type="number" 
											value="<?php echo esc_attr($data->settings->viewerItemValueDisplayCount); ?>" 
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-jsonLdEnabled" class="col-sm-3 col-form-label"><?php echo esc_html__('Enable JSON-LD frontend data', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input name="jsonLdEnabled" 
											id="abp01-jsonLdEnabled" 
											type="checkbox" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->jsonLdEnabled ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>
							</div>

							<div id="abp01-map-settings" 
								role="tabpanel"
								aria-labelledby="abp01-map-settings-tab"
								class="tab-pane abp01-tab-content-container">

								<p class="abp01-settings-info lead">
									<?php echo esc_html__('These settings configure the map component', 'abp01-trip-summary'); ?>
								</p>

								<div class="mb-3 row">
									<label for="abp01-tileLayerUrl" class="col-sm-3 col-form-label"><?php echo esc_html__('Tile layer URL template', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input type="text" 
											id="abp01-tileLayerUrl" 
											name="tileLayerUrl" 
											class="form-control abp01-text-input abp01-tile-layer-url-input" 
											value="<?php echo esc_attr($data->settings->tileLayer->url); ?>" 
										/>

										<input type="button" 
											id="abp01-predefined-tile-layer-selector" 
											class="button" 
											value="<?php echo esc_html__('... or chose pre-defined', 'abp01-trip-summary'); ?>" 
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-tileLayerAttributionUrl" class="col-sm-3 col-form-label"><?php echo esc_html__('Tile layer attribution URL', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input type="text" 
											id="abp01-tileLayerAttributionUrl" 
											name="tileLayerAttributionUrl" 
											class="form-control abp01-text-input" 
											value="<?php echo esc_attr($data->settings->tileLayer->attributionUrl); ?>" 
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-tileLayerAttributionTxt" class="col-sm-3 col-form-label"><?php echo esc_html__('Tile layer attribution text', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input type="text" 
											id="abp01-tileLayerAttributionTxt" 
											name="tileLayerAttributionTxt" 
											class="form-control abp01-text-input" 
											value="<?php echo esc_attr($data->settings->tileLayer->attributionTxt); ?>" 
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-tileLayerApiKey" class="col-sm-3 col-form-label"><?php echo esc_html__('Tile layer API key', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input type="text" 
											id="abp01-tileLayerApiKey" 
											name="tileLayerApiKey" 
											class="form-control abp01-text-input" 
											value="<?php echo esc_attr($data->settings->tileLayer->apiKey); ?>" 
										/>

										<span id="abp01-tileLayer-apiKey-nag" 
											class="dashicons dashicons-warning" 
											style="display: none" 
											data-nag-text="<?php echo esc_attr__('This tile layer requires an API key from the service provider', 'abp01-trip-summary'); ?>"></span>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-showFullScreen" class="col-sm-3 col-form-label"><?php echo esc_html__('Enable map fullscreen mode?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input type="checkbox" 
											id="abp01-showFullScreen" 
											name="showFullScreen" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->showFullScreen ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-showMagnifyingGlass" class="col-sm-3 col-form-label"><?php echo esc_html__('Show magnifying glass?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input type="checkbox" 
											id="abp01-showMagnifyingGlass" 
											name="showMagnifyingGlass" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->showMagnifyingGlass ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-showMapScale" class="col-sm-3 col-form-label"><?php echo esc_html__('Show map scale?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input type="checkbox" 
											id="abp01-showMapScale" 
											name="showMapScale" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->showMapScale ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-showMinMaxAltitude" class="col-sm-3 col-form-label"><?php echo esc_html__('Show minimum & maximum altitude?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input type="checkbox" 
											id="abp01-showMinMaxAltitude" 
											name="showMinMaxAltitude" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->showMinMaxAltitude ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-showAltitudeProfile" class="col-sm-3 col-form-label"><?php echo esc_html__('Show altitude profile?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input type="checkbox" 
											id="abp01-showAltitudeProfile" 
											name="showAltitudeProfile" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->showAltitudeProfile ? 'checked="checked"' : ''; ?>
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-allowTrackDownload" class="col-sm-3 col-form-label"><?php echo esc_html__('Allow track download?', 'abp01-trip-summary'); ?></label>
									<div class="col-sm-9 abp01-checkbox-container">
										<input type="checkbox" 
											id="abp01-allowTrackDownload" 
											name="allowTrackDownload" 
											class="abp01-checkbox" 
											value="true" <?php echo $data->settings->allowTrackDownload ? 'checked="checked"' : '' ?>
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-trackLineColour" class="col-sm-3 col-form-label"><?php echo esc_html__('Track line colour', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input id="abp01-trackLineColour" 
											name="trackLineColour" 
											type="text" 
											value="<?php echo esc_attr($data->settings->trackLineColour); ?>" 
											data-default-color="#0033ff"  
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-trackLineWeight" class="col-sm-3 col-form-label"><?php echo esc_html__('Track line weight', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input id="abp01-trackLineWeight"
											class="form-control abp01-number-input"
											min="<?php echo esc_attr($data->optionsLimits->minAllowedTrackLineWeight); ?>"
											name="trackLineWeight" 
											type="number" 
											value="<?php echo esc_attr($data->settings->trackLineWeight); ?>" 
										/>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="abp01-mapHeight" class="col-sm-3 col-form-label"><?php echo esc_html__('Map height', 'abp01-trip-summary'); ?>:</label>
									<div class="col-sm-9">
										<input id="abp01-mapHeight" 
											class="form-control abp01-number-input"
											min="<?php echo esc_attr($data->optionsLimits->minAllowedMapHeight); ?>"
											name="mapHeight" 
											type="number" 
											value="<?php echo esc_attr($data->settings->mapHeight); ?>" 
										/>
									</div>
								</div>
							</div>

							<div id="abp01-page-workspace-toolbar" class="abp01-page-workspace-toolbar">
								<div class="apb01-settings-save">
									<input type="button" 
										id="abp01-submit-settings" 
										name="abp01-submit-settings" 
										class="btn btn-primary abp01-form-submit-btn apb01-settings-save-btn" 
										value="<?php echo esc_html__('Save settings', 'abp01-trip-summary'); ?>" 
									/>
								</div>
							</div>

							<script id="tpl-abp01-predefined-tile-layers-container" type="text/x-kite">
								<div id="abp01-predefined-tile-layers-container" class="abp01-window-container">
									<div id="abp01-predefined-tile-layers-container-header" class="abp01-window-container-header">
										<h3><?php echo __('Chose a pre-defined tile layer service', 'abp01-trip-summary'); ?></h3>
										<a href="javascript:void(0)" class="abp01-close-window abp01-close-tile-layer-selector">
											<span class="dashicons dashicons-dismiss"></span>
										</a>
										<div class="abp01-clear"></div>
									</div>
									<div id="abp01-predefined-tile-layers-container-inner" class="abp01-window-container-inner">
										{{#predefinedTileLayers}}
											<div class="abp01-predefined-tile-layer">
												<h4>{{label}}</h4>
												<ul>
													<li class="abp01-predefined-tile-layer-prop"> 
														{{tileLayerObject.attributionTxt}}
													</li>
													{{? apiKeyRequired }}
														<li class="abp01-predefined-tile-layer-prop abp01-predefined-tile-layer-prop-warn"> 
															<span class="abp01-predefined-tile-layer-prop-warn-message">
																<span class="dashicons dashicons-warning"></span>
																<?php echo __('This tile layer requires an API key from the service provider', 'abp01-trip-summary'); ?>
															</span>
														</li>
													{{/?}}
												</ul>
												<div class="abp01-predefined-tile-layer-actions">
													<a class="button abp01-view-tile-layer-details" href="{{infoUrl}}" target="_blank">
														<?php echo __('View details', 'abp01-trip-summary'); ?>
													</a>
													<a class="button button-primary abp01-use-tile-layer" data-predefined-tile-layer-id="{{id}}" href="javascript:void(0);">
														<?php echo __('Use this tile layer', 'abp01-trip-summary'); ?>
													</a>
												</div>
											</div>
										{{/predefinedTileLayers}}
									</div
								</div
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>