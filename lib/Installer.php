<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Installer {
    private $_env;

    private $_lookupData = array();

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

    public function activate() {
        if (!$this->_installSchema()) {
            return false;
        }
        if (!$this->_installData()) {
            $this->_uninstallSchema();
            return false;
        } else {
            $this->_createCapabilities();
            return true;
        }
    }

    public function deactivate() {
        $this->_removeCapabilities();
        return true;
    }

    public function uninstall() {
        return $this->deactivate() &&
            $this->_uninstallSchema();
    }

    private function _createCapabilities() {
        Abp01_Auth::getInstance()->installCapabilities();
    }

    private function _removeCapabilities() {
        Abp01_Auth::getInstance()->removeCapabilities();
    }

    private function _removeDirRecursive($dir) {
        $items = @scandir($dir);
        if (!$items || !is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_file($path)) {
                @unlink($path);
            } else if (is_dir($path)) {
                $this->_removeDirRecursive($path);
            }
        }

        @rmdir($dir);
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
        return "CREATE TABLE `" . $this->_getRouteTrackTableName() . "` (
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