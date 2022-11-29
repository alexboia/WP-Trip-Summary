<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class SettingsTests extends WP_UnitTestCase {
    use SettingsDataHelpers;

    public function tearDown() {
		parent::tearDown();
        delete_option(Abp01_Settings::OPT_SETTINGS_KEY);
    }

    public function test_canGetSettings_whenDefault() {
        $settings = $this->_getSettings();
        $this->assertEquals($this->_getDefaultSettings(), $this->_collectSettings($settings));
    }

    public function test_trySetInvalidUnitSystem() {
        foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $key => $label) {
            $this->_testWhenSettingInvalidUnitSystemTheOldOneIsPresereved($key);
        }
    }

    private function _testWhenSettingInvalidUnitSystemTheOldOneIsPresereved($initialUnitSystem) {
        $settings = $this->_getSettings();
        $settings->setUnitSystem($initialUnitSystem);
        $settings->setUnitSystem('bogus');

        $this->assertEquals($initialUnitSystem, 
            $settings->getUnitSystem());
    }

    public function test_trySetInvalidInitialViewerTab() {
        foreach (Abp01_Viewer::getAvailableTabs() as $key => $label) {
            $this->_testWhensettingInvalidInitialViewerTabTheOldOneIsPreserved($key);
        }
    }

    private function _testWhensettingInvalidInitialViewerTabTheOldOneIsPreserved($initialViewerTab) {
        $settings = $this->_getSettings();
        $settings->setInitialViewerTab($initialViewerTab);
        $settings->setInitialViewerTab('bogus_tab');

        $this->assertEquals($initialViewerTab, 
            $settings->getInitialViewerTab());
    }

    public function test_canConvertToPlainObject() {
        $settings = $this->_getSettings();
        $expectedData = $this->_generateTestSettings();

        $this->_fillSettingsWithGeneratedData($settings, 
            $expectedData);

        $asPlainObject = $settings->asPlainObject();

        $this->_assertSettingsPlainObjectCorrect($expectedData, 
            $asPlainObject);
    }

    private function _fillSettingsWithGeneratedData(Abp01_Settings $settings, $data) {
        $settings->setShowFullScreen($data->showFullScreen);
        $settings->setShowMagnifyingGlass($data->showMagnifyingGlass);
        $settings->setShowTeaser($data->showTeaser);
        $settings->setInitialViewerTab($data->initialViewerTab);
        $settings->setViewerItemLayout($data->viewerItemLayout);
        $settings->setViewerItemValueDisplayCount($data->viewerItemValueDisplayCount);
        $settings->setShowMapScale($data->showMapScale);
        $settings->setTopTeaserText($data->topTeaserText);
        $settings->setBottomTeaserText($data->bottomTeaserText);
        $settings->setUnitSystem($data->unitSystem);
        $settings->setTileLayers($data->tileLayers);
        $settings->setAllowTrackDownload($data->allowTrackDownload);
        $settings->setTrackLineColour($data->trackLineColour);
        $settings->setTrackLineWeight($data->trackLineWeight);
        $settings->setMapHeight($data->mapHeight);
        $settings->setShowMinMaxAltitude($data->showMinMaxAltitude);
        $settings->setShowAltitudeProfile($data->showAltitudeProfile);
        return $settings;
    }

    private function _assertSettingsPlainObjectCorrect($settingsData, $asPlainObject) {
        $this->assertNotEmpty($asPlainObject);

        $this->assertEquals($settingsData->showTeaser, 
            $asPlainObject->showTeaser);
        $this->assertEquals($settingsData->topTeaserText, 
            $asPlainObject->topTeaserText);
        $this->assertEquals($settingsData->bottomTeaserText, 
            $asPlainObject->bottomTeaserText);
        $this->assertEquals($settingsData->viewerItemLayout,
            $asPlainObject->viewerItemLayout);
        $this->assertEquals($settingsData->viewerItemValueDisplayCount,
            $asPlainObject->viewerItemValueDisplayCount);

        $tileLayers = $settingsData->tileLayers;
        $this->assertEquals($tileLayers[0], 
            $asPlainObject->tileLayer);

        $this->assertEquals($settingsData->showFullScreen, 
            $asPlainObject->showFullScreen);
        $this->assertEquals($settingsData->showMagnifyingGlass, 
            $asPlainObject->showMagnifyingGlass);
        $this->assertEquals($settingsData->unitSystem, 
            $asPlainObject->unitSystem);
        
        $expectedMeasurementUnits = $this->_getMeasurementUnits($settingsData->unitSystem);
        $this->assertEquals($expectedMeasurementUnits, 
            $asPlainObject->measurementUnits);

        $this->assertEquals($settingsData->showMapScale, 
            $asPlainObject->showMapScale);
        $this->assertEquals($settingsData->allowTrackDownload, 
            $asPlainObject->allowTrackDownload);
        $this->assertEquals($settingsData->trackLineColour, 
            $asPlainObject->trackLineColour);
        $this->assertEquals($settingsData->trackLineWeight, 
            $asPlainObject->trackLineWeight);
        $this->assertEquals($settingsData->mapHeight, 
            $asPlainObject->mapHeight);
        $this->assertEquals($settingsData->initialViewerTab, 
            $asPlainObject->initialViewerTab);
        $this->assertEquals($settingsData->showMinMaxAltitude, 
            $asPlainObject->showMinMaxAltitude);
        $this->assertEquals($settingsData->showAltitudeProfile, 
            $asPlainObject->showAltitudeProfile);
    }

    private function _getMeasurementUnits($unitSystem) {
        return Abp01_UnitSystem::create($unitSystem)
            ->asPlainObject();
    }

    public function test_canGetMeasurementUnits() {
        $settings = $this->_getSettings();
        foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $key => $label) {
            $settings->setUnitSystem($key);

            $this->assertEquals($this->_getMeasurementUnits($key), 
                $settings->getMeasurementUnits());
        }
    }

    public function test_canGetOptionLimits() {
        $settings = $this->_getSettings();
        $optionLimits = $settings->getOptionsLimits();

        $this->assertEquals($settings->getMinimumAllowedMapHeight(), 
            $optionLimits->minAllowedMapHeight);
        $this->assertEquals($settings->getMinimumAllowedTrackLineWeight(), 
            $optionLimits->minAllowedTrackLineWeight);
    }

    public function test_canSaveSettings() {
        $settings = $this->_getSettings();
        $expectedData = $this->_generateTestSettings();

        $this->_fillSettingsWithGeneratedData($settings, 
            $expectedData);

        $settings->saveSettings();
		$this->assertEquals($expectedData, 
            $this->_collectSettings($settings));
		
        $settings->clearSettingsCache();
        $this->assertEquals($expectedData, 
            $this->_collectSettings($settings));
    }

    public function test_canPurgeAllSettings_whenDefault() {
        $settings = $this->_getSettings();
        $settings->purgeAllSettings();
        $this->assertEquals($this->_getDefaultSettings(), $this->_collectSettings($settings));
    }

    public function test_canPurgeAllSettings_whenModifiedAndSaved() {
        $settings = $this->_getSettings();
        $settings->setTopTeaserText('Test top teaser text');
		$settings->setBottomTeaserText('Test bottom teaser text');
        $settings->saveSettings();
        $settings->clearSettingsCache();

        $settings->purgeAllSettings();
        $this->assertEquals($this->_getDefaultSettings(), $this->_collectSettings($settings));
    }

    private function _collectSettings(Abp01_Settings $settings) {
        $data = new stdClass();
        $data->showFullScreen = $settings->getShowFullScreen();
        $data->showMagnifyingGlass = $settings->getShowMagnifyingGlass();
        $data->showTeaser = $settings->getShowTeaser();
        $data->viewerItemLayout = $settings->getViewerItemLayout();
        $data->viewerItemValueDisplayCount = $settings->getViewerItemValueDisplayCount();
        $data->showMapScale = $settings->getShowMapScale();
        $data->topTeaserText = $settings->getTopTeaserText();
        $data->bottomTeaserText = $settings->getBottomTeaserText();
        $data->tileLayers = $settings->getTileLayers();
        $data->unitSystem = $settings->getUnitSystem();
        $data->allowTrackDownload = $settings->getAllowTrackDownload();
        $data->trackLineColour = $settings->getTrackLineColour();
        $data->trackLineWeight = $settings->getTrackLineWeight();
        $data->mapHeight = $settings->getMapHeight();
        $data->initialViewerTab = $settings->getInitialViewerTab();
        $data->showMinMaxAltitude = $settings->getShowMinMaxAltitude();
		$data->showAltitudeProfile = $settings->getShowAltitudeProfile();
        return $data;
    }

    public function test_trySetMapHeight_lowerThanMinimumAllowedHeight() {
        $faker = $this->_getFaker();
        $settings = $this->_getSettings();       

        $invalidMapHeight = $faker->numberBetween(0, 
            $settings->getMinimumAllowedMapHeight() - 1);

        $settings->setMapHeight($invalidMapHeight);
        $this->assertEquals($settings->getMinimumAllowedMapHeight(), 
            $settings->getMapHeight());
    }

    private function _getSettings() {
        return Abp01_Settings::getInstance();
    }
}