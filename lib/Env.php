<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

declare(strict_types=1);

namespace WpTripSummary {
	if (!defined('ABP01_LOADED')) {
		exit;
	}

	use mysqli_driver;
	use MysqliDb;
    use stdClass;

	/**
	 * A class that serves as an accessor for the current WordPress environment
	 */
	class Env {
		private static Env|null $_instance = null;

		/**
		 * Current language
		 */
		private string $_lang;

		/**
		 * Whether we're running in debug mode or not
		 */
		private bool $_isDebugMode;

		/**
		 * The current database host server
		 */
		private string $_dbHost;

		/**
		 * Database credentials - username
		 */
		private string $_dbUserName;

		/**
		 * Database credentials - password
		 */
		private string $_dbPassword;

		/**
		 * Database table prefix
		 */
		private string $_dbTablePrefix;

		/**
		 * The name of the database to which we're connecting
		 */
		private string $_dbName;

		/**
		 * The database collation
		 */
		private string $_dbCollate;

		/**
		 * The database charset
		 */
		private string $_dbCharset;

		/**
		 * The name of the table that holds the route details. Prefix included.
		 */
		private string $_routeDetailsTableName;

		/**
		 * The name of the table that holds the serialized route tracks. Prefix included.
		 */
		private string $_routeTrackTableName;

		/**
		 * The name of the table that holds the look-up data items. Prefix included.
		 */
		private string $_lookupTableName;

		/**
		 * The name of the table that holds the look-up data items translations. Prefix included.
		 */
		private string $_lookupLangTableName;

		/**
		 * The name of the table that holds the relationships between routes (posts) and look-up data items. Prefix included
		 */
		private string $_routeDetailsLookupTableName;

		private string $_routeLogTableName;

		/**
		 * The name of the wordpress users table
		 */
		private string $_wpUsersTableName;

		/**
		 * The current database object instance
		 */
		private MysqliDb|null $_db = null;

		/**
		 * The current information schema database object instance
		 */
		private MysqliDb|null $_metaDb = null;

		/**
		 * Whether or not the mysqli driver has been initialized
		 */
		private bool $_driverInitialized = false;

		/**
		 * The current WordPress version
		 */
		private string $_wpVersion;

		/**
		 * The current PHP version
		 */
		private string $_phpVersion;

		/**
		 * The path to the root plug-in directory
		 */
		private string $_pluginRootDir;

		/**
		 * The path to the data directory. 

		 */
		private string $_dataDir;

		/**
		 * The path to the root storage directory of the plug-in. This directory hosts all the other storage sub-directories.
		 */
		private string $_rootStorageDir;

		/**
		 * The path to the tracks storage directory. This is where the original track data files are stored, as uploaded by the users.
		 */
		private string $_tracksStorageDir;

		/**
		 * The path to the cache storage direcory. This is where all the cached track files are stored.
		 */
		private string $_cacheStorageDir;

		/**
		 * The path to the logs storage directory.
		 */
		private string $_logStorageDir;

		/**
		 * The path to the views directory
		 */
		private string $_viewsDir;

		/**
		 * The current plug-in version
		 */
		private string $_version = ABP01_VERSION;

		private string $_mysqliDbClass = MysqliDb::class;

		public static function getInstance(): Env {
			if (self::$_instance == null) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function configureMysqliDbClass(string $className): void {
			if ($this->_db != null || $this->_metaDb != null) {
				throw new \WpTripSummary\Exception('Cannot change database wrapper class: an instance has already been created!');
			}

			if (!is_a($className, MysqliDb::class, true)) {
				throw new \WpTripSummary\Exception('Cannot change database wrapper class: Class is not an instance of <' . MysqliDb::class . '>!');
			}

			$this->_mysqliDbClass = $className;
		}

		private function __construct() {
			$this->_initFromWpConfig();
			$this->_initTableNames();
			$this->_initVersions();
			$this->_initDirs();
		}

		public function __clone(): never {
			throw new \Exception('Cloning a singleton of type ' . __CLASS__ . ' is not allowed');
		}

		private function _initFromWpConfig(): void {
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

		private function _initVersions(): void {
			$this->_phpVersion = PHP_VERSION;
			$this->_wpVersion = get_bloginfo('version', 'raw');
		}

		private function _initDirs(): void {
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
			$this->_logStorageDir = wp_normalize_path(sprintf('%s/logs', 
				$this->_rootStorageDir));

			if (defined('ABP01_LOGS_DIR')) {
				$customLogsDir = constant('ABP01_LOGS_DIR');
				if (!empty($customLogsDir) && is_dir($customLogsDir)) {
					$this->_logStorageDir = $customLogsDir;
				}
			}
		}

		private function _initTableNames(): void {
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
			$this->_routeLogTableName = $this->_dbTablePrefix
				. 'abp01_techbox_route_log';
			$this->_wpUsersTableName = $this->_dbTablePrefix
				. 'users';
		}

		public function overrideDataDir(string $dataDir): void {
			if (empty($dataDir) || !is_dir($dataDir)) {
				throw new \InvalidArgumentException();
			}
			$this->_dataDir = $dataDir;
		}
		
		public function getFrontendTemplateLocations(): stdClass {
			$dirs = new \stdClass();
			$dirs->default = $this->_viewsDir;
			$dirs->theme = $this->getCurrentThemeDir() . '/abp01-viewer';
			$dirs->themeUrl = $this->getCurrentThemeUrl() . '/abp01-viewer';
			return $dirs;
		}

		public function getLang(): string {
			return $this->_lang;
		}

		public function isDebugMode(): bool {
			return $this->_isDebugMode;
		}

		public function getDbHost(): string {
			return $this->_dbHost;
		}

		public function getDbUserName(): string {
			return $this->_dbUserName;
		}

		public function getDbPassword(): string {
			return $this->_dbPassword;
		}

		public function getDbTablePrefix(): string {
			return $this->_dbTablePrefix;
		}

		public function getDbName(): string {
			return $this->_dbName;
		}

		public function getDbCollate(): string {
			return $this->_dbCollate;
		}

		public function getDbCharset(): string {
			return $this->_dbCharset;
		}

		/**
		 * @return \MysqliDb
		 */
		public function getDb(): MysqliDb {
			if ($this->_db == null) {
				$className = $this->_mysqliDbClass;
				$this->_db = new $className($this->_dbHost,
					$this->_dbUserName,
					$this->_dbPassword,
					$this->_dbName);

				$this->_initDriverIfNeeded();
			}

			return $this->_db;
		}

		private function _initDriverIfNeeded(): void {
			if (!$this->_driverInitialized) {
				$driver = new mysqli_driver();
				$driver->report_mode =  MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
				$this->_driverInitialized = true;
			}
		}

		/**
		 * @return \MysqliDb
		 */
		public function getMetaDb(): MysqliDb {
			if ($this->_metaDb == null) {
				$className = $this->_mysqliDbClass;
				$this->_metaDb = new $className($this->_dbHost,
					$this->_dbUserName,
					$this->_dbPassword,
					'information_schema');

				$this->_initDriverIfNeeded();
			}

			return $this->_metaDb;
		}
		
		public function getCurrentThemeId(): string {
			return get_stylesheet();
		}

		public function getCurrentThemeDir(): string {
			return  wp_get_theme()->get_stylesheet_directory();
		}

		public function getCurrentThemeUrl(): string {
			return wp_get_theme()->get_stylesheet_directory_uri();
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
		public function getCurrentPostId($fallbackGetVarName): ?int {
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

		public function isAdminPage($slug): bool {
			return $this->getCurrentAdminPage() == 'admin.php' 
				&& $this->_getCurrentAdminPageSlug() == strtolower($slug);
		}

		public function getAdminPageUrl($slug): ?string {
			return admin_url('admin.php?page=' . $slug);
		}

		private function _getCurrentAdminPageSlug(): ?string {
			return isset($_GET['page']) 
				? strtolower($_GET['page']) 
				: null;
		}

		public function isSavingWpOptions(): bool {
			$adminPage = $this->getCurrentAdminPage();
			return ($adminPage == 'options.php' || $adminPage == 'options-general.php') 
				&& $this->isHttpPost();
		}

		public function isListingWpPosts(): bool {
			$requiredPostTypes = $this->_parseArgsAsPostTypesArray(func_get_args());

			$postType = isset($_GET['post_type']) 
				? $_GET['post_type'] 
				: 'post';

			return $this->getCurrentAdminPage() == 'edit.php' 
				&& (empty($requiredPostTypes) || in_array($postType, $requiredPostTypes));
		}

		private function _parseArgsAsPostTypesArray(array $args): array {
			$requiredPostTypes = $args;
			if (!empty($requiredPostTypes) 
				&& isset($requiredPostTypes[0]) 
				&& is_array($requiredPostTypes[0])) {
				$requiredPostTypes = $requiredPostTypes[0];
			}
			return $requiredPostTypes;
		}

		public function isEditingWpPost(): bool {      
			$isEditingPost = in_array($this->getCurrentAdminPage(), array(
				'post-new.php', 
				'post.php'
			));

			if ($isEditingPost) {
				$requiredPostTypes = $this->_parseArgsAsPostTypesArray(func_get_args());
				if (!empty($requiredPostTypes)) {
					$post = isset($GLOBALS['post']) 
						? $GLOBALS['post'] 
						: null;

					$isEditingPost = ($post != null 
						&& !empty($post->post_type) 
						&& in_array($post->post_type, $requiredPostTypes));
				}
			}

			return $isEditingPost;
		}

		public function getCurrentAdminPage(): ?string {
			return isset($GLOBALS['pagenow']) 
				? strtolower($GLOBALS['pagenow']) 
				: null;
		}

		public function getAjaxBaseUrl(): string {
			return get_admin_url(null, 'admin-ajax.php', 'admin');
		}

		public function getPluginAssetUrl(string $relativeAssetUrl): string {
			return plugins_url($relativeAssetUrl, ABP01_PLUGIN_MAIN);
		}

		public function isPluginActive(string $plugin): bool {
			if (!function_exists('is_plugin_active')) {
				return in_array($plugin, (array)get_option('active_plugins', array()));
			} else {
				return is_plugin_active($plugin);
			}
		}

		public function getHttpMethod(): ?string {
			return isset($_SERVER['REQUEST_METHOD']) 
				? strtolower($_SERVER['REQUEST_METHOD']) 
				: null;
		}

		public function isHttpGet(): bool {
			return $this->getHttpMethod() === 'get';
		}

		public function isHttpPost(): bool {
			return $this->getHttpMethod() === 'post';
		}

		public function getWpPostsTableName(): string {
			return isset($GLOBALS['wpdb']) 
				? (string)$GLOBALS['wpdb']->posts 
				: $this->_dbTablePrefix . 'posts';
		}

		public function getRouteTrackTableName(): string {
			return $this->_routeTrackTableName;
		}

		public function getRouteDetailsTableName(): string {
			return $this->_routeDetailsTableName;
		}

		public function getLookupLangTableName(): string {
			return $this->_lookupLangTableName;
		}

		public function getLookupTableName(): string {
			return $this->_lookupTableName;
		}

		public function getRouteDetailsLookupTableName(): string {
			return $this->_routeDetailsLookupTableName;
		}

		public function getRouteLogTableName(): string {
			return $this->_routeLogTableName;
		}

		public function getWpUsersTableName(): string {
			return $this->_wpUsersTableName;
		}

		public function getDataDir(): string {
			return $this->_dataDir;
		}

		public function getViewsDir(): string {
			return $this->_viewsDir;
		}

		public function getViewFilePath(string $viewFile): string {
			return wp_normalize_path(sprintf('%s/%s', 
				$this->_viewsDir, 
				$viewFile));
		}

		public function getViewHelpersFilePath(string $helperFile): string {
			return wp_normalize_path(sprintf('%s/helpers/%s', 
				$this->_viewsDir, 
				$helperFile));
		}

		public function getRootStorageDir(): string {
			return  $this->_rootStorageDir;
		}

		public function getTracksStorageDir(): string {
			return $this->_tracksStorageDir;
		}

		public function getLogStorageDir(): string {
			return $this->_logStorageDir;
		}

		public function getCacheStorageDir(): string {
			return $this->_cacheStorageDir;
		}

		public function getPhpVersion(): string {
			return $this->_phpVersion;
		}

		public function getRequiredPhpVersion(): string {
			return '8.0.0';
		}

		public function getWpVersion(): string {
			return $this->_wpVersion;
		}

		public function getRequiredWpVersion(): string {
			return '6.0.0';
		}

		public function getVersion(): string {
			return $this->_version;
		}
	}
}