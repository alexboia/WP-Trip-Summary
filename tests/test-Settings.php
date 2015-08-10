<?php
class SettingsTests extends WP_UnitTestCase {
    public function tearDown() {
        delete_option(Abp01_Settings::OPT_SETTINGS_KEY);
    }

    public function testCanGetSettings_whenDefault() {
        $settings = $this->_getSettings();
        $this->assertEquals($this->_getDefaults(), $this->_collectSettings($settings));
    }

    public function testTrySetInvalidUnitSystem() {
        $settings = $this->_getSettings();

        $settings->setUnitSystem(Abp01_UnitSystem::IMPERIAL);
        $settings->setUnitSystem('bogus');
        $this->assertEquals(Abp01_UnitSystem::IMPERIAL, $settings->getUnitSystem());

        $settings->setUnitSystem(Abp01_UnitSystem::METRIC);
        $settings->setUnitSystem('bogus');
        $this->assertEquals(Abp01_UnitSystem::METRIC, $settings->getUnitSystem());
    }

    public function testCanSaveSettings() {
        $tileLayer = new stdClass();
        $tileLayer->url = 'http://{s}.tile.example.com/{z}/{x}/{y}.png';
        $tileLayer->attributionTxt = 'Example.com & Contributors';
        $tileLayer->attributionUrl = 'http://tile.example.com/copyright';

        $expected = new stdClass();
        $expected->showFullScreen = false;
        $expected->showMagnifyingGlass = false;
        $expected->showTeaser = false;
        $expected->showMapScale = false;
        $expected->allowTrackDownload = false;
        $expected->topTeaserText = 'Test top teaser text';
        $expected->bottomTeaserText = 'Test bottom teaser text';
        $expected->tileLayers = array($tileLayer);
        $expected->unitSystem = Abp01_UnitSystem::IMPERIAL;

        $settings = $this->_getSettings();
        $settings->setShowFullScreen($expected->showFullScreen);
        $settings->setShowMagnifyingGlass($expected->showMagnifyingGlass);
        $settings->setShowTeaser($expected->showTeaser);
        $settings->setShowMapScale($expected->showMapScale);
        $settings->setTopTeaserText($expected->topTeaserText);
        $settings->setBottomTeaserText($expected->bottomTeaserText);
        $settings->setUnitSystem($expected->unitSystem);
        $settings->setTileLayers($expected->tileLayers);
        $settings->setAllowTrackDownload($expected->allowTrackDownload);

        $settings->saveSettings();
		$this->assertEquals($expected, $this->_collectSettings($settings));
		
        $settings->clearSettingsCache();
        $this->assertEquals($expected, $this->_collectSettings($settings));
    }

    public function testCanPurgeAllSettings_whenDefault() {
        $settings = $this->_getSettings();
        $settings->purgeAllSettings();
        $this->assertEquals($this->_getDefaults(), $this->_collectSettings($settings));
    }

    public function testCanPurgeAllSettings_whenModifiedAndSaved() {
        $settings = $this->_getSettings();
        $settings->setTopTeaserText('Test top teaser text');
		$settings->setBottomTeaserText('Test bottom teaser text');
        $settings->saveSettings();
        $settings->clearSettingsCache();

        $settings->purgeAllSettings();
        $this->assertEquals($this->_getDefaults(), $this->_collectSettings($settings));
    }

    private function _collectSettings(Abp01_Settings $settings) {
        $data = new stdClass();
        $data->showFullScreen = $settings->getShowFullScreen();
        $data->showMagnifyingGlass = $settings->getShowMagnifyingGlass();
        $data->showTeaser = $settings->getShowTeaser();
        $data->showMapScale = $settings->getShowMapScale();
        $data->topTeaserText = $settings->getTopTeaserText();
        $data->bottomTeaserText = $settings->getBottomTeaserText();
        $data->tileLayers = $settings->getTileLayers();
        $data->unitSystem = $settings->getUnitSystem();
        $data->allowTrackDownload = $settings->getAllowTrackDownload();
        return $data;
    }

    private function _getDefaults() {
        $defaults = new stdClass();
        $defaults->showFullScreen = true;
        $defaults->showMagnifyingGlass = true;
        $defaults->showTeaser = true;
        $defaults->showMapScale = true;
        $defaults->allowTrackDownload = true;
        $defaults->topTeaserText = $this->_getExpectedDefaultTopTeaserText();
        $defaults->bottomTeaserText = $this->_getExpectedDefaultBottomTeaserText();
        $defaults->tileLayers = array($this->_getExpectedDefaultTileLayer());
        $defaults->unitSystem = Abp01_UnitSystem::METRIC;
        return $defaults;
    }

    private function _getExpectedDefaultTileLayer() {
        $tileLayer = new stdClass();
        $tileLayer->url = 'http://{s}.tile.osm.org/{z}/{x}/{y}.png';
        $tileLayer->attributionTxt = 'OpenStreetMap & Contributors';
        $tileLayer->attributionUrl = 'http://osm.org/copyright';
        return $tileLayer;
    }

    private function _getExpectedDefaultBottomTeaserText() {
        return __('It looks like you skipped the story. You should check it out. Click here to go back to beginning', 'abp01-trip-summary');
    }

    private function _getExpectedDefaultTopTeaserText() {
        return __('For the pragmatic sort, there is also a trip summary at the bottom of this page. Click here to consult it', 'abp01-trip-summary');
    }

    private function _getSettings() {
        return Abp01_Settings::getInstance();
    }
}