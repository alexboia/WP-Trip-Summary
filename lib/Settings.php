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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

/**
 * Provides the means of managing plug-in settings. 
 * It contains methods to get, set and persist plug-in settings.
 * The settings are retrieved automatically the first time one of these events happen:
 * - An option is read;
 * - An option is set to a new value.
 * In order to be persisted, however, the saveSettings() method has to be called explicitly.
 * It uses the WP options API (see https://codex.wordpress.org/Options_API) to read and persist settings.
 * 
 * @package WP-Trip-Summary
 * */
class Abp01_Settings {
	const MINIMUM_ALLOWED_MAP_HEIGHT = 350;

	const MINIMUM_ALLOWED_TRACK_LINE_WEIGHT = 1;

	const MINIMUM_VIEWER_ITEM_DISPLAY_COUNT = 0;

	/**
	 * Key for the "show teaser" setting
	 * 
	 * @var string
	 * */
	const OPT_TEASER_SHOW = 'showTeaser';

	/**
	 * Key for the "top teaser text" setting
	 * 
	 * @var string
	 * */
	const OPT_TEASER_TOP = 'teaserTopTxt';

	/**
	 * Key for the "bottom teaser text" setting
	 * 
	 * @var string
	 * */
	const OPT_TEASER_BOTTOM = 'teaserBottomTxt';

	/**
	 * Key for "selected viewer tab" setting
	 * 
	 * @var string
	 */
	const OPT_INITIAL_VIEWER_TAB = 'initialViewerTab';

	/**
	 * Key for "chose how multi-value items are laid out" setting
	 * 
	 * @var string
	 */
	const OPT_VIEWER_ITEM_LAYOUT = 'viewerItemLayout';

	/**
	 * Key for "chose how many values of a multi-valued item are displayed" setting
	 * 
	 * @var string
	 */
	const OPT_VIEWER_ITEM_VALUE_DISPLAY_COUNT = 'viewerItemValueDisplayCount';

	/**
	 * Key for the tile layer settings
	 * 
	 * @var string
	 * */
	const OPT_MAP_TILE_LAYER_URLS = 'mapTileLayerUrls';

	/**
	 * Key for the "show magnifying glass" setting
	 * 
	 * @var string
	 * */
	const OPT_MAP_FEATURES_MAGNIFYING_GLASS_SHOW = 'mapMagnifyingGlassShow';

	/**
	 * Key for the "show full screen" setting
	 * 
	 * @var string
	 * */
	const OPT_MAP_FEATURES_FULL_SCREEN_SHOW = 'mapFullScreenShow';

	/**
	 * Key for the "show map scale" setting
	 * 
	 * @var string
	 * */
	const OPT_MAP_FEATURES_SCALE_SHOW = 'mapScaleShow';

	/**
	 * Key for the unit system setting
	 * 
	 * @var string
	 * */
	const OPT_UNIT_SYSTEM = 'unitSystem';

	/**
	 * Key for the "allow track download" setting
	 * 
	 * @var string
	 * */
	const OPT_ALLOW_TRACK_DOWNLOAD = 'allowTrackDownload';

	/**
	 * Key for the "track line colour" setting
	 * 
	 * @var string
	 * */
	const OPT_TRACK_LINE_COLOUR = 'trackLineColour';

	/**
	 * Key for the "track line weight" setting
	 * 
	 * @var string
	 */
	const OPT_TRACK_LINE_WEIGHT = 'trackLineWeight';

	/**
	 * Key for the "show min max altitude" setting
	 * 
	 * @var string
	 */
	const OPT_MAP_FEATURES_MINMAX_ALTITUDE = 'minMaxAltitudeShow';

	/**
	 * Key for "show altitude profile" setting
	 * 
	 * @var string
	 */
	const OPT_MAP_FEATURES_ALTITUDE_PROFILE = 'altitudeProfileShow';

	/**
	 * Key for "map height" setting
	 * 
	 * @var string
	 */
	const OPT_MAP_HEIGHT = 'mapHeight';

	/**
	 * Key for "json ld enabled" setting
	 * 
	 * @var string
	 */
	const OPT_JSONLD_ENABLED = 'jsonLdEnabled';

	/**
	 * The key used to store the serialized settings, using the WP options API
	 * 
	 * @var string
	 * */
	const OPT_SETTINGS_KEY = 'abp01.settings';

	/**
	 * The Abp01_Settings singleton instance
	 * 
	 * @var Abp01_Settings
	 */
	private static $_instance = null;

	/**
	 * Holds a cache of the setting array, to avoid repeatedly looking up the settings
	 * 
	 * @var array
	 * */
	private $_data = null;

	private function __construct() {
		return;
	}

	public function __clone() {
		throw new Exception('Cloning a singleton of type ' . __CLASS__ . ' is not allowed');
	}

	/**
	 * @return Abp01_Settings
	 * */
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Loads the settings if the local cache is not yet set.
	 * The cache is considered unset if it has a null value.
	 * If no data is found, the cache is initialized with an empty array.
	 * 
	 * @return void
	 * */
	private function _loadSettingsIfNeeded() {
		if ($this->_data === null) {
			$this->_data = get_option(self::OPT_SETTINGS_KEY, array());
			if (!is_array($this->_data)) {
				$this->_data = array();
			}
		}
	}

	/**
	 * Fetch the value for the given option key, 
	 * 	with the given type and default value.
	 * 
	 * @param string $key The option key
	 * @param string $type The option value type
	 * @param mixed $default The default option value, returned if no value found for given option key
	 * 
	 * @return mixed The option value
	 */
	private function _getOption($key, $type, $default) {
		$this->_loadSettingsIfNeeded();
		$optionValue = isset($this->_data[$key]) ? $this->_data[$key] : $default;
		if (!settype($optionValue, $type)) {
			$optionValue = $default;
		}
		$this->_data[$key] = $optionValue;
		return $optionValue;
	}

	/**
	 * Sets the option described by the given key, 
	 * 	with the given type to the given value.
	 * The option type is used to filter and sanitize the given option value.
	 * 
	 * @param string $key The option key
	 * @param string $type The option value type
	 * @param mixed $value The option value
	 * 
	 * @return void 
	 */
	private function _setOption($key, $type, $value) {
		$this->_loadSettingsIfNeeded();
		$this->_data[$key] = Abp01_InputFiltering::filterValue($value, $type);
	}

	private function _getDefaultTileLayer() {
		return Abp01_Settings_PredefinedTileLayer::getDefaultTileLayer()
			->getTileLayerObject();
	}

	private function _getDefaultTopTeaserText() {
		return __('For the pragmatic sort, there is also a trip summary at the bottom of this page. Click here to consult it', 'abp01-trip-summary');
	}

	private function _getDefaultBottomTeaserText() {
		return __('It looks like you skipped the story. You should check it out. Click here to go back to beginning', 'abp01-trip-summary');
	}

	public function asPlainObject() {
		$data = new stdClass();
		$this->_loadSettingsIfNeeded();

		$data->showTeaser = $this->getShowTeaser();
		$data->topTeaserText = $this->getTopTeaserText();
		$data->bottomTeaserText = $this->getBottomTeaserText();
		$data->tileLayer = $this->getTileLayers()[0];
		$data->showFullScreen = $this->getShowFullScreen();
		$data->showMagnifyingGlass = $this->getShowMagnifyingGlass();
		$data->unitSystem = $this->getUnitSystem();
		$data->measurementUnits = $this->getMeasurementUnits();
		$data->showMapScale = $this->getShowMapScale();
		$data->allowTrackDownload = $this->getAllowTrackDownload();
		$data->trackLineColour = $this->getTrackLineColour();
		$data->trackLineWeight = $this->getTrackLineWeight();
		$data->showMinMaxAltitude = $this->getShowMinMaxAltitude();
		$data->showAltitudeProfile = $this->getShowAltitudeProfile();
		$data->mapHeight = $this->getMapHeight();
		$data->initialViewerTab = $this->getInitialViewerTab();
		$data->viewerItemLayout = $this->getViewerItemLayout();
		$data->viewerItemValueDisplayCount = $this->getViewerItemValueDisplayCount();
		$data->jsonLdEnabled = $this->getEnableJsonLdFrontenData();

		//TODO: these should not be part of the plain settings object
		$data->allowedUnitSystems = self::getAllowedUnitSystems();
		$data->allowedViewerTabs = self::getAllowedViewerTabs();
		$data->allowedItemLayouts = self::getAllowedItemLayouts();
		$data->allowedPredefinedTileLayers = self::getAllowedPredefinedTileLayers();

		return $data;
	}

	public function getOptionsLimits() {
		$data = new stdClass();
		$data->minAllowedMapHeight = $this->getMinimumAllowedMapHeight();
		$data->minAllowedTrackLineWeight = $this->getMinimumAllowedTrackLineWeight();
		$data->minViewerItemValueDisplayCount = $this->getMinimumViewerItemValueDisplayCount();
		return $data;
	}

	public function getMeasurementUnits() {
		return Abp01_UnitSystem::create($this->getUnitSystem())
			->asPlainObject();
	}

	public function getShowTeaser() {
		return $this->_getOption(self::OPT_TEASER_SHOW, 'boolean', true);
	}

	public function setShowTeaser($showTeaser) {
		$this->_setOption(self::OPT_TEASER_SHOW, 'boolean', $showTeaser);
		return $this;
	}

	public function getTopTeaserText() {
		return $this->_getOption(self::OPT_TEASER_TOP, 'string', $this->_getDefaultTopTeaserText());
	}

	public function setTopTeaserText($topTeaserText) {
		$this->_setOption(self::OPT_TEASER_TOP, 'string', $topTeaserText);
		return $this;
	}

	public function getBottomTeaserText() {
		return $this->_getOption(self::OPT_TEASER_BOTTOM, 'string', $this->_getDefaultBottomTeaserText());
	}

	public function setBottomTeaserText($bottomTeaserText) {
		$this->_setOption(self::OPT_TEASER_BOTTOM, 'string', $bottomTeaserText);
		return $this;
	}

	public function getInitialViewerTab() {
		return $this->_getOption(self::OPT_INITIAL_VIEWER_TAB, 'string', Abp01_Viewer::TAB_INFO);
	}

	public function setInitialViewerTab($viewerTab) {
		if (!Abp01_Viewer::isTabSupported($viewerTab)) {
			$viewerTab = $this->getInitialViewerTab();
		}

		$this->_setOption(self::OPT_INITIAL_VIEWER_TAB, 'string', $viewerTab);
		return $this;
	}

	public function getViewerItemLayout() {
		return $this->_getOption(self::OPT_VIEWER_ITEM_LAYOUT, 'string', Abp01_Viewer::ITEM_LAYOUT_HORIZONTAL);
	}

	public function setViewerItemLayout($viewerItemLayout) {
		if (!Abp01_Viewer::isItemLayoutSupported($viewerItemLayout)) {
			$viewerItemLayout = $this->getViewerItemLayout();
		}

		$this->_setOption(self::OPT_VIEWER_ITEM_LAYOUT, 'string', $viewerItemLayout);
		return $this;
	}

	public function getViewerItemValueDisplayCount() {
		return $this->_getOption(self::OPT_VIEWER_ITEM_VALUE_DISPLAY_COUNT, 'integer', 3);
	}

	public function setViewerItemValueDisplayCount($displayCount) {
		$displayCount = max($displayCount, $this->getMinimumViewerItemValueDisplayCount());
		$this->_setOption(self::OPT_VIEWER_ITEM_VALUE_DISPLAY_COUNT, 'integer', $displayCount);
		return $this;
	}

	public function getMinimumViewerItemValueDisplayCount() {
		return self::MINIMUM_VIEWER_ITEM_DISPLAY_COUNT;
	}

	public function getTileLayers() {
		$tileLayers = $this->_getOption(self::OPT_MAP_TILE_LAYER_URLS, 'array', array($this->_getDefaultTileLayer()));
		foreach ($tileLayers as $index => $tileLayer) {
			$tileLayers[$index] = $this->_checkAndNormalizeTileLayer($tileLayer);
		}
		return $tileLayers;
	}

	public function getMainTileLayer() {
		$tileLayers = $this->getTileLayers();
		return $tileLayers[0];
	}

	private function _checkAndNormalizeTileLayer($tileLayer) {
		if (!is_object($tileLayer) || empty($tileLayer->url)) {
			return false;
		}
		if (!isset($tileLayer->attributionTxt)) {
			$tileLayer->attributionTxt = null;
		}
		if (!isset($tileLayer->attributionUrl)) {
			$tileLayer->attributionUrl = null;
		}
		if (!isset($tileLayer->apiKey)) {
			$tileLayer->apiKey = null;
		}
		return $tileLayer;
	}

	public function setTileLayers($tileLayers) {
		$saveLayers = array();
		if (!is_array($tileLayers)) {
			$tileLayers = array($tileLayers);
		}
		foreach ($tileLayers as $layer) {
			$layer = $this->_checkAndNormalizeTileLayer($layer);
			if ($layer) {
				$saveLayers[] = $layer;
			}
		}
		if (!count($saveLayers)) {
			throw new InvalidArgumentException('tileLayers');
		}
		$this->_setOption(self::OPT_MAP_TILE_LAYER_URLS, 'string', $saveLayers);
		return $this;
	}

	public function getAllowTrackDownload() {
		return $this->_getOption(self::OPT_ALLOW_TRACK_DOWNLOAD, 'boolean', true);
	}

	public function setAllowTrackDownload($allowTrackDownload) {
		$this->_setOption(self::OPT_ALLOW_TRACK_DOWNLOAD, 'boolean', $allowTrackDownload);
		return $this;
	}

	public function getShowFullScreen() {
		return $this->_getOption(self::OPT_MAP_FEATURES_FULL_SCREEN_SHOW, 'boolean', true);
	}

	public function setShowFullScreen($showFullScreen) {
		$this->_setOption(self::OPT_MAP_FEATURES_FULL_SCREEN_SHOW, 'boolean', $showFullScreen);
		return $this;
	}

	public function getShowMapScale() {
		return $this->_getOption(self::OPT_MAP_FEATURES_SCALE_SHOW, 'boolean', true);
	}

	public function setShowMapScale($showMapScale) {
		$this->_setOption(self::OPT_MAP_FEATURES_SCALE_SHOW, 'boolean', $showMapScale);
		return $this;
	}

	public function getShowMagnifyingGlass() {
		return $this->_getOption(self::OPT_MAP_FEATURES_MAGNIFYING_GLASS_SHOW, 'boolean', true);
	}

	public function setShowMagnifyingGlass($showMagnifyingGlass) {
		$this->_setOption(self::OPT_MAP_FEATURES_MAGNIFYING_GLASS_SHOW, 'boolean', $showMagnifyingGlass);
		return $this;
	}

	public function getUnitSystem() {
		return $this->_getOption(self::OPT_UNIT_SYSTEM, 'string', Abp01_UnitSystem::METRIC);
	}

	public function setUnitSystem($unitSystem) {
		$allowedUnitSystems = array_keys(self::getAllowedUnitSystems());
		if (!in_array($unitSystem, $allowedUnitSystems)) {
			$unitSystem = $this->getUnitSystem();
		}
		$this->_setOption(self::OPT_UNIT_SYSTEM, 'string', $unitSystem);
		return $this;
	}

	public function getTrackLineColour() {
		return $this->_getOption(self::OPT_TRACK_LINE_COLOUR, 'string', '#0033ff');
	}

	public function setTrackLineColour($colourHex) {
		$this->_setOption(self::OPT_TRACK_LINE_COLOUR, 'string', $colourHex);
		return $this;
	}

	public function getTrackLineWeight() {
		return $this->_getOption(self::OPT_TRACK_LINE_WEIGHT, 'integer', 3);
	}

	public function setTrackLineWeight($weight) {
		$weight = max(intval($weight), $this->getMinimumAllowedTrackLineWeight());
		$this->_setOption(self::OPT_TRACK_LINE_WEIGHT, 'integer', $weight);
		return $this;
	}

	public function getMinimumAllowedTrackLineWeight() {
		return self::MINIMUM_ALLOWED_TRACK_LINE_WEIGHT;
	}

	public function getShowMinMaxAltitude() {
		return $this->_getOption(self::OPT_MAP_FEATURES_MINMAX_ALTITUDE, 'boolean', true);
	}

	public function setShowMinMaxAltitude($showMinMaxAltitude) {
		$this->_setOption(self::OPT_MAP_FEATURES_MINMAX_ALTITUDE, 'boolean', $showMinMaxAltitude);
		return $this;
	}

	public function getShowAltitudeProfile() {
		return $this->_getOption(self::OPT_MAP_FEATURES_ALTITUDE_PROFILE, 'boolean', true);
	}

	public function setShowAltitudeProfile($showAltitudeProfile) {
		$this->_setOption(self::OPT_MAP_FEATURES_ALTITUDE_PROFILE, 'boolean', $showAltitudeProfile);
		return $this;
	}

	public function getMapHeight() {
		return $this->_getOption(self::OPT_MAP_HEIGHT, 'integer', $this->getMinimumAllowedMapHeight());
	}

	public function setMapHeight($mapHeight) {
		$mapHeight = max(intval($mapHeight), $this->getMinimumAllowedMapHeight());
		$this->_setOption(self::OPT_MAP_HEIGHT, 'integer', $mapHeight);
		return $this;
	}

	public function setEnableJsonLdFrontendData($enable) {
		$this->_setOption(self::OPT_JSONLD_ENABLED, 'boolean', $enable === true);
	}

	public function getEnableJsonLdFrontenData() {
		return $this->_getOption(self::OPT_JSONLD_ENABLED, 'boolean', false);
	}

	public function getMinimumAllowedMapHeight() {
		return self::MINIMUM_ALLOWED_MAP_HEIGHT;
	}

	public function syncTopTeaserTextWithCurrentLocale() {
		$this->setTopTeaserText($this->_getDefaultTopTeaserText());
	}

	public function syncBottomTeaserTextWithCurrentLocale() {
		$this->setBottomTeaserText($this->_getDefaultBottomTeaserText());
	}

	public static function getAllowedUnitSystems() {
		return Abp01_UnitSystem::getAvailableUnitSystems();
	}

	public static function getAllowedViewerTabs() {
		return Abp01_Viewer::getAvailableTabs();
	}

	public static function getAllowedItemLayouts() {
		return Abp01_Viewer::getAvailableItemLayouts();
	}

	public static function getAllowedPredefinedTileLayers() {
		$allowedPredefinedTileLayersInfos = array();
		$predefinedTileLayers = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayers();
		
		foreach ($predefinedTileLayers as $id => $predefinedLayer) {
			$allowedPredefinedTileLayersInfos[$id] = $predefinedLayer->asPlainObject();
		}

		return $allowedPredefinedTileLayersInfos;
	} 

	public function saveSettings() {
		$this->_loadSettingsIfNeeded();
		update_option(self::OPT_SETTINGS_KEY, $this->_data);
		return true;
	}

	public function purgeAllSettings() {
		$this->clearSettingsCache();
		return delete_option(self::OPT_SETTINGS_KEY);
	}

	public function clearSettingsCache() {
		$this->_data = null;
	}
}
