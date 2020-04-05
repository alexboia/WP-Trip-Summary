<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
    var abp01_imgBase = '<?php echo esc_js($data->imgBaseUrl); ?>';
    var abp01_ajaxUrl = '<?php echo esc_js($data->ajaxUrl); ?>';
    var abp01_ajaxEditInfoAction = '<?php echo esc_js($data->ajaxEditInfoAction); ?>';
    var abp01_ajaxUploadTrackAction = '<?php echo esc_js($data->ajaxUploadTrackAction); ?>';
    var abp01_ajaxGetTrackAction = '<?php echo esc_js($data->ajaxGetTrackAction); ?>';
    var abp01_ajaxClearTrackAction = '<?php echo esc_js($data->ajaxClearTrackAction); ?>';
    var abp01_ajaxClearInfoAction = '<?php echo esc_js($data->ajaxClearInfoAction); ?>';
    var abp01_downloadTrackAction = '<?php echo esc_js($data->downloadTrackAction); ?>';
    var abp01_tourType = '<?php echo esc_js($data->tourType); ?>';

    var abp01_flashUploaderUrl = '<?php echo esc_js($data->flashUploaderUrl); ?>';
    var abp01_xapUploaderUrl = '<?php echo esc_js($data->xapUploaderUrl); ?>';
    var abp01_uploadMaxFileSize = '<?php echo esc_js($data->uploadMaxFileSize); ?>';
    var abp01_uploadChunkSize = '<?php echo esc_js($data->uploadChunkSize); ?>';
    var abp01_uploadKey = '<?php echo $data->uploadKey; ?>';

    var abp01_postId = '<?php echo $data->postId; ?>';
    var abp01_hasTrack = <?php echo $data->hasTrack ? 'true' : 'false'; ?>;

    var abp01_baseTitle = '<?php echo esc_html__('Edit trip summary', 'abp01-trip-summary') ?>';
</script>

<div id="abp01-techbox-editor" style="display:none;">
    <input type="hidden" name="abp01-nonce" 
        id="abp01-nonce" 
        value="<?php echo esc_attr($data->nonce); ?>" />
    <input type="hidden" name="abp01-nonce-get" 
        id="abp01-nonce-get" 
        value="<?php echo esc_attr($data->nonceGet); ?>" />
    <input type="hidden" name="abp01-nonce-download" 
        id="abp01-nonce-download" 
        value="<?php echo esc_attr($data->nonceDownload); ?>" />

    <div id="abp01-editor-wrapper" class="abp01-editor-wrapper">
        <div class="abp01-editor-title-wrap">
            <div id="ctrl_abp01_editorTitle" class="abp01-editor-title">
                <span class="abp01-editor-icon"></span><?php echo esc_html__('Edit trip summary', 'abp01-trip-summary') ?>
            </div>
            <div class="abp01-editor-title-close">
                <a href="javascript:void(0)" data-action="abp01-closeTechBox" class="abp01-close-editor">
                    <span class="dashicons dashicons-dismiss"></span>
                </a>
            </div>
            <div class="abp01-clear"></div>
        </div>
        <div id="abp01-editor-content" class="abp01-editor-content tab-container">
            <ul id="abp01-editor-tabs" class="abp01-editor-tabs">
                <li id="abp01-tab-info" class="abp01-tab abp01-tab-info tab">
                    <a href="#abp01-form-info" data-action="abp01-tab">
                        <span class="dashicons dashicons-index-card"></span><?php echo esc_html__('Info', 'abp01-trip-summary') ?>
                    </a>
                </li>
                <li id="abp01-tab-map" class="abp01-tab abp01-tab-map tab">
                    <a href="#abp01-form-map" data-action="abp01-tab">
                        <span class="dashicons dashicons-chart-line"></span><?php echo esc_html__('Map', 'abp01-trip-summary'); ?>
                    </a>
                </li>
            </ul>
            <div class="abp01-clear"></div>
            <div id="abp01-editor-form" class="abp01-editor-form">
                <div class="abp01-tabContent" id="abp01-form-info" style="display: none;"></div>
                <div class="abp01-tabContent" id="abp01-form-map" style="display: none;"></div>
            </div>
            <div class="abp01-editor-action"></div>
        </div>
        <div class="abp01-editor-footer">
            <a id="abp01-resetTechBox" href="javascript:void(0)" class="button button-large button-reset"><?php echo esc_html__('Clear', 'abp01-trip-summary'); ?></a>
            <a id="abp01-saveTechBox" href="javascript:void(0)" class="button button-primary button-large button-save-summary" <?php echo empty($data->tourType) ? 'style="display:none;"' : ''; ?>><?php echo esc_html__('Save', 'abp01-trip-summary'); ?></a>
            <div class="abp01-clear"></div>
        </div>
    </div>
</div>

<script id="tpl-abp01-formInfo-unselected" type="text/x-kite">
    <div id="abp01-form-info-typeSelection" class="abp01-type-selector-container">
        <h3 class="abp01-form-info-selector-notice">
            <?php echo esc_html__('Chose the type of your tour', 'abp01-trip-summary'); ?>
        </h3>
        <a href="javascript:void(0)" class="button button-hero abp01-type-selector first" data-action="abp01-typeSelect" data-type="bike">
            <?php echo esc_html__('Biking', 'abp01-trip-summary'); ?>
        </a>
        <a href="javascript:void(0)" class="button button-hero abp01-type-selector" data-action="abp01-typeSelect" data-type="hiking">
            <?php echo esc_html__('Hiking', 'abp01-trip-summary'); ?>
        </a>
        <a href="javascript:void(0)" class="button button-hero abp01-type-selector" data-action="abp01-typeSelect" data-type="trainRide">
            <?php echo esc_html__('Train Ride', 'abp01-trip-summary'); ?>
        </a>
        <div class="abp01-clear"></div>
    </div>
</script>

<script id="tpl-abp01-formInfo-bikeTour" type="text/x-kite">
    <div id="abp01-form-info-bike">
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeDistance"><?php echo esc_html__('Total distance', 'abp01-trip-summary'); ?>:</label>
            <input type="text" id="ctrl_abp01_bikeDistance" name="ctrl_abp01_bikeDistance" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'bikeDistance')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeTotalClimb"><?php echo esc_html__('Total climb', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_bikeTotalClimb" name="ctrl_abp01_bikeTotalClimb" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'bikeTotalClimb')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeDifficultyLevel"><?php echo esc_html__('Difficulty level', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->difficultyLevels) && is_array($data->difficultyLevels)): ?>
				<select id="ctrl_abp01_bikeDifficultyLevel" name="ctrl_abp01_bikeDifficultyLevel" class="ab01-input-select">
					<option value="0"><?php echo esc_html__('-- Choose an option --', 'abp01-trip-summary'); ?></option>
					<?php abp01_render_difficulty_level_options($data->difficultyLevels, abp01_extract_value_from_data($data, 'bikeDifficultyLevel')); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->difficultyLevelsAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeAccess"><?php echo esc_html__('Access information', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_bikeAccess" name="ctrl_abp01_bikeAccess" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'bikeAccess')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeRecommendedSeasons"><?php echo esc_html__('Open during seasons', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->recommendedSeasons) && is_array($data->recommendedSeasons)): ?>
				<select name="bikeRecommendedSeasons" id="ctrl_abp01_bikeRecommendedSeasons" multiple="multiple">
					<?php abp01_render_select_options($data->recommendedSeasons, 'bikeRecommendedSeasons', $data); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->recommendedSeasonsAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikePathSurfaceType"><?php echo esc_html__('Path surface type', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->pathSurfaceTypes) && is_array($data->pathSurfaceTypes)): ?>
				<select name="bikePathSurfaceType" id="ctrl_abp01_bikePathSurfaceType" multiple="multiple">				
					<?php abp01_render_select_options($data->pathSurfaceTypes, 'bikePathSurfaceType', $data); ?>				
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->pathSurfaceTypesAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeBikeType"><?php echo esc_html__('Bike type', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->bikeTypes) && is_array($data->bikeTypes)): ?>
				<select name="bikeBikeType" id="ctrl_abp01_bikeBikeType" multiple="multiple">				
					<?php abp01_render_select_options($data->bikeTypes, 'bikeBikeType', $data); ?>				
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->bikeTypesAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
    </div>
</script>

<script id="tpl-abp01-formInfo-hikingTour" type="text/x-kite">
    <div id="abp01-form-info-hiking">
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingDistance"><?php echo esc_html__('Total distance', 'abp01-trip-summary') ?></label>
            <input type="text" id="ctrl_abp01_hikingDistance" name="ctrl_abp01_hikingDistance" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'hikingDistance')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingTotalClimb"><?php echo esc_html__('Total climb', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_hikingTotalClimb" name="ctrl_abp01_hikingTotalClimb" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'hikingTotalClimb')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="abp01_hikingDifficultyLevel"><?php echo esc_html__('Difficulty level', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->difficultyLevels) && is_array($data->difficultyLevels)): ?>
				<select name="hikingDifficultyLevel" id="abp01_hikingDifficultyLevel" class="abp01-input-select">
					<option value="0"><?php echo esc_html__('-- Choose an option --', 'abp01-trip-summary'); ?></option>
					<?php abp01_render_difficulty_level_options($data->difficultyLevels, abp01_extract_value_from_data($data, 'hikingDifficultyLevel')); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->difficultyLevelsAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingAccess"><?php echo esc_html__('Access information', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_hikingAccess" name="ctrl_abp01_hikingAccess" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'hikingAccess')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingRecommendedSeasons"><?php echo esc_html__('Open during seasons', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->recommendedSeasons) && is_array($data->recommendedSeasons)): ?>
				<select name="hikingRecommendedSeasons" id="ctrl_abp01_hikingRecommendedSeasons" multiple="multiple">				
					<?php abp01_render_select_options($data->recommendedSeasons, 'hikingRecommendedSeasons', $data); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->recommendedSeasonsAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingSurfaceType"><?php echo esc_html__('Path surface type', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->pathSurfaceTypes) && is_array($data->pathSurfaceTypes)): ?>
				<select name="hikingSurfaceType" id="ctrl_abp01_hikingSurfaceType" multiple="multiple">				
					<?php abp01_render_select_options($data->pathSurfaceTypes, 'hikingSurfaceType', $data); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->pathSurfaceTypesAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingRouteMarkers"><?php echo esc_html__('Route markers', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_hikingRouteMarkers" name="ctrl_abp01_hikingRouteMarkers" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'hikingRouteMarkers')); ?>" />
        </div>
    </div>
</script>

<script id="tpl-abp01-formInfo-trainRide" type="text/x-kite">
    <div id="abp01-form-info-trainRide">
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideDistance"><?php echo esc_html__('Total distance', 'abp01-trip-summary') ?></label>
            <input type="text" id="ctrl_abp01_trainRideDistance" name="ctrl_abp01_trainRideDistance" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'trainRideDistance')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideChangeNumber"><?php echo esc_html__('Exchanged trains', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_trainRideChangeNumber" name="ctrl_abp01_trainRideChangeNumber" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'trainRideChangeNumber')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideGauge"><?php echo esc_html__('Line gauge', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_trainRideGauge" name="ctrl_abp01_trainRideGauge" class="abp01-input-text" value="<?php echo esc_attr(abp01_extract_value_from_data($data, 'trainRideGauge')); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideOperator"><?php echo esc_html__('Railroad operators', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->railroadOperators) && is_array($data->railroadOperators)): ?>
				<select name="trainRideOperator" id="ctrl_abp01_trainRideOperator" multiple="multiple">
					<?php abp01_render_select_options($data->railroadOperators, 'trainRideOperator', $data); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->railroadOperatorsAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideLineStatus"><?php echo esc_html__('Line status', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->railroadLineStatuses) && is_array($data->railroadLineStatuses)): ?>
				<select name="trainRideLineStatus" id="ctrl_abp01_trainRideLineStatus" multiple="multiple">
					<?php abp01_render_select_options($data->railroadLineStatuses, 'trainRideLineStatus', $data); ?>	
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->railroadLineStatusesAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="trainRideElectrificationStatus"><?php echo esc_html__('Electrification status', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->railroadElectrification) && is_array($data->railroadElectrification)): ?>
				<select name="trainRideElectrificationStatus" id="ctrl_abp01_trainRideElectrificationStatus" multiple="multiple">
					<?php abp01_render_select_options($data->railroadElectrification, 'trainRideElectrificationStatus', $data); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->railroadElectrificationAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideLineType"><?php echo esc_html__('Line type', 'abp01-trip-summary'); ?></label>
			<?php if (!empty($data->railroadLineTypes) && is_array($data->railroadLineTypes)): ?>
				<select name="trainRideLineType" id="ctrl_abp01_trainRideLineType" multiple="multiple">
					<?php abp01_render_select_options($data->railroadLineTypes, 'trainRideLineType', $data); ?>
				</select>
			<?php else: ?>
				<div class="abp01_no_lookup_items_notice">
					<a href="<?php echo esc_url($data->railroadLineTypesAdminUrl); ?>" target="_blank"><?php echo esc_html__('No items to select from. Visit the lookup data management page to add some.', 'abp01-trip-summary'); ?></a>
				</div>
			<?php endif; ?>
        </div>
    </div>
</script>

<script id="tpl-abp01-formMap-unselected" type="text/x-kite">
    <div id="abp01-form-map-trackSelection" class="abp01-type-selector-container">
        <h3 class="abp01-form-map-selector-notice">
            <?php echo esc_html__('Upload a GPX track file', 'abp01-trip-summary'); ?>
        </h3>
        <a id="abp01-track-selector" href="javascript:void(0)" class="button button-hero abp01-track-selector first" data-action="abp01-trackSelect">
            <?php echo esc_html__('Chose file', 'abp01-trip-summary'); ?>
        </a>
        <div class="abp01-clear"></div>
    </div>
</script>

<script id="tpl-abp01-progress-container" type="text/x-kite">
    <div id="abp01-progress-container" class="abp01-progress-container">
        <div data-role="progressLabel" id="abp01-progress-label" class="abp01-progress-label"></div>
        <div data-role="progressParent" id="abp01-progress-bar" class="abp01-progress-bar"></div>
    </div>
</script>

<script id="tpl-abp01-formMap-uploaded" type="text/x-kite">
    <div id="abp01-map-container" class="abp01-map-container" data-role="map-container">
        <div id="abp01-map" class="abp01-map" data-role="map-holder"></div>
        <div id="abp01-map-retry-container" class="abp01-map-retry-container" style="display: none;">
            <div id="abp01-map-retry-message" class="abp01-map-retry-message"><?php echo esc_html__('The map could not be loaded due to either a network error or a possible server issue.', 'abp01-trip-summary'); ?></div>
            <a id="abp01-map-retry" href="javascript:void(0)" class="button button-hero abp01-map-retry first" data-action="abp01-mapRetry"><?php echo esc_html__('Retry', 'abp01-trip-summary'); ?></a>
        </div>
    </div>
</script>