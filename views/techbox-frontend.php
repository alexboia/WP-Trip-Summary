<?php
    defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
    var abp01_imgBase = '<?php echo $data->imgBaseUrl; ?>';
    var abp01_ajaxUrl = '<?php echo $data->ajaxUrl; ?>';
    var abp01_ajaxGetTrackAction = '<?php echo $data->ajaxGetTrackAction; ?>';

    var abp01_hasInfo = <?php echo $data->info->exists ? 'true' : 'false' ?>;
    var abp01_hasTrack = <?php echo $data->track->exists ? 'true' : 'false' ?>;
    var abp01_postId = <?php echo $data->postId; ?>;
    var abp01_nonceGet = '<?php echo $data->nonceGet; ?>';
</script>

<?php if ($data && ($data->info->exists || $data->track->exists)): ?>
    <div id="abp01-techbox-frontend" class="abp01-techbox-frontend">
        <div id="abp01-techbox-title" class="abp0-techbox-title">
            <span class="abp01-techbox-icon"></span><?php echo __('Trip summary', 'abp01-trip-summary'); ?>
        </div>
        <div id="abp01-techbox-wrapper" class="abp01-techbox-wrapper">
            <?php if ($data->info->exists && $data->track->exists): ?>
                <ul id="abp01-techbox-tabs" class="abp01-techbox-tabs">
                    <li id="abp01-tab-info" class="abp01-tab abp01-tab-info <?php echo !$data->track->exists ? 'abp01-full-tab' : ''; ?>">
                        <a href="#abp01-techbox-info"><span class="dashicons dashicons-index-card"></span><?php echo __('Prosaic details', 'abp01-trip-summary'); ?></a>
                    </li>
                    <li id="abp01-tab-map" class="abp01-tab abp01-tab-map <?php echo !$data->info->exists ? 'abp01-full-tab' : ''; ?>">
                        <a href="#abp01-techbox-map"><span class="dashicons dashicons-chart-line"></span><?php echo __('Map', 'abp01-trip-summary'); ?></a>
                    </li>
                </ul>
            <?php endif; ?>
            <div class="abp01-clear"></div>
            <div id="abp01-techbox-content" class="abp01-techbox-content">
                <?php if ($data->info->exists): ?>
                    <div id="abp01-techbox-info" class="abp01-techbox-info" style="<?php echo $data->track->exists ? 'display: none;' : ''; ?>">
                        <ul>
                            <?php if ($data->info->isBikingTour): ?>
                                <?php abp01_display_info_item($data, 'bikeDistance', __('Total distance', 'abp01-trip-summary'), 'km'); ?>
                                <?php abp01_display_info_item($data, 'bikeTotalClimb', __('Total climb', 'abp01-trip-summary'), 'm'); ?>
                                <?php abp01_display_info_item($data, 'bikeDifficultyLevel', __('Difficulty level', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'bikeAccess', __('Access information', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'bikeRecommendedSeasons', __('Open during seasons', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'bikePathSurfaceType', __('Path surface type', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'bikeBikeType', __('Recommended bike type', 'abp01-trip-summary'), ''); ?>
                            <?php elseif ($data->info->isHikingTour): ?>
                                <?php abp01_display_info_item($data, 'hikingDistance', __('Total distance', 'abp01-trip-summary'), 'km'); ?>
                                <?php abp01_display_info_item($data, 'hikingTotalClimb', __('Total climb', 'abp01-trip-summary'), 'm'); ?>
                                <?php abp01_display_info_item($data, 'hikingDifficultyLevel', __('Difficulty level', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'hikingAccess', __('Access information', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'hikingRecommendedSeasons', __('Open during seasons', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'hikingSurfaceType', __('Path surface type', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'hikingRouteMarkers', __('Path markers', 'abp01-trip-summary'), ''); ?>
                            <?php elseif ($data->info->isTrainRideTour): ?>
                                <?php abp01_display_info_item($data, 'trainRideDistance', __('Total distance', 'abp01-trip-summary'), 'km'); ?>
                                <?php abp01_display_info_item($data, 'trainRideChangeNumber', __('Exchanged trains', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'trainRideGauge', __('Line gauge', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'trainRideOperator', __('Railroad operators', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'trainRideLineStatus', __('Line status', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'trainRideElectrificationStatus', __('Electrification status', 'abp01-trip-summary'), ''); ?>
                                <?php abp01_display_info_item($data, 'trainRideLineType', __('Line type', 'abp01-trip-summary'), ''); ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($data->track->exists): ?>
                    <div id="abp01-techbox-map" class="abp01-techbox-map" style="<?php echo $data->info->exists ? 'display: none;' : ''; ?>">
                        <div id="abp01-map-container" class="abp01-map-container" data-role="map-container">
                            <div id="abp01-map" class="abp01-map" data-role="map-holder"></div>
                            <a id="abp01-map-retry" class="abp01-map-retry" data-role="map-retry" href="javascript:void(0)" style="display: none;"><?php echo __('Retry', 'abp01-trip-summary'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div id="abp01-techbox-content-skip-teaser" class="abp01-techbox-content-skip-teaser" style="display: none;">
                <a id="abp01-techbox-content-skip-teaser-action" href="javascript:void(0)"><?php echo __('It looks like you skipped the story. You should check it out. Click here to go back to beginning', 'abp01-trip-summary'); ?></a>
            </div>
        </div>
    </div>
<?php endif; ?>