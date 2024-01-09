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

trait SettingsDataHelpers {
    use GenericTestHelpers;

    protected function _getDefaultSettings() {
        $defaults = new stdClass();
        $defaults->showFullScreen = true;
        $defaults->showMagnifyingGlass = true;
        $defaults->showTeaser = true;
        $defaults->initialViewerTab = Abp01_Viewer::TAB_INFO;
        $defaults->viewerItemLayout = Abp01_Viewer::ITEM_LAYOUT_HORIZONTAL;
        $defaults->viewerItemValueDisplayCount = 3;
        $defaults->showMapScale = true;
        $defaults->allowTrackDownload = true;
        $defaults->topTeaserText = $this->_getDefaultTopTeaserText();
        $defaults->bottomTeaserText = $this->_getDefaultBottomTeaserText();
        $defaults->tileLayers = array($this->_getDefaultTileLayer());
        $defaults->unitSystem = Abp01_UnitSystem::METRIC;
        $defaults->trackLineColour = '#0033ff';
        $defaults->trackLineWeight = 3;
        $defaults->mapHeight = 350;
        $defaults->showMinMaxAltitude = true;
		$defaults->showAltitudeProfile = true;
        $defaults->jsonLdEnabled = false;
        return $defaults;
    }

    private function _getDefaultTileLayer() {
        $tileLayer = new stdClass();
        $tileLayer->url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        $tileLayer->attributionTxt = 'OpenStreetMap & Contributors';
        $tileLayer->attributionUrl = 'https://www.openstreetmap.org/copyright';
        $tileLayer->apiKey = null;
        return $tileLayer;
    }

    private function _getDefaultBottomTeaserText() {
        return __('It looks like you skipped the story. You should check it out. Click here to go back to beginning', 'abp01-trip-summary');
    }

    private function _getDefaultTopTeaserText() {
        return __('For the pragmatic sort, there is also a trip summary at the bottom of this page. Click here to consult it', 'abp01-trip-summary');
    }

    protected function _generateTestSettings() {
        $faker = $this->_getFaker();

        $tileLayer = new stdClass();
        $tileLayer->url = 'http://{s}.tile.example.com/{z}/{x}/{y}.png';
        $tileLayer->attributionTxt = 'Example.com & Contributors';
        $tileLayer->attributionUrl = 'http://tile.example.com/copyright';
        $tileLayer->apiKey = $faker->uuid;

        $settings = new stdClass();
        $settings->showFullScreen = false;
        $settings->showMagnifyingGlass = false;
        $settings->showTeaser = false;
        $settings->showMapScale = false;
        $settings->allowTrackDownload = false;
        $settings->topTeaserText = $faker->sentence();
        $settings->bottomTeaserText = $faker->sentence();
        $settings->tileLayers = array($tileLayer);
        $settings->unitSystem = Abp01_UnitSystem::IMPERIAL;
        $settings->trackLineColour = '#FFCC00';
        $settings->trackLineWeight = 10;
        $settings->mapHeight = 1111;
        $settings->initialViewerTab = $faker->randomElement(array_keys(Abp01_Viewer::getAvailableTabs()));
        $settings->showMinMaxAltitude = true;
		$settings->showAltitudeProfile = false;
        $settings->viewerItemLayout = $faker->randomElement(array_keys(Abp01_Viewer::getAvailableItemLayouts()));
        $settings->viewerItemValueDisplayCount = $faker->numberBetween(0, 5);
        $settings->jsonLdEnabled = ($faker->numberBetween(0, 100) % 2 == 0);

        return $settings;
    }

    protected function _generateTestSettingsOptionLimits() {
        $faker = $this->_getFaker();
        $data = new stdClass();
		$data->minAllowedMapHeight = $faker->randomNumber();
		$data->minAllowedTrackLineWeight = $faker->randomNumber();
        $data->minViewerItemValueDisplayCount = $faker->randomNumber();
		return $data;
    }
}