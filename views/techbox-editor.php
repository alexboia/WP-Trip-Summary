<?php
    defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
    var abp01_imgBase = '<?php echo $data->imgBaseUrl; ?>';
    var abp01_ajaxUrl = '<?php echo $data->ajaxUrl; ?>';
    var abp01_ajaxEditInfoAction = '<?php echo $data->ajaxEditInfoAction; ?>';
    var abp01_ajaxUploadTrackAction = '<?php echo $data->ajaxUploadTrackAction; ?>';
    var abp01_ajaxGetTrackAction = '<?php echo $data->ajaxGetTrackAction; ?>';
    var abp01_ajaxClearTrackAction = '<?php echo $data->ajaxClearTrackAction ?>';
    var abp01_ajaxClearInfoAction = '<?php echo $data->ajaxClearInfoAction; ?>';
    var abp01_tourType = '<?php echo $data->tourType ?>';

    var abp01_flashUploaderUrl = '<?php echo $data->flashUploaderUrl; ?>';
    var abp01_xapUploaderUrl = '<?php echo $data->xapUploaderUrl; ?>';
    var abp01_uploadMaxFileSize = <?php echo $data->uploadMaxFileSize; ?>;
    var abp01_uploadChunkSize = <?php echo $data->uploadChunkSize; ?>;
    var abp01_uploadKey = '<?php echo $data->uploadKey; ?>';

    var abp01_postId = <?php echo $data->postId; ?>;
    var abp01_hasTrack = <?php echo $data->hasTrack ? 'true' : 'false'; ?>;

    var abp01_baseTitle = '<?php echo __('Edit trip summary', 'abp01-trip-summary') ?>';
</script>

<div id="abp01-techbox-editor" style="display:none;">
    <input type="hidden" name="abp01-nonce" id="abp01-nonce" value="<?php echo $data->nonce ?>" />
    <input type="hidden" name="abp01-nonce-get" id="abp01-nonce-get" value="<?php echo $data->nonceGet; ?>" />

    <div id="abp01-editor-wrapper" class="abp01-editor-wrapper">
        <div class="abp01-editor-title-wrap">
            <div id="ctrl_abp01_editorTitle" class="abp01-editor-title">
                <span class="abp01-editor-icon"></span><?php echo __('Edit trip summary', 'abp01-trip-summary') ?>
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
                        <span class="dashicons dashicons-index-card"></span><?php echo __('Info', 'abp01-trip-summary') ?>
                    </a>
                </li>
                <li id="abp01-tab-map" class="abp01-tab abp01-tab-map tab">
                    <a href="#abp01-form-map" data-action="abp01-tab">
                        <span class="dashicons dashicons-chart-line"></span><?php echo __('Map', 'abp01-trip-summary'); ?>
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
            <a id="abp01-resetTechBox" href="javascript:void(0)" class="button button-large button-reset"><?php echo __('Clear', 'abp01-trip-summary'); ?></a>
            <a id="abp01-saveTechBox" href="javascript:void(0)" class="button button-primary button-large button-save-summary"><?php echo __('Save', 'abp01-trip-summary'); ?></a>
            <div class="abp01-clear"></div>
        </div>
    </div>
</div>

<script id="tpl-abp01-formInfo-unselected" type="text/x-kite">
    <div id="abp01-form-info-typeSelection" class="abp01-type-selector-container">
        <h3 class="abp01-form-info-selector-notice">
            <?php echo __('Chose the type of your tour'); ?>
        </h3>
        <a href="javascript:void(0)" class="button button-hero abp01-type-selector first" data-action="abp01-typeSelect" data-type="bike">
            <?php echo __('Biking', 'abp01-trip-summary'); ?>
        </a>
        <a href="javascript:void(0)" class="button button-hero abp01-type-selector" data-action="abp01-typeSelect" data-type="hiking">
            <?php echo __('Hiking', 'abp01-trip-summary'); ?>
        </a>
        <a href="javascript:void(0)" class="button button-hero abp01-type-selector" data-action="abp01-typeSelect" data-type="trainRide">
            <?php echo __('Train Ride', 'abp01-trip-summary'); ?>
        </a>
        <div class="abp01-clear"></div>
    </div>
</script>

<script id="tpl-abp01-formInfo-bikeTour" type="text/x-kite">
    <div id="abp01-form-info-bike">
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeDistance"><?php echo __('Total distance', 'abp01-trip-summary'); ?>:</label>
            <input type="text" id="ctrl_abp01_bikeDistance" name="ctrl_abp01_bikeDistance" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'bikeDistance'); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeTotalClimb"><?php echo __('Total climb', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_bikeTotalClimb" name="ctrl_abp01_bikeTotalClimb" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'bikeTotalClimb'); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeDifficultyLevel"><?php echo __('Difficulty level', 'abp01-trip-summary'); ?></label>
            <select id="ctrl_abp01_bikeDifficultyLevel" name="ctrl_abp01_bikeDifficultyLevel" class="ab01-input-select">
                <option value="0"><?php echo __('-- Choose an option --', 'abp01-trip-summary'); ?></option>
                <?php if (isset($data->difficultyLevels) && is_array($data->difficultyLevels)): ?>
                    <?php renderDifficultyLevelOptions($data->difficultyLevels, extractValueFromData($data, 'bikeDifficultyLevel')); ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_bikeAccess"><?php echo __('Access information', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_bikeAccess" name="ctrl_abp01_bikeAccess" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'bikeAccess') ?>" />
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Open during seasons', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_bikeRecommendedSeasons_container">
                <?php if (isset($data->recommendedSeasons) && is_array($data->recommendedSeasons)): ?>
                    <?php renderCheckboxOptions($data->recommendedSeasons, 'bikeRecommendedSeasons', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Path surface type', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_bikePathSurfaceType_container">
                <?php if (isset($data->pathSurfaceTypes) && is_array($data->pathSurfaceTypes)): ?>
                    <?php renderCheckboxOptions($data->pathSurfaceTypes, 'bikePathSurfaceType', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Bike type', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_bikeBikeType_container">
                <?php if (isset($data->bikeTypes) && is_array($data->bikeTypes)): ?>
                    <?php renderCheckboxOptions($data->bikeTypes, 'bikeBikeType', $data); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</script>

<script id="tpl-abp01-formInfo-hikingTour" type="text/x-kite">
    <div id="abp01-form-info-hiking">
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingDistance"><?php echo __('Total distance', 'abp01-trip-summary') ?></label>
            <input type="text" id="ctrl_abp01_hikingDistance" name="ctrl_abp01_hikingDistance" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'hikingDistance') ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingTotalClimb"><?php echo __('Total climb', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_hikingTotalClimb" name="ctrl_abp01_hikingTotalClimb" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'hikingTotalClimb'); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="abp01_hikingDifficultyLevel"><?php echo __('Difficulty level', 'abp01-trip-summary'); ?></label>
            <select name="abp01_hikingDifficultyLevel" id="abp01_hikingDifficultyLevel" class="abp01-input-select">
                <option value="0"><?php echo __('-- Choose an option --', 'abp01-trip-summary'); ?></option>
                <?php if (isset($data->difficultyLevels) && is_array($data->difficultyLevels)): ?>
                    <?php renderDifficultyLevelOptions($data->difficultyLevels, extractValueFromData($data, 'hikingDifficultyLevel')); ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingAccess"><?php echo __('Access information', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_hikingAccess" name="ctrl_abp01_hikingAccess" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'hikingAccess'); ?>" />
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Open during seasons', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_hikingRecommendedSeasons_container">
                <?php if (isset($data->recommendedSeasons) && is_array($data->recommendedSeasons)): ?>
                    <?php renderCheckboxOptions($data->recommendedSeasons, 'hikingRecommendedSeasons', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Path surface type', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_hikingSurfaceType_container">
                <?php if (isset($data->pathSurfaceTypes) && is_array($data->pathSurfaceTypes)): ?>
                    <?php renderCheckboxOptions($data->pathSurfaceTypes, 'hikingSurfaceType', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_hikingRouteMarkers"><?php echo __('Route markers', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_hikingRouteMarkers" name="ctrl_abp01_hikingRouteMarkers" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'hikingRouteMarkers'); ?>" />
        </div>
    </div>
</script>

<script id="tpl-abp01-formInfo-trainRide" type="text/x-kite">
    <div id="abp01-form-info-trainRide">
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideDistance"><?php echo __('Total distance', 'abp01-trip-summary') ?></label>
            <input type="text" id="ctrl_abp01_trainRideDistance" name="ctrl_abp01_trainRideDistance" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'trainRideDistance'); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideChangeNumber"><?php echo __('Exchanged trains', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_trainRideChangeNumber" name="ctrl_abp01_trainRideChangeNumber" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'trainRideChangeNumber'); ?>" />
        </div>
        <div class="abp01-form-line">
            <label for="ctrl_abp01_trainRideGauge"><?php echo __('Line gauge', 'abp01-trip-summary'); ?></label>
            <input type="text" id="ctrl_abp01_trainRideGauge" name="ctrl_abp01_trainRideGauge" class="abp01-input-text" value="<?php echo extractValueFromData($data, 'trainRideGauge') ?>" />
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Railroad operators', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_trainRideOperator_container">
                <?php if (isset($data->railroadOperators) && is_array($data->railroadOperators)): ?>
                    <?php renderCheckboxOptions($data->railroadOperators, 'trainRideOperator', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Line status', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_trainRideLineStatus_container">
                <?php if (isset($data->railroadLineStatuses) && is_array($data->railroadLineStatuses)): ?>
                    <?php renderCheckboxOptions($data->railroadLineStatuses, 'trainRideLineStatus', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Electrification status', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_trainRideElectrificationStatus_container">
                <?php if (isset($data->railroadElectrification) && is_array($data->railroadElectrification)): ?>
                    <?php renderCheckboxOptions($data->railroadElectrification, 'trainRideElectrificationStatus', $data); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="abp01-form-line">
            <label><?php echo __('Line type', 'abp01-trip-summary'); ?></label>
            <div id="ctrl_abp01_trainRideLineType_container">
                <?php if (isset($data->railroadLineTypes) && is_array($data->railroadLineTypes)): ?>
                    <?php renderCheckboxOptions($data->railroadLineTypes, 'trainRideLineType', $data); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</script>

<script id="tpl-abp01-formMap-unselected" type="text/x-kite">
    <div id="abp01-form-map-trackSelection" class="abp01-type-selector-container">
        <h3 class="abp01-form-map-selector-notice">
            <?php echo __('Upload a GPX track file', 'abp01-trip-summary'); ?>
        </h3>
        <a id="abp01-track-selector" href="javascript:void(0)" class="button button-hero abp01-track-selector first" data-action="abp01-trackSelect">
            <?php echo __('Chose file', 'abp01-trip-summary'); ?>
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
    </div>
</script>