<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Lookup {
    const DIFFICULTY_LEVEL = 'difficultyLevel';

    const PATH_SURFACE_TYPE = 'pathSurfaceType';

    const BIKE_TYPE = 'bikeType';

    const RAILROAD_LINE_TYPE = 'railroadLineType';

    const RAILROAD_OPERATOR = 'railroadOperator';

    const RAILROAD_LINE_STATUS = 'railroadLineStatus';

    const RECOMMEND_SEASONS = 'recommendSeasons';

    const RAILROAD_ELECTRIFICATION = 'railroadElectrificationStatus';

    private static $_instance = null;

    private $_cache = null;

    private $_env = null;

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->_env = Abp01_Env::getInstance();
    }

    private function _loadDataIfNeeded() {
        $db = $this->_env->getDb();
        $table = $this->_env->getLookupTableName();
        $rows = $db->rawQuery('SELECT * FROM `' . $table . '` ORDER BY `lookup_label` ASC', null, false);

        $this->_cache = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $category = $row['lookup_category'];
                if (!isset($this->_cache[$category])) {
                    $this->_cache[$category] = array();
                }
                $this->_cache[$category][$row['ID']] = $row['lookup_label'];
            }
        }
    }

    private function _getLookupOptions($type) {
        $this->_loadDataIfNeeded();
        $options = array();
        if (isset($this->_cache[$type])) {
            foreach ($this->_cache[$type] as $id => $label) {
                $options[] = $this->_createOption($id, $label, $type);
            }
        }
        return $options;
    }

    private function _createOption($id, $label, $type) {
        $option = new stdClass();
        $option->id = $id;
        $option->label = $label;
        $option->type = $type;
        return $option;
    }

    public function getDifficultyLevelOptions() {
        return $this->_getLookupOptions(self::DIFFICULTY_LEVEL);
    }

    public function getPathSurfaceTypeOptions() {
        return $this->_getLookupOptions(self::PATH_SURFACE_TYPE);
    }

    public function getBikeTypeOptions() {
        return $this->_getLookupOptions(self::BIKE_TYPE);
    }

    public function getRecommendedSeasonsOptions() {
        return $this->_getLookupOptions(self::RECOMMEND_SEASONS);
    }

    public function getRailroadLineTypeOptions() {
        return $this->_getLookupOptions(self::RAILROAD_LINE_TYPE);
    }

    public function getRailroadOperatorOptions() {
        return $this->_getLookupOptions(self::RAILROAD_OPERATOR);
    }

    public function getRailroadLineStatusOptions() {
        return $this->_getLookupOptions(self::RAILROAD_LINE_STATUS);
    }

    public function getRailroadElectrificationOptions() {
        return $this->_getLookupOptions(self::RAILROAD_ELECTRIFICATION);
    }

    public function lookup($type, $id) {
        $this->_loadDataIfNeeded();
        if (isset($this->_cache[$type][$id])) {
            $result = $this->_createOption(intval($id), $this->_cache[$type][$id], $type);
        } else {
            $result = null;
        }
        return $result;
    }

    public function lookupDifficultyLevel($id) {
        return $this->lookup(self::DIFFICULTY_LEVEL, $id);
    }

    public function lookupPathSurfaceType($id) {
        return $this->lookup(self::PATH_SURFACE_TYPE, $id);
    }

    public function lookupBikeType($id) {
        return $this->lookup(self::BIKE_TYPE, $id);
    }

    public function lookupRailroadLineType($id) {
        return $this->lookup(self::RAILROAD_LINE_TYPE, $id);
    }

    public function lookupRailroadOperator($id) {
        return $this->lookup(self::RAILROAD_OPERATOR, $id);
    }

    public function lookupRailroadLineStatus($id) {
        return $this->lookup(self::RAILROAD_LINE_STATUS, $id);
    }

    public function lookupRailroadElectrification($id) {
        return $this->lookup(self::RAILROAD_ELECTRIFICATION, $id);
    }

    public function lookupRecommendedSeason($id) {
        return $this->lookup(self::RECOMMEND_SEASONS, $id);
    }
}