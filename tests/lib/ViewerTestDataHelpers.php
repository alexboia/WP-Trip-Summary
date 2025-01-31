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

trait ViewerTestDataHelpers {
    use GenericTestHelpers;

    protected function _getNoData() {
        $data = new stdClass();

        $data->track = new stdClass();
        $data->track->exists = false;

        $data->info = new stdClass();
        $data->info->exists = false;

        return $data;
    }

    private function _getRandomViewerItemLayout() {
        $faker = $this->_getFaker();
        $items = Abp01_Viewer::getAvailableItemLayouts();
        return $faker->randomElement($items);
    }

    protected function _getTrackDataOnly($showTeaser) {
        $faker = $this->_getFaker();
        $data = $this->_createNewFrontendViewerData();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->showAltitudeProfile = $faker->boolean();
        $data->settings->mapHeight = $faker->numberBetween(450, 1111);
        $data->settings->viewerItemValueDisplayCount = 3;
        $data->settings->viewerItemLayout = $this->_getRandomViewerItemLayout();

        $data->info = new stdClass();
        $data->info->exists = false;
        $data->info->isBikingTour = false;
        $data->info->isHikingTour = false;
        $data->info->isTrainRideTour = false;

        $data->track = new stdClass();
        $data->track->exists = true;

        return $data;
    }

    protected function _getRandomTrackDataOnly() {
        $faker = $this->_getFaker();
        return $this->_getTrackDataOnly($faker->boolean());
    }

    private function _createNewFrontendViewerData() {
        $faker = $this->_getFaker();
        $postId = $faker->randomNumber();

        $data = new stdClass();
        $data->postId = $postId;
		$data->ajaxUrl = $faker->url;
		$data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;
		$data->downloadTrackAction = ABP01_ACTION_DOWNLOAD_TRACK;
		$data->imgBaseUrl = $faker->url;
        $data->nonceGet = $faker->randomAscii;
	    $data->nonceDownload = $faker->randomAscii;

        return $data;
    }

    protected function _getBikingTripData($showTeaser, $trackExists) {
        $faker = $this->_getFaker();
        $data = $this->_createNewFrontendViewerData();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->measurementUnits = $faker->randomElement($this->_getAvailableMeasurementUnits());
        $data->settings->showAltitudeProfile = $faker->boolean();
        $data->settings->mapHeight = $faker->numberBetween(450, 1111);
        $data->settings->viewerItemValueDisplayCount = 3;
        $data->settings->viewerItemLayout = $this->_getRandomViewerItemLayout();

        $data->info = new stdClass();
        $data->info->exists = true;
        $data->info->isBikingTour = true;
        $data->info->isHikingTour = false;
        $data->info->isTrainRideTour = false;

        $data->info->bikeDistance = $faker->boolean() 
            ? $faker->numberBetween(1) 
            : 0;
        $data->info->bikeTotalClimb = $faker->boolean() 
            ? $faker->numberBetween(1) 
            : 0;
        $data->info->bikeDifficultyLevel = $faker->boolean() 
            ? $this->_randomOption(Abp01_Lookup::DIFFICULTY_LEVEL) 
            : null;
        $data->info->bikeAccess = $faker->boolean() 
            ? $faker->sentence() 
            : null;
        $data->info->bikeRecommendedSeasons = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::RECOMMEND_SEASONS)
            : null;
        $data->info->bikePathSurfaceType = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::PATH_SURFACE_TYPE) 
            : null;
        $data->info->bikeBikeType = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::BIKE_TYPE) 
            : null;

        $data->track = new stdClass();
        $data->track->exists = $trackExists;

        return $data;
    }

    protected function _getRandomBikingTripData() {
        $faker = $this->_getFaker();
        return $this->_getBikingTripData($faker->boolean(), 
            $faker->boolean());
    }

    protected function _getHikingTripData($showTeaser, $trackExists) {
        $faker = $this->_getFaker();
        $data = $this->_createNewFrontendViewerData();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->measurementUnits = $faker->randomElement($this->_getAvailableMeasurementUnits());
        $data->settings->showAltitudeProfile = $faker->boolean();
        $data->settings->mapHeight = $faker->numberBetween(450, 1111);
        $data->settings->viewerItemValueDisplayCount = 3;
        $data->settings->viewerItemLayout = $this->_getRandomViewerItemLayout();

        $data->info = new stdClass();
        $data->info->exists = true;
        $data->info->isBikingTour = false;
        $data->info->isHikingTour = true;
        $data->info->isTrainRideTour = false;

        $data->info->hikingDistance = $faker->boolean() 
            ? $faker->numberBetween(1) 
            : 0;
        $data->info->hikingTotalClimb = $faker->boolean() 
            ? $faker->numberBetween(1) 
            : 0;
        $data->info->hikingDifficultyLevel = $faker->boolean() 
            ? $this->_randomOption(Abp01_Lookup::DIFFICULTY_LEVEL) 
            : null;
        $data->info->hikingAccess = $faker->boolean()
            ? $faker->sentence()
            : null;
        $data->info->hikingRecommendedSeasons = $faker->boolean()
            ? $this->_randomOptionList(Abp01_Lookup::RECOMMEND_SEASONS)
            : null;
        $data->info->hikingSurfaceType = $faker->boolean()
            ? $this->_randomOptionList(Abp01_Lookup::PATH_SURFACE_TYPE)
            : null;
        $data->info->hikingRouteMarkers = $faker->boolean()
            ? $faker->sentence()
            : null;

        $data->track = new stdClass();
        $data->track->exists = $trackExists;

        return $data;
    }

    protected function _getRandomHikingTripData() {
        $faker = $this->_getFaker();
        return $this->_getHikingTripData($faker->boolean(), 
            $faker->boolean());
    }

    protected function _getTrainRideTripData($showTeaser, $trackExists) {
        $faker = $this->_getFaker();
        $data = $this->_createNewFrontendViewerData();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->measurementUnits = $faker->randomElement($this->_getAvailableMeasurementUnits());
        $data->settings->showAltitudeProfile = $faker->boolean();
        $data->settings->mapHeight = $faker->numberBetween(450, 1111);
        $data->settings->viewerItemValueDisplayCount = 3;
        $data->settings->viewerItemLayout = $this->_getRandomViewerItemLayout();

        $data->info = new stdClass();
        $data->info->exists = true;
        $data->info->isBikingTour = false;
        $data->info->isHikingTour = false;
        $data->info->isTrainRideTour = true;

        $data->info->trainRideDistance = $faker->boolean() 
            ? $faker->numberBetween(1) 
            : 0;
        $data->info->trainRideChangeNumber = $faker->boolean() 
            ? $faker->numberBetween(1, 5) 
            : 0;
        $data->info->trainRideGauge = $faker->boolean() 
            ? $faker->numberBetween(600, 1000) 
            : 0;
        $data->info->trainRideOperator = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::RAILROAD_OPERATOR) 
            : null;
        $data->info->trainRideLineStatus = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::RAILROAD_LINE_STATUS) 
            : null;
        $data->info->trainRideElectrificationStatus = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::RAILROAD_ELECTRIFICATION) 
            : null;
        $data->info->trainRideLineType = $faker->boolean() 
            ? $this->_randomOptionList(Abp01_Lookup::RAILROAD_LINE_TYPE) 
            : null;

        $data->track = new stdClass();
        $data->track->exists = $trackExists;

        return $data;
    }

    protected function _getRandomTrainRideTripData() {
        $faker = $this->_getFaker();
        return $this->_getTrainRideTripData($faker->boolean(), 
            $faker->boolean());
    }

    protected function _getFrontendTopTeaserData($showTeaser, $infoExists, $trackExists) {
        $data = new stdClass();
        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->topTeaserText = 'Test top teaser';

        $data->info = new stdClass();
        $data->info->exists = $infoExists;

        $data->track = new stdClass();
        $data->track->exists = $trackExists;

        return $data;
    }

    protected function _getRandomFrontendTopTeaserData() {
        $faker = $this->_getFaker();
        return $this->_getFrontendTopTeaserData($faker->boolean(), 
            $faker->boolean(), 
            $faker->boolean());
    }

    protected function _randomOptionList($type) {
        $faker = $this->_getFaker();
        $count = $faker->numberBetween(1, 10);
        $list = array();

        for ($i = 0; $i < $count; $i ++) {
            $list[] = $this->_randomOption($type);
        }

        return $list;
    }

    protected function _randomOption($type) {
        $faker = $this->_getFaker();
        return $this->_createOption($faker->numberBetween(1), 
            $faker->randomAscii, 
            $type);
    }

    private function _createOption($id, $label, $type) {
		if (is_string($label)) {
			$label = array(
				'defaultLabel' => $label,
				'translatedLabel' => $label
			);
		}

		$option = new stdClass();
		$option->id = $id;		
		$option->type = $type;
		$option->defaultLabel = $label['defaultLabel'];
		$option->hasTranslation = !empty($label['translatedLabel']);
		$option->label = $option->hasTranslation
			? $label['translatedLabel'] 
			: $label['defaultLabel'];

		return $option;
	}

    protected function _getAvailableMeasurementUnits() {
        return array(
            (new Abp01_UnitSystem_Metric())->asPlainObject(),
            (new Abp01_UnitSystem_Imperial())->asPlainObject()
        );
    }
}