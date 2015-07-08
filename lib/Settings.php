<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Settings {
    const OPT_TEASER_SHOW = 'showTeaser';

    const OPT_TEASER_TOP = 'teaserTopTxt';

    const OPT_TEASER_BOTTOM = 'teaserBottomTxt';

    const OPT_MAP_TILE_LAYER_URLS = 'mapTileLayerUrls';

    const OPT_MAP_FEATURES_MAGNIFYING_GLASS_SHOW = 'mapMagnifyingGlassShow';

    const OPT_MAP_FEATURES_FULL_SCREEN_SHOW = 'mapFullScreenShow';

    const OPT_UNIT_SYSTEM = 'unitSystem';

    const OPT_SETTINGS_KEY = 'abp01.settings';

    private static $_instance = null;

    private $_data = null;

    private function __construct() {
        return;
    }

    public function __clone() {
        throw new Exception('Cloning a singleton of type ' . __CLASS__ . ' is not allowed');
    }

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function _loadSettingsIfNeeded() {
        if ($this->_data === null) {
            $this->_data = get_option(self::OPT_SETTINGS_KEY, array());
            if (!is_array($this->_data)) {
                $this->_data = array();
            }
        }
    }

    private function _getOption($key, $type, $default) {
        $this->_loadSettingsIfNeeded();
        $optionValue = isset($this->_data[$key]) ? $this->_data[$key] : $default;
        if (!settype($optionValue, $type)) {
            $optionValue = $default;
        }
        $this->_data[$key] = $optionValue;
        return $optionValue;
    }

    private function _setOption($key, $type, $value) {
        $this->_loadSettingsIfNeeded();
        if (settype($value, $type)) {
            $this->_data[$key] = $value;
            return true;
        } else {
            return false;
        }
    }

    public function getShowTeaser() {
        return $this->_getOption(self::OPT_TEASER_SHOW, 'boolean', true);
    }

    public function setShowTeaser($showTeaser) {
        $this->_setOption(self::OPT_TEASER_SHOW, 'boolean', $showTeaser);
        return $this;
    }

    public function getTopTeaserText() {
        return $this->_getOption(self::OPT_TEASER_TOP, 'string',
            __('For the pragmatic sort, there is also a trip summary at the bottom of this page. Click here to consult it', 'abp01-trip-summary'));
    }

    public function setTopTeaserText($topTeaserText) {
        $this->_setOption(self::OPT_TEASER_TOP, 'string', $topTeaserText);
        return $this;
    }

    public function getBottomTeaserText() {
        return $this->_getOption(self::OPT_TEASER_BOTTOM, 'string',
            __('It looks like you skipped the story. You should check it out. Click here to go back to beginning', 'abp01-trip-summary'));
    }

    public function setBottomTeaserText($bottomTeaserText) {
        $this->_setOption(self::OPT_TEASER_BOTTOM, 'string', $bottomTeaserText);
        return $this;
    }

    public function getTileLayerUrls() {
        return $this->_getOption(self::OPT_MAP_TILE_LAYER_URLS, 'array', array('http://{s}.tile.osm.org/{z}/{x}/{y}.png'));
    }

    public function setTileLayerUrls(array $tileLayerUrls) {
        $this->_setOption(self::OPT_MAP_TILE_LAYER_URLS, 'string', $tileLayerUrls);
        return $this;
    }

    public function getShowFullScreen() {
        return $this->_getOption(self::OPT_MAP_FEATURES_FULL_SCREEN_SHOW, 'boolean', true);
    }

    public function setShowFullScreen($showFullScreen) {
        $this->_setOption(self::OPT_MAP_FEATURES_FULL_SCREEN_SHOW, 'boolean', $showFullScreen);
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
        return $this->_getOption(self::OPT_UNIT_SYSTEM, 'string', 'metric');
    }

    public function setUnitSystem($unitSystem) {
        $allowedUnitSystems = $this->getAllowedUnitSystems();
        if (!in_array($unitSystem, $allowedUnitSystems)) {
            $unitSystem = 'metric';
        }
        $this->_setOption(self::OPT_UNIT_SYSTEM, 'string', $unitSystem);
        return $this;
    }

    public function saveSettings() {
        $this->_loadSettingsIfNeeded();
        return update_option(self::OPT_SETTINGS_KEY, $this->_data);
    }

    public function purgeAllSettings() {
        return delete_option(self::OPT_SETTINGS_KEY);
    }

    public function getAllowedUnitSystems() {
        return array('metric', 'imperial');
    }
}