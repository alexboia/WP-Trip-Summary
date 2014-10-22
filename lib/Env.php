<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Env {
    private static $_instance = null;

    private $_lang;

    private $_isDebugMode;

    private $_dbHost;

    private $_dbUserName;

    private $_dbPassword;

    private $_dbTablePrefix;

    private $_dbName;

    private $_routeDetailsTableName;

    private $_routeTrackTableName;

    private $_lookupTableName;

    private $_lookupLangTableName;

    private $_db = null;

    private $_wpVersion;

    private $_phpVersion;

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->_initFromWpConfig();
        $this->_initVersions();
    }

    public  function __clone() {
        throw new Exception('Cloning a singleton of type ' . __CLASS__ . ' is not allowed');
    }

    private function _initFromWpConfig() {
        $this->_lang = 'en_US';
        $this->_isDebugMode = defined('WP_DEBUG') && WP_DEBUG == true;

        $this->_dbHost = defined('DB_HOST') ? DB_HOST
            : null;
        $this->_dbUserName = defined('DB_USER') ? DB_USER
            : null;
        $this->_dbPassword = defined('DB_PASSWORD') ? DB_PASSWORD
            : null;
        $this->_dbName = defined('DB_NAME') ? DB_NAME
            : null;

        $this->_dbTablePrefix = isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix']
            : null;

        $this->_routeTrackTableName = $this->_dbTablePrefix
            . 'abp01_techbox_route_track';
        $this->_routeDetailsTableName = $this->_dbTablePrefix
            . 'abp01_techbox_route_details';
        $this->_lookupTableName = $this->_dbTablePrefix
            . 'abp01_techbox_lookup';
        $this->_lookupLangTableName = $this->_dbTablePrefix
            . 'abp01_techbox_lookup_lang';
    }

    private function _initVersions() {
        $this->_phpVersion = PHP_VERSION;
        $this->_wpVersion = get_bloginfo('version', 'raw');
    }

    public function getLang() {
        return $this->_lang;
    }

    public function isDebugMode() {
        return $this->_isDebugMode;
    }

    public function getDbHost() {
        return $this->_dbHost;
    }

    public function getDbUserName() {
        return $this->_dbUserName;
    }

    public function getDbPassword() {
        return $this->_dbPassword;
    }

    public function getDbTablePrefix() {
        return $this->_dbTablePrefix;
    }

    public function getDbName() {
        return $this->_dbName;
    }

    public function getDb() {
        if ($this->_db == null) {
            $this->_db = new MysqliDb($this->getDbHost(),
                $this->getDbUserName(),
                $this->getDbPassword(),
                $this->getDbName());

            $driver = new mysqli_driver();
            $driver->report_mode =  MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        }
        return $this->_db;
    }

    public function getRouteTrackTableName() {
        return $this->_routeTrackTableName;
    }

    public function getRouteDetailsTableName() {
        return $this->_routeDetailsTableName;
    }

    public function getLookupLangTableName() {
        return $this->_lookupLangTableName;
    }

    public function getLookupTableName() {
        return $this->_lookupTableName;
    }

    public function getPhpVersion() {
        return $this->_phpVersion;
    }

    public function getRequiredPhpVersion() {
        return '5.2.4';
    }

    public function getWpVersion() {
        return $this->_wpVersion;
    }

    public function getRequiredWpVersion() {
        return '4.0';
    }
}