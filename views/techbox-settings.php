<?php
/**
 * Copyright (c) 2014-2019, Alexandru Boia
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *  - Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
	defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
    var abp01_nonce = '<?php echo $data->nonce; ?>';//abp01_nonce_settings
    var abp01_ajaxSaveAction = '<?php echo $data->ajaxSaveAction; ?>';
    var abp01_ajaxBaseUrl = '<?php echo $data->ajaxUrl; ?>';
</script>
<div id="abp01-settings-page">
	<form id="abp01-settings-form" method="post">
		<div id="abp01-settings-form-beacon"></div>
		<h2><?php echo __('Trip Summary Settings', 'abp01-trip-summary'); ?></h2>
		<div id="abp01-settings-save-result" class="updated settings-error abp01-settings-save-result" style="display:none"></div>
		<div id="abp01-settings-container">
			<h3><?php echo __('General Settings', 'abp01-trip-summary'); ?></h3>
			<div class="abp01-settings-info description">
				<?php echo __('These settings control various aspects that do not belong to any one topic', 'abp01-trip-summary'); ?>
			</div>
			<div class="abp01-settings-container">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="abp01-unitSystem"><?php echo __('Unit system', 'abp01-trip-summary'); ?>:</label>
						</th>
						<td>
							<select name="unitSystem" id="abp01-unitSystem" class="abp01-select">
								<?php foreach ($data->settings->allowedUnitSystems as $s => $lbl): ?>
									<?php if ($data->settings->unitSystem == $s): ?>
									<option value="<?php echo $s ?>" selected="selected"><?php echo $lbl ?></option>
									<?php else: ?>
										<option value="<?php echo $s ?>"><?php echo $lbl ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<h3><?php echo __('Viewer settings', 'abp01-trip-summary'); ?></h3>
			<div class="abp01-settings-info description">
				<?php echo __('These settings configure the trip summary viewer', 'abp01-trip-summary'); ?>
			</div>
			<div class="abp01-settings-container">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="abp01-showTeaser"><?php echo __('Show teaser?', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input name="showTeaser" id="abp01-showTeaser" type="checkbox" class="abp01-checkbox" value="true" <?php echo $data->settings->showTeaser ? 'checked="checked"' : ''; ?>/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-topTeaserText"><?php echo __('Top teaser text', 'abp01-trip-summary'); ?>:</label>
						</th>
						<td>
							<textarea name="topTeaserText" id="abp01-topTeaserText" class="regular-text abp01-textarea-input"><?php echo $data->settings->topTeaserText; ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-bottomTeaserText"><?php echo __('Bottom teaser text', 'abp01-trip-summary'); ?>:</label>
						</th>
						<td>
							<textarea name="bottomTeaserText" id="abp01-bottomTeaserText" class="regular-text abp01-textarea-input"><?php echo $data->settings->bottomTeaserText; ?></textarea>
						</td>
					</tr>
				</table>
			</div>

			<h3><?php echo __('Map Settings', 'abp01-trip-summary'); ?></h3>
			<div class="abp01-settings-info description">
				<?php echo __('These settings configure the map component', 'abp01-trip-summary'); ?>
			</div>
			<div class="abp01-settings-container">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="abp01-tileLayerUrl"><?php echo __('Tile layer URL template', 'abp01-trip-summary'); ?>:</label>
						</th>
						<td>
							<input type="text" id="abp01-tileLayerUrl" name="tileLayerUrl" class="regular-text abp01-text-input" value="<?php echo $data->settings->tileLayer->url; ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-tileLayerAttributionUrl"><?php echo __('Tile layer attribution URL', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input type="text" id="abp01-tileLayerAttributionUrl" name="tileLayerAttributionUrl" class="regular-text abp01-text-input" value="<?php echo $data->settings->tileLayer->attributionUrl; ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-tileLayerAttributionTxt"><?php echo __('Tile layer attribution text', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input type="text" id="abp01-tileLayerAttributionTxt" name="tileLayerAttributionTxt" class="regular-text abp01-text-input" value="<?php echo $data->settings->tileLayer->attributionTxt; ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-showFullScreen"><?php echo __('Enable map fullscreen mode?', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input type="checkbox" id="abp01-showFullScreen" name="showFullScreen" class="abp01-checkbox" value="true" <?php echo $data->settings->showFullScreen ? 'checked="checked"' : ''; ?>/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-showMagnifyingGlass"><?php echo __('Show magnifying glass?', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input type="checkbox" id="abp01-showMagnifyingGlass" name="showMagnifyingGlass" class="abp01-checkbox" value="true" <?php echo $data->settings->showMagnifyingGlass ? 'checked="checked"' : ''; ?>/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-showMapScale"><?php echo __('Show map scale?', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input type="checkbox" id="abp01-showMapScale" name="showMapScale" class="abp01-checkbox" value="true" <?php echo $data->settings->showMapScale ? 'checked="checked"' : ''; ?>/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abp01-allowTrackDownload"><?php echo __('Allow track download?', 'abp01-trip-summary'); ?></label>
						</th>
						<td>
							<input type="checkbox" id="abp01-allowTrackDownload" name="allowTrackDownload" class="abp01-checkbox" value="true" <?php echo $data->settings->allowTrackDownload ? 'checked="checked"' : '' ?>/>
						</td>
					</tr>
				</table>
			</div>

			<div class="apb01-settings-save">
				<input type="button" id="abp01-submit-settings" name="abp01-submit-settings" class="button button-primary abp01-form-submit-btn" value="<?php echo __('Save settings', 'abp01-trip-summary'); ?>" />
			</div>
		</div>

		<script id="tpl-abp01-progress-container" type="text/x-kite">
			<div id="abp01-progress-container" class="abp01-progress-container">
				<div data-role="progressLabel" id="abp01-progress-label" class="abp01-progress-label"></div>
				<div data-role="progressParent" id="abp01-progress-bar" class="abp01-progress-bar"></div>
			</div>
		</script>
	</form>
</div>