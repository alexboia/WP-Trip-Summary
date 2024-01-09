<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

	/** @var stdClass $data */
?>

<?php 
	$totalTabCount = abp01_count_frontend_viewer_tabs($data);
	$hasAdditionalTabs = abp01_frontend_viewer_has_additional_tabs($data);
	$tabWidth = abp01_frontend_determine_viewer_tab_width($totalTabCount);
?>

<?php if ($data && ($data->info->exists || $data->track->exists)): ?>
	<div id="abp01-techbox-frontend" class="abp01-techbox-frontend">
		<div id="abp01-techbox-title" class="abp01-techbox-title">
			<span class="abp01-techbox-icon"></span><?php echo esc_html__('Trip summary', 'abp01-trip-summary'); ?>
		</div>
		<div id="abp01-techbox-wrapper" class="abp01-techbox-wrapper">
			<?php if ($totalTabCount > 0): ?>
				<ul id="abp01-techbox-tabs" class="abp01-techbox-tabs">
					<?php if ($data->info->exists): ?>
						<li id="abp01-tab-info" class="abp01-tab abp01-tab-info <?php echo abp01_frontend_viewer_maybe_full_tab_css_class($totalTabCount); ?>" style="width: <?php echo $tabWidth ;?>;">
							<a href="#abp01-techbox-info"><span class="dashicons dashicons-index-card"></span><?php echo esc_html__('Prosaic details', 'abp01-trip-summary'); ?></a>
						</li>
					<?php endif; ?>
					<?php if ($data->track->exists): ?>
						<li id="abp01-tab-map" class="abp01-tab abp01-tab-map <?php echo abp01_frontend_viewer_maybe_full_tab_css_class($totalTabCount); ?>" style="width: <?php echo $tabWidth ;?>;">
							<a href="#abp01-techbox-map"><span class="dashicons dashicons-chart-line"></span><?php echo esc_html__('Map', 'abp01-trip-summary'); ?></a>
						</li>
					<?php endif; ?>
					<?php if ($hasAdditionalTabs): ?>
						<?php foreach ($data->additionalTabs as $tabId => $tabInfo): ?>
							<li id="<?php echo esc_attr($tabId); ?>" class="abp01-tab <?php echo esc_attr($tabId); ?> <?php echo abp01_frontend_viewer_maybe_full_tab_css_class($totalTabCount); ?>" style="width: <?php echo $tabWidth ;?>;">
								<a href="#<?php echo esc_attr($tabId) ?>-content"><span class="dashicons <?php echo !empty($tabInfo['icon']) ? esc_attr($tabInfo['icon']) : '' ?>"></span><?php echo esc_html($tabInfo['label']); ?></a>
							</li> 
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
			<div class="abp01-clear"></div>
			<div id="abp01-techbox-content" class="abp01-techbox-content">
				<?php if ($data->info->exists): ?>
					<div id="abp01-techbox-info" class="abp01-techbox-info" style="display: none;">
						<ul>
							<?php if ($data->info->isBikingTour): ?>
								<?php abp01_display_info_item($data, 'bikeDistance', __('Total distance', 'abp01-trip-summary'), $data->settings->measurementUnits->distanceUnit); ?>
								<?php abp01_display_info_item($data, 'bikeTotalClimb', __('Total climb', 'abp01-trip-summary'), $data->settings->measurementUnits->heightUnit); ?>
								<?php abp01_display_info_item($data, 'bikeDifficultyLevel', __('Difficulty level', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'bikeAccess', __('Access information', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'bikeRecommendedSeasons', __('Open during seasons', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'bikePathSurfaceType', __('Path surface type', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'bikeBikeType', __('Recommended bike type', 'abp01-trip-summary'), ''); ?>
							<?php elseif ($data->info->isHikingTour): ?>
								<?php abp01_display_info_item($data, 'hikingDistance', __('Total distance', 'abp01-trip-summary'), $data->settings->measurementUnits->distanceUnit); ?>
								<?php abp01_display_info_item($data, 'hikingTotalClimb', __('Total climb', 'abp01-trip-summary'), $data->settings->measurementUnits->heightUnit); ?>
								<?php abp01_display_info_item($data, 'hikingDifficultyLevel', __('Difficulty level', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'hikingAccess', __('Access information', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'hikingRecommendedSeasons', __('Open during seasons', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'hikingSurfaceType', __('Path surface type', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'hikingRouteMarkers', __('Path markers', 'abp01-trip-summary'), ''); ?>
							<?php elseif ($data->info->isTrainRideTour): ?>
								<?php abp01_display_info_item($data, 'trainRideDistance', __('Total distance', 'abp01-trip-summary'), $data->settings->measurementUnits->distanceUnit); ?>
								<?php abp01_display_info_item($data, 'trainRideChangeNumber', __('Exchanged trains', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'trainRideGauge', __('Line gauge', 'abp01-trip-summary'), $data->settings->measurementUnits->lengthUnit); ?>
								<?php abp01_display_info_item($data, 'trainRideOperator', __('Railroad operators', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'trainRideLineStatus', __('Line status', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'trainRideElectrificationStatus', __('Electrification status', 'abp01-trip-summary'), ''); ?>
								<?php abp01_display_info_item($data, 'trainRideLineType', __('Line type', 'abp01-trip-summary'), ''); ?>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>
				<?php if ($data->track->exists): ?>
					<div id="abp01-techbox-map" class="abp01-techbox-map" style="display: none;">
						<div id="abp01-map-container" class="abp01-map-container" data-role="map-container" style="height: <?php echo esc_attr($data->settings->mapHeight); ?>px;">
							<div id="abp01-map" class="abp01-map" data-role="map-holder"></div>
							<div id="abp01-map-retry-container" class="abp01-map-retry-container" style="display: none;">
								<div id="abp01-map-retry-message" class="abp01-map-retry-message"><?php echo esc_html__('The map could not be loaded due to either a network error or a possible server issue.', 'abp01-trip-summary'); ?></div>
								<a id="abp01-map-retry" class="abp01-map-retry" data-role="map-retry" href="javascript:void(0)"><?php echo esc_html__('Retry', 'abp01-trip-summary'); ?></a>
							</div>
						</div>
						<?php if ($data->settings->showAltitudeProfile): ?>
							<div id="abp01-altitude-profile-container"></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ($hasAdditionalTabs): ?>
					<?php foreach ($data->additionalTabs as $tabId => $tabInfo): ?>
						<div id="<?php echo esc_attr($tabId) ?>-content" class="abp01-additional-tab <?php echo esc_attr($tabId) ?>-content" style="display: none;">
							<?php do_action('abp01_additional_frontend_viewer_tab_content', 
								$tabId, 
								$tabInfo, 
								$data); ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php if ($data->settings->showTeaser): ?>
				<div id="abp01-techbox-content-skip-teaser" class="abp01-techbox-content-skip-teaser" style="display: none;">
					<a id="abp01-techbox-content-skip-teaser-action" href="javascript:void(0)"><?php echo esc_html($data->settings->bottomTeaserText); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>