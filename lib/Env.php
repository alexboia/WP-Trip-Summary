<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
 * A class that serves as an accessor for the current WordPress environment
 */
class Abp01_Env {
	/**
	 * The singleton instance
     * 
	 * @var Abp01_Env
	 */
    private static $_instance = null;

	/**
	 * Current language
     * 
	 * @var string
	 */
    private $_lang;

	/**
	 * Whether we're running in debug mode or not
     * 
	 * @var boolean
	 */
    private $_isDebugMode;

	/**
	 * The current database host server
     * 
	 * @var string
	 */
    private $_dbHost;

	/**
	 * Database credentials - username
     * 
	 * @var string
	 */
    private $_dbUserName;

	/**
	 * Database credentials - password
     * 
	 * @var string
	 */
    private $_dbPassword;

	/**
	 * Database table prefix
     * 
	 * @var string
	 */
    private $_dbTablePrefix;

	/**
	 * The name of the database to which we're connecting
     * 
	 * @var string
	 */
    private $_dbName;

    /**
     * The database collation
     * 
     * @var string
     */
    private $_dbCollate;

    /**
     * The database charset
     * 
     * @var string
     */
    private $_dbCharset;

	/**
	 * The name of the table that holds the route details. Prefix included.
     * 
	 * @var string
	 */
    private $_routeDetailsTableName;

	/**
	 * The name of the table that holds the serialized route tracks. Prefix included.
     * 
	 * @var string
	 */
    private $_routeTrackTableName;

	/**
	 * The name of the table that holds the look-up data items. Prefix included.
     * 
	 * @var string
	 */
    private $_lookupTableName;

	/**
	 * The name of the table that holds the look-up data items translations. Prefix included.
     * 
	 * @var string
	 */
    private $_lookupLangTableName;

	/**
	 * The name of the table that holds the relationships between routes (posts) and look-up data items. Prefix included
     * 
	 * @var string
	 */
	private $_routeDetailsLookupTableName;

	/**
	 * The current database object instance
     * 
	 * @var MysqliDb
	 */
    private $_db = null;

    /**
     * The current information schema database object instance
     * 
     * @var MysqliDb
     */
    private $_metaDb = null;

    /**
     * Whether or not the mysqli driver has been initialized
     * 
     * @var boolean
     */
    private $_driverInitialized = false;

	/**
	 * The current WordPress version
     * 
	 * @var string
	 */
    private $_wpVersion;

	/**
	 * The current PHP version
     * 
	 * @var string
	 */
    private $_phpVersion;

    /**
     * The path to the root plug-in directory
     * 
     * @var string
     */
    private $_pluginRootDir;

	/**
	 * The path to the data directory. 
     * 
	 * @var string 
	 */
    private $_dataDir;

    /**
     * The path to the root storage directory of the plug-in. This directory hosts all the other storage sub-directories.
     * 
     * @var string
     */
    private $_rootStorageDir;

    /**
     * The path to the tracks storage directory. This is where the original track data files are stored, as uploaded by the users.
     * 
     * @var string
     */
    private $_tracksStorageDir;

    /**
     * The path to the cache storage direcory. This is where all the cached track files are stored.
     * 
     * @var string
     */
    private $_cacheStorageDir;

    /**
     * The path to the views directory
     * 
     * @var string
     */
    private $_viewsDir;

	/**
	 * The current plug-in version
     * 
	 * @var string
	 */
    private $_version = ABP01_VERSION;

	/**
	 * Gets or creates the singleton instance
	 * @return Abp01_Env The singleton instance
	 */
    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->_initFromWpConfig();
        $this->_initTableNames();
        $this->_initVersions();
        $this->_initDataDir();
        $this->_initDirs();
    }

    public function __clone() {
        throw new Exception('Cloning a singleton of type ' . __CLASS__ . ' is not allowed');
    }

    private function _initFromWpConfig() {
        $this->_lang = get_locale();
        $this->_isDebugMode = defined('WP_DEBUG') && WP_DEBUG == true;

        $this->_dbHost = defined('DB_HOST') 
            ? DB_HOST
            : null;
        $this->_dbUserName = defined('DB_USER') 
            ? DB_USER
            : null;
        $this->_dbPassword = defined('DB_PASSWORD') 
            ? DB_PASSWORD
            : null;
        $this->_dbName = defined('DB_NAME') 
            ? DB_NAME
            : null;
        $this->_dbCharset = defined('DB_CHARSET') 
            ? DB_CHARSET 
            : null;
        $this->_dbCollate = defined('DB_COLLATE') 
            ? DB_COLLATE 
            : null;

        $this->_dbTablePrefix = isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix']
            : null;
    }

    private function _initVersions() {
        $this->_phpVersion = PHP_VERSION;
        $this->_wpVersion = get_bloginfo('version', 'raw');
    }

    private function _initDataDir() {
        $pluginRoot = dirname(dirname(__FILE__));
		
    }

    private function _initDirs() {
        if (defined('ABP01_PLUGIN_ROOT')) {
            $this->_pluginRootDir = ABP01_PLUGIN_ROOT;
        } else {
            $this->_pluginRootDir = dirname(dirname(__FILE__));
        }

        $this->_dataDir = wp_normalize_path(sprintf('%s/data', 
            $this->_pluginRootDir));

        $this->_viewsDir = wp_normalize_path(sprintf('%s/views', 
            $this->_pluginRootDir));

        $uploadRootDirInfo = wp_upload_dir();
        $this->_rootStorageDir = wp_normalize_path(sprintf('%s/wp-trip-summary', 
            $uploadRootDirInfo['basedir']));
        $this->_tracksStorageDir = wp_normalize_path(sprintf('%s/tracks', 
            $this->_rootStorageDir));
        $this->_cacheStorageDir = wp_normalize_path(sprintf('%s/cache', 
            $this->_rootStorageDir));
    }

    private function _initTableNames() {
        $this->_routeTrackTableName = $this->_dbTablePrefix
            . 'abp01_techbox_route_track';
        $this->_routeDetailsTableName = $this->_dbTablePrefix
            . 'abp01_techbox_route_details';
        $this->_lookupTableName = $this->_dbTablePrefix
            . 'abp01_techbox_lookup';
        $this->_lookupLangTableName = $this->_dbTablePrefix
            . 'abp01_techbox_lookup_lang';
		$this->_routeDetailsLookupTableName = $this->_dbTablePrefix
			. 'abp01_techbox_route_details_lookup';
    }

    private function _getCurrentAdminPageSlug() {
        return isset($_GET['page']) 
            ? strtolower($_GET['page']) 
            : null;
    }

	public function overrideDataDir($dataDir) {
		if (empty($dataDir) || !is_dir($dataDir)) {
			throw new InvalidArgumentException();
		}
		$this->_dataDir = $dataDir;
    }
    
    public function getFrontendTemplateLocations() {
        $dirs = new stdClass();
        $dirs->default = $this->_viewsDir;
        $dirs->theme = $this->getCurrentThemeDir() . '/abp01-viewer';
        $dirs->themeUrl = $this->getCurrentThemeUrl() . '/abp01-viewer';
        return $dirs;
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

    public function getDbCollate() {
        return $this->_dbCollate;
    }

    public function getDbCharset() {
        return $this->_dbCharset;
    }

    public function getDb() {
        if ($this->_db == null) {
            $this->_db = new MysqliDb($this->_dbHost,
                $this->_dbUserName,
                $this->_dbPassword,
                $this->_dbName);

            $this->_initDriverIfNeeded();
        }

        return $this->_db;
    }

    public function getMetaDb() {
        if ($this->_metaDb == null) {
            $this->_metaDb = new MysqliDb($this->_dbHost,
                $this->_dbUserName,
                $this->_dbPassword,
                'information_schema');

            $this->_initDriverIfNeeded();
        }

        return $this->_metaDb;
    }

    private function _initDriverIfNeeded() {
        if (!$this->_driverInitialized) {
            $driver = new mysqli_driver();
            $driver->report_mode =  MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
            $this->_driverInitialized = true;
        }
    }
	
	public function getCurrentThemeId() {
		return get_stylesheet();
	}

    public function getCurrentThemeDir() {
        return  wp_get_theme()->get_stylesheet_directory();
    }

    public function getCurrentThemeUrl() {
        return  wp_get_theme()->get_stylesheet_directory_uri();
    }

    /**
     * Retrieves the current post ID, searching 
     * for the following data sources, in this order:
     *  - the $GLOBALS['post'] - if this is set, then it returns $GLOBALS['post']->ID;
     *  - $_GET['post'] - if this is set, then it returns intval($_GET['post'])
     *  - $_GET[$fallbackGetVarName] - if this is set, then it returns intval($_GET[$fallbackGetVarName])
     * 
     * @param string $fallbackGetVarName The key to search for in the $_GET superglobal array if the previous sources did not yield any post ID
     * @return int|null The post ID or null if not found
     */
    public function getCurrentPostId($fallbackGetVarName) {
        $post = isset($GLOBALS['post']) 
            ? $GLOBALS['post'] 
            : null;

        if ($post && isset($post->ID)) {
            return intval($post->ID);
        } else if (isset($_GET['post'])) {
            return intval($_GET['post']);
        } else if (isset($_GET[$fallbackGetVarName])) {
            return intval($_GET[$fallbackGetVarName]);
        }
        return null;
    }

    public function isAdminPage($slug) {
        return $this->getCurrentAdminPage() == 'admin.php' 
            && $this->_getCurrentAdminPageSlug() == strtolower($slug);
    }

    public function isSavingWpOptions() {
        return $this->getCurrentAdminPage() == 'options.php' && $this->isHttpPost();
    }

    public function isListingWpPosts() {
        $requiredPostTypes = func_get_args();

        $postType = isset($_GET['post_type']) 
            ? $_GET['post_type'] 
            : 'post';

        return $this->getCurrentAdminPage() == 'edit.php' 
            && (empty($requiredPostTypes) || in_array($postType, $requiredPostTypes));
    }

    public function isEditingWpPost() {      
        $isEditingPost = in_array($this->getCurrentAdminPage(), array(
            'post-new.php', 
            'post.php'
        ));

        if ($isEditingPost) {
            $args = func_num_args();
            if ($args > 0) {
                $post = isset($GLOBALS['post']) 
                    ? $GLOBALS['post'] 
                    : null;

                return $post != null 
                    && !empty($post->post_type) 
                    && in_array($post->post_type, func_get_args());
            }
        } else {
            return $isEditingPost;
        }
    }

    public function getCurrentAdminPage() {
        return isset($GLOBALS['pagenow']) 
            ? strtolower($GLOBALS['pagenow']) 
            : null;
    }

    public function getAjaxBaseUrl() {
        return get_admin_url(null, 'admin-ajax.php', 'admin');
    }

    public function getPluginAssetUrl($relativeAssetUrl) {
        return plugins_url($relativeAssetUrl, ABP01_PLUGIN_MAIN);
    }

    public function isPluginActive($plugin) {
        if (!function_exists('is_plugin_active')) {
            return in_array($plugin, (array)get_option('active_plugins', array()));
        } else {
            return is_plugin_active($plugin);
        }
    }

    public function getHttpMethod() {
        return isset($_SERVER['REQUEST_METHOD']) 
            ? strtolower($_SERVER['REQUEST_METHOD']) 
            : null;
    }

    public function isHttpGet() {
        return $this->getHttpMethod() === 'get';
    }

    public function isHttpPost() {
        return $this->getHttpMethod() === 'post';
    }

    public function getWpPostsTableName() {
        return isset($GLOBALS['wpdb']) 
            ? $GLOBALS['wpdb']->posts 
            : $this->_dbTablePrefix . 'posts';
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

	public function getRouteDetailsLookupTableName() {
		return $this->_routeDetailsLookupTableName;
	}

    public function getDataDir() {
        return $this->_dataDir;
    }

    public function getViewsDir() {
        return $this->_viewsDir;
    }

    public function getViewFilePath($viewFile) {
        return wp_normalize_path(sprintf('%s/%s', 
            $this->_viewsDir, 
            $viewFile));
    }

    public function getViewHelpersFilePath($helperFile) {
        return wp_normalize_path(sprintf('%s/helpers/%s', 
            $this->_viewsDir, 
            $helperFile));
    }

    public function getRootStorageDir() {
        return  $this->_rootStorageDir;
    }

    public function getTracksStorageDir() {
        return $this->_tracksStorageDir;
    }

    public function getCacheStorageDir() {
        return $this->_cacheStorageDir;
    }

    public function getPhpVersion() {
        return $this->_phpVersion;
    }

    public function getRequiredPhpVersion() {
        return '5.6.2';
    }

    public function getWpVersion() {
        return $this->_wpVersion;
    }

    public function getRequiredWpVersion() {
        return '5.0';
    }

    public function getVersion() {
        return $this->_version;
    }
}