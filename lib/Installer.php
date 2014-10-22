<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Installer {
    const INCOMPATIBLE_PHP_VERSION = 1;

    const INCOMPATIBLE_WP_VERSION = 2;

    const SUPPORT_LIBXML_NOT_FOUND = 3;

    const SUPPORT_MYSQL_SPATIAL_NOT_FOUND = 4;

    const SUPPORT_MYSQLI_NOT_FOUND = 5;

    private $_env;

    private $_lookupData = array();

    private $_lastError = null;

    public function __construct() {
        $this->_env = Abp01_Env::getInstance();

        $this->_lookupData[Abp01_Lookup::DIFFICULTY_LEVEL] = array(
            'Usor',
            'Mediu',
            'Dificil',
            'Tortura medievala'
        );

        $this->_lookupData[Abp01_Lookup::PATH_SURFACE_TYPE] = array(
            'Asfalt',
            'Placi de beton',
            'Pamant',
            'Iarba',
            'Macadam',
            'Piatra neasezata'
        );

        $this->_lookupData[Abp01_Lookup::BIKE_TYPE] = array(
            'MTB',
            'Cursiera',
            'Trekking',
            'Bicicleta de oras'
        );

        $this->_lookupData[Abp01_Lookup::RAILROAD_OPERATOR] = array(
            'CFR',
            'Regiotrans',
            'TFC - Transferoviar Calatori',
            'Regional'
        );

        $this->_lookupData[Abp01_Lookup::RAILROAD_LINE_STATUS] = array(
            'In exploatare',
            'Inchisa',
            'Desfiintata',
            'In reabilitare'
        );

        $this->_lookupData[Abp01_Lookup::RAILROAD_LINE_TYPE] = array(
            'Linie simpla',
            'Linie dubla'
        );

        $this->_lookupData[Abp01_Lookup::RECOMMEND_SEASONS] = array(
            'Primavara',
            'Vara',
            'Toamna',
            'Iarna'
        );

        $this->_lookupData[Abp01_Lookup::RAILROAD_ELECTRIFICATION] = array(
            'Electrificata',
            'Neelectrificata',
            'Partial electrificata'
        );
    }

    public function canBeInstalled() {
        $this->_reset();
        try {
            if (!$this->_isCompatPhpVersion()) {
                return self::INCOMPATIBLE_PHP_VERSION;
            }
            if (!$this->_isCompatWpVersion()) {
                return self::INCOMPATIBLE_WP_VERSION;
            }
            if (!$this->_hasMysqli()) {
                return self::SUPPORT_MYSQLI_NOT_FOUND;
            }
            if (!$this->_hasLibxml()) {
                return self::SUPPORT_LIBXML_NOT_FOUND;
            }
            if (!$this->_hasMysqlSpatialSupport() ||
                !$this->_hasRequiredMysqlSpatialFunctions()) {
                return self::SUPPORT_MYSQL_SPATIAL_NOT_FOUND;
            }
        } catch (Exception $e) {
            $this->_lastError = $e;
        }
        return empty($this->_lastError) ? 0 : false;
    }

    public function activate() {
        $this->_reset();
        try {
            if (!$this->_installSchema()) {
                return false;
            }
            if (!$this->_installData()) {
                $this->_uninstallSchema();
                return false;
            } else {
                return $this->_createCapabilities();
            }
        } catch (Exception $e) {
            $this->_lastError = $e;
        }
        return false;
    }

    public function deactivate() {
        $this->_reset();
        try {
            return $this->_removeCapabilities();
        } catch (Exception $e) {
            $this->_lastError = $e;
        }
        return false;
    }

    public function uninstall() {
        $this->_reset();
        return $this->deactivate() &&
            $this->_uninstallSchema();
    }

    public function getRequiredPhpVersion() {
        return $this->_env->getRequiredPhpVersion();
    }

    public function getRequiredWpVersion() {
        return $this->_env->getRequiredWpVersion();
    }

    public function getLastError() {
        return $this->_lastError;
    }

    private function _isCompatPhpVersion() {
        $current = $this->_env->getPhpVersion();
        $required = $this->_env->getRequiredPhpVersion();
        return version_compare($current, $required, '>=');
    }

    private function _isCompatWpVersion() {
        $current = $this->_env->getWpVersion();
        $required = $this->_env->getRequiredWpVersion();
        return version_compare($current, $required, '>=');
    }

    private function _hasLibxml() {
        return function_exists('simplexml_load_string') &&
            function_exists('simplexml_load_file');
    }

    private function _hasMysqli() {
        return extension_loaded('mysqli') &&
            class_exists('mysqli_driver') &&
            class_exists('mysqli');
    }

    private function _hasMysqlSpatialSupport() {
        $result = false;
        $db = $this->_env->getDb();

        if (!$db) {
            return false;
        }

        $haveGeometry = $db->rawQuery("SHOW VARIABLES WHERE Variable_name = 'have_geometry'");
        if (!empty($haveGeometry) && is_array($haveGeometry)) {
            $haveGeometry = $haveGeometry[0];
            $result = !empty($haveGeometry['Value']) &&
                strcasecmp($haveGeometry['Value'], 'YES') === 0;
        }

        return $result;
    }

    private function _hasRequiredMysqlSpatialFunctions() {
        $result = false;
        $db = $this->_env->getDb();
        $expected = 'POLYGON((1 2,3 2,3 4,1 4,1 2))';

        if (!$db) {
            return false;
        }

        $spatialTest = $db->rawQuery('SELECT AsText(Envelope(LineString(
            GeometryFromText(AsText(Point(1, 2)), 3857),
            GeometryFromText(AsText(Point(3, 4)), 3857)
        ))) AS SPATIAL_TEST');

        if (!empty($spatialTest) && is_array($spatialTest)) {
            $result = strcasecmp($spatialTest[0]['SPATIAL_TEST'], $expected) === 0;
        }

        return $result;
    }

    private function _createCapabilities() {
        Abp01_Auth::getInstance()->installCapabilities();
        return true;
    }

    private function _removeCapabilities() {
        Abp01_Auth::getInstance()->removeCapabilities();
        return true;
    }

    private function _installData() {
        $db = $this->_env->getDb();
        $table = $this->_getLookupTableName();
        $ok = true;

        if (!$db) {
            return false;
        }

        //make sure table is empty
        $stats = $db->getOne($table, 'COUNT(*) AS cnt');
        if ($stats && is_array($stats) && $stats['cnt'] > 0) {
            return true;
        }

        //save lookup data
        foreach ($this->_lookupData as $category => $data) {
            foreach ($data as $label) {
                $ok = $ok && $db->insert($table, array(
                    'lookup_category' => $category,
                    'lookup_label' => $label
                )) !== false;
                if (!$ok) {
                    break 2;
                }
            }
        }

        return $ok;
    }

    private function _installSchema() {
        $ok = true;
        $tables = array(
            $this->_getLookupTableDefinition(),
            $this->_getRouteDetailsTableDefinition(),
            $this->_getRouteTrackTableDefinition()
        );

        foreach ($tables as $table) {
            $ok = $ok && $this->_createTable($table);
        }

        if (!$ok) {
            $this->_uninstallSchema();
        }

        return $ok;
    }

    private function _uninstallSchema() {
        return $this->_uninstallRouteDetailsTable() !== false &&
            $this->_uninstallRouteTrackTable() !== false &&
            $this->_uninstallLookupTable() !== false;
    }

    private function _createTable($tableDef) {
        $db = $this->_env->getDb();
        if (!$db) {
            return false;
        }

        $charset = $this->_getDefaultCharset();
        $collate = $this->_getCollate();

        if (!empty($charset)) {
            $charset = "DEFAULT CHARACTER SET = '" . $charset . "'";
            $tableDef .= ' ' . $charset . ' ';
        }
        if (!empty($collate)) {
            $collate = "COLLATE = '" . $collate . "'";
            $tableDef .= ' ' . $collate . ' ';
        }

        $tableDef .= ' ';
        $tableDef .= 'ENGINE=MyISAM';

        $db->rawQuery($tableDef, null, false);
        $lastError = trim($db->getLastError());

        return empty($lastError);
    }

    private function _getRouteTrackTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteTrackTableName() . "` (
            `post_ID` BIGINT(20) UNSIGNED NOT NULL,
            `route_track_file` LONGTEXT NOT NULL,
            `route_min_coord` POINT NOT NULL,
            `route_max_coord` POINT NOT NULL,
            `route_bbox` POLYGON NOT NULL,
            `route_min_alt` FLOAT NULL DEFAULT '0',
            `route_max_alt` FLOAT NULL DEFAULT '0',
            `route_track_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `route_track_modified_at` TIMESTAMP NULL DEFAULT NULL,
            `route_track_modified_by` BIGINT(20) NULL DEFAULT NULL,
                PRIMARY KEY (`post_ID`),
                SPATIAL INDEX `idx_route_track_bbox` (`route_bbox`)
        )";
    }

    private function _getRouteDetailsTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteDetailsTableName() . "` (
            `post_ID` BIGINT(10) UNSIGNED NOT NULL,
            `route_type` VARCHAR(150) NOT NULL,
            `route_data_serialized` LONGTEXT NOT NULL,
            `route_data_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `route_data_last_modified_at` TIMESTAMP NULL DEFAULT NULL,
            `route_data_last_modified_by` BIGINT(20) NULL DEFAULT NULL,
                PRIMARY KEY (`post_ID`)
        )";
    }

    private function _getLookupTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getLookupTableName() . "` (
            `ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `lookup_category` VARCHAR(150) NOT NULL,
            `lookup_label` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`ID`)
        )";
    }

    private function _uninstallRouteTrackTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getRouteTrackTableName() . '`', null, false) : false;
    }

    private function _uninstallRouteDetailsTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getRouteDetailsTableName() . '`', null, false) : false;
    }

    private function _uninstallLookupTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getLookupTableName() . '`', null, false) : false;
    }

    private function _reset() {
        $this->_lastError = null;
    }

    private function _getDefaultCharset() {
        return defined('DB_CHARSET') ? DB_CHARSET : null;
    }

    private function _getCollate() {
        return defined('DB_COLLATE') ? DB_COLLATE : null;
    }

    private function _getRouteTrackTableName() {
        return $this->_env->getRouteTrackTableName();
    }

    private function _getRouteDetailsTableName() {
        return $this->_env->getRouteDetailsTableName();
    }

    private function _getLookupTableName() {
        return $this->_env->getLookupTableName();
    }
}