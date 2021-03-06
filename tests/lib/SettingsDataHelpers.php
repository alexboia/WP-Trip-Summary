<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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
        $defaults->showMapScale = true;
        $defaults->allowTrackDownload = true;
        $defaults->topTeaserText = $this->_getDefaultTopTeaserText();
        $defaults->bottomTeaserText = $this->_getDefaultBottomTeaserText();
        $defaults->tileLayers = array($this->_getDefaultTileLayer());
        $defaults->unitSystem = Abp01_UnitSystem::METRIC;
        $defaults->trackLineColour = '#0033ff';
        $defaults->trackLineWeight = 3;
        $defaults->mapHeight = 350;
        $defaults->initialViewerTab = Abp01_Viewer::TAB_INFO;
        $defaults->showMinMaxAltitude = true;
		$defaults->showAltitudeProfile = true;
        return $defaults;
    }

    private function _getDefaultTileLayer() {
        $tileLayer = new stdClass();
        $tileLayer->url = 'http://{s}.tile.osm.org/{z}/{x}/{y}.png';
        $tileLayer->attributionTxt = 'OpenStreetMap & Contributors';
        $tileLayer->attributionUrl = 'http://osm.org/copyright';
        return $tileLayer;
    }

    private function _getDefaultBottomTeaserText() {
        return __('It looks like you skipped the story. You should check it out. Click here to go back to beginning', 'abp01-trip-summary');
    }

    private function _getDefaultTopTeaserText() {
        return __('For the pragmatic sort, there is also a trip summary at the bottom of this page. Click here to consult it', 'abp01-trip-summary');
    }

    protected function _generateTestSettings() {
        $tileLayer = new stdClass();
        $tileLayer->url = 'http://{s}.tile.example.com/{z}/{x}/{y}.png';
        $tileLayer->attributionTxt = 'Example.com & Contributors';
        $tileLayer->attributionUrl = 'http://tile.example.com/copyright';

        $settings = new stdClass();
        $settings->showFullScreen = false;
        $settings->showMagnifyingGlass = false;
        $settings->showTeaser = false;
        $settings->showMapScale = false;
        $settings->allowTrackDownload = false;
        $settings->topTeaserText = 'Test top teaser text';
        $settings->bottomTeaserText = 'Test bottom teaser text';
        $settings->tileLayers = array($tileLayer);
        $settings->unitSystem = Abp01_UnitSystem::IMPERIAL;
        $settings->trackLineColour = '#FFCC00';
        $settings->trackLineWeight = 10;
        $settings->mapHeight = 1111;
        $settings->initialViewerTab = Abp01_Viewer::TAB_MAP;
        $settings->showMinMaxAltitude = true;
		$settings->showAltitudeProfile = false;

        return $settings;
    }

    protected function _generateTestSettingsOptionLimits() {
        $faker = $this->_getFaker();
        $data = new stdClass();
		$data->minAllowedMapHeight = $faker->randomNumber();
		$data->minAllowedTrackLineWeight = $faker->randomNumber();
		return $data;
    }
}