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

class FrontendThemeDefaultTests extends WP_UnitTestCase {
    use GenericTestHelpers;

    public function test_canGetVersion() {
        $theme = $this->_getFrontendThemeDefault();
        $this->assertEquals($this->_getEnv()->getVersion(), $theme->getVersion());
    }

    public function test_canIncludeFrontendViewerStyles() {
        $theme = $this->_getFrontendThemeDefault();
        $theme->includeFrontendViewerStyles();

        $this->assertTrue(wp_style_is(Abp01_Includes::STYLE_FRONTEND_MAIN, 'enqueued'));
    }

    public function test_canRegisterFrontendViewHelpers() {
        $theme = $this->_getFrontendThemeDefault();
        $theme->registerFrontendViewerHelpers();

        $expectedFunctions = array(
            'abp01_extract_value_from_frontend_data',
            'abp01_format_info_item_value',
            'abp01_display_info_item'
        );

        foreach ($expectedFunctions as $fn) {
            $this->assertTrue(function_exists($fn));
        }
    }

    public function test_canRenderTopTeaser_whenShowTeaserTrue() {
        $theme = $this->_getFrontendThemeDefault();

        $data = $this->_getFrontendTopTeaserData(true, true, false);
        $this->_runTeaserRenderingTest($theme, $data, true);

        $data = $this->_getFrontendTopTeaserData(true, false, true);
        $this->_runTeaserRenderingTest($theme, $data, true);

        $data = $this->_getFrontendTopTeaserData(true, true, true);
        $this->_runTeaserRenderingTest($theme, $data, true);

        $data = $this->_getFrontendTopTeaserData(true, false, false);
        $this->_runTeaserRenderingTest($theme, $data, false);
    }

    public function test_canRenderTopTeaser_whenShowTeaserFalse() {
        $theme = $this->_getFrontendThemeDefault();

        $dataset = array(
            $this->_getFrontendTopTeaserData(false, true, false),
            $this->_getFrontendTopTeaserData(false, false, true),
            $this->_getFrontendTopTeaserData(false, true, true),
            $this->_getFrontendTopTeaserData(false, false, false)
        );

        foreach ($dataset as $data) {
            $this->_runTeaserRenderingTest($theme, $data, false);
        }
    }

    public function test_canRenderViewer_whenHasTripData() {
        $theme = $this->_getFrontendThemeDefault();

        if (!$this->_frontendViewHelperRegistered()) {
            $theme->registerFrontendViewerHelpers();
        }

        for ($i = 0; $i < 10; $i ++) {
            $dataset = array(
                $this->_getHikingTripData(true, true),
                $this->_getHikingTripData(false, true),
                $this->_getBikingTripData(true, true),
                $this->_getBikingTripData(false, true),
                $this->_getTrainRideTripData(true, true),
                $this->_getTrainRideTripData(false, true),

                $this->_getHikingTripData(true, false),
                $this->_getHikingTripData(false, false),
                $this->_getBikingTripData(true, false),
                $this->_getBikingTripData(false, false),
                $this->_getTrainRideTripData(true, false),
                $this->_getTrainRideTripData(false, false)
            );

            foreach ($dataset as $data) {
                $this->_runViewerRenderingTest($theme, $data, true);
            }
        }
    }

    public function test_canRenderViewer_whenHasTrackDataOnly() {
        $theme = $this->_getFrontendThemeDefault();

        if (!$this->_frontendViewHelperRegistered()) {
            $theme->registerFrontendViewerHelpers();
        }

        for ($i = 0; $i < 10; $i ++) {
            $dataset = array(
                $this->_getTrackDataOnly(true),
                $this->_getTrackDataOnly(false)
            );

            foreach ($dataset as $data) {
                $this->_runViewerRenderingTest($theme, $data, true);
            }
        }
    }

    public function test_canRenderViewer_whenHasNoData() {
        $theme = $this->_getFrontendThemeDefault();

        if (!$this->_frontendViewHelperRegistered()) {
            $theme->registerFrontendViewerHelpers();
        }
        
        $data = $this->_getNoData();
        $this->_runViewerRenderingTest($theme, $data, false);
    }

    private function _runTeaserRenderingTest($theme, $data, $expectedNotEmpty) {
        $contents = trim($theme->renderTeaser($data));
        if ($expectedNotEmpty) {
            $this->assertNotEmpty($contents);
        } else {
            $this->assertEmpty($contents);
        }
    }

    private function _runViewerRenderingTest($theme, $data, $expectedNotEmpty) {
        $contents = trim($theme->renderViewer($data));
        if ($expectedNotEmpty) {
            $this->assertNotEmpty($contents);
        } else {
            $this->assertEmpty($contents);
        }
    }

    private function _getNoData() {
        $data = new stdClass();

        $data->track = new stdClass();
        $data->track->exists = false;

        $data->info = new stdClass();
        $data->info->exists = false;

        return $data;
    }

    private function _getTrackDataOnly($showTeaser) {
        $data = new stdClass();
        $faker = $this->_getFaker();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->showAltitudeProfile = $faker->boolean();

        $data->info = new stdClass();
        $data->info->exists = false;
        $data->info->isBikingTour = false;
        $data->info->isHikingTour = false;
        $data->info->isTrainRideTour = false;

        $data->track = new stdClass();
        $data->track->exists = true;

        return $data;
    }
    
    private function _getBikingTripData($showTeaser, $trackExists) {
        $data = new stdClass();
        $faker = $this->_getFaker();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->measurementUnits = $faker->randomElement($this->_getAvailableMeasurementUnits());
        $data->settings->showAltitudeProfile = $faker->boolean();

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

    private function _getHikingTripData($showTeaser, $trackExists) {
        $data = new stdClass();
        $faker = $this->_getFaker();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->measurementUnits = $faker->randomElement($this->_getAvailableMeasurementUnits());
        $data->settings->showAltitudeProfile = $faker->boolean();

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

    private function _getTrainRideTripData($showTeaser, $trackExists) {
        $data = new stdClass();
        $faker = $this->_getFaker();

        $data->settings = new stdClass();
        $data->settings->showTeaser = $showTeaser;
        $data->settings->bottomTeaserText = 'Bottom teaser text';
        $data->settings->measurementUnits = $faker->randomElement($this->_getAvailableMeasurementUnits());
        $data->settings->showAltitudeProfile = $faker->boolean();

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

    private function _getFrontendTopTeaserData($showTeaser, $infoExists, $trackExists) {
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

    private function _randomOptionList($type) {
        $faker = $this->_getFaker();
        $count = $faker->numberBetween(1, 10);
        $list = array();

        for ($i = 0; $i < $count; $i ++) {
            $list[] = $this->_randomOption($type);
        }

        return $list;
    }

    private function _randomOption($type) {
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

    private function _getAvailableMeasurementUnits() {
        return array(
            (new Abp01_UnitSystem_Metric())->asPlainObject(),
            (new Abp01_UnitSystem_Imperial())->asPlainObject()
        );
    }

    private function _frontendViewHelperRegistered() {
        return function_exists('abp01_extract_value_from_frontend_data');
    }

    private function _getFrontendThemeDefault() {
        return new Abp01_FrontendTheme_Default($this->_getEnv());
    }
}