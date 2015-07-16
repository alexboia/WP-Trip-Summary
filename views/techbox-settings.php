<script type="text/javascript">
    var abp01_nonce = '<?php echo $data->nonce; ?>';//abp01_nonce_settings
    var abp01_ajaxSaveAction = '<?php echo $data->ajaxSaveAction; ?>';
    var abp01_ajaxBaseUrl = '<?php echo $data->ajaxUrl; ?>';
</script>
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
	                    <input name="showTeaser" id="abp01-showTeaser" type="checkbox" class="abp01-checkbox" value="true" checked="<?php echo $data->settings->showTeaser ? 'checked' : ''; ?>" />
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
	                    <input type="checkbox" id="abp01-showFullScreen" name="showFullScreen" class="abp01-checkbox" value="true" checked="<?php echo $data->settings->showFullScreen ? 'checked' : ''; ?>" />
	                </td>
	            </tr>
	            <tr>
	                <th scope="row">
	                    <label for="abp01-showMagnifyingGlass"><?php echo __('Show magnifying glass?', 'abp01-trip-summary'); ?></label>
	                </th>
	                <td>
	                    <input type="checkbox" id="abp01-showMagnifyingGlass" name="showMagnifyingGlass" class="abp01-checkbox" value="true" checked="<?php echo $data->settings->showMagnifyingGlass ? 'checked' : ''; ?>" />
	                </td>
	            </tr>
	            <tr>
	            	<th scope="row">
	            		<label for="abp01-showMapScale"><?php echo __('Show map scale?', 'abp01-trip-summary'); ?></label>
	            	</th>
	            	<td>
	            		<input type="checkbox" id="abp01-showMapScale" name="showMapScale" class="abp01-checkbox" value="true" checked="<?php echo $data->settings->showMapScale ? 'checked' : ''; ?>" />
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