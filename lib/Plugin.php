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

class Abp01_Plugin {
	/**
	 * @var Abp01_Route_Track_Processor
	 */
	private $_routeTrackProcessor;

	/**
	 * @var Abp01_Transfer_Uploader_FileValidatorProvider
	 */
	private $_fileValidatorProvider;

	/**
	 * @var Abp01_Route_Track_DocumentParser_Factory
	 */
	private $_documentParserFactory;
	
	/**
	 * @var Abp01_NonceProvider_DownloadTrackData
	 */
	private $_downloadTrackDataNonceProvider;

	/**
	 * @var Abp01_NonceProvider_ReadTrackData
	 */
	private $_readTrackDataNonceProvider;

	/**
	 * @var Abp01_Viewer_DataSource_Cache
	 */
	private $_viewerDataSourceCache;

	/**
	 * @var Abp01_Viewer_DataSource
	 */
	private $_viewerDataSource;

	/**
	 * @var Abp01_Rest_DataSource
	 */
	private $_restDataSource;

	/**
	 * @var Abp01_ChangeLogDataSource
	 */
	private $_changeLogDataSource;

	/**
	 * @var Abp01_AuditLog_Provider
	 */
	private $_auditLogProvider;

	/**
	 * @var Abp01_Viewer
	 */
	private $_viewer;

	/**
	 * @var Abp01_UrlHelper
	 */
	private $_urlHelper;

	/**
	 * @var Abp01_PluginModules_PluginModuleHost
	 */
	private $_pluginModuleHost;

	/**
	 * @var Abp01_PluginMenu
	 */
	private $_pluginMenu;

	/**
	 * @var Abp01_MaintenanceTool_Registry
	 */
	private $_maintenanceToolRegistry;


	public function __construct() {
		return;
	}

	public function run() {
		register_activation_hook(ABP01_PLUGIN_MAIN, 
			array($this, 'onActivatePlugin'));
		register_deactivation_hook(ABP01_PLUGIN_MAIN, 
			array($this, 'onDeactivatePlugin'));
		register_uninstall_hook(ABP01_PLUGIN_MAIN, 
			array(__CLASS__, 'onUninstallPlugin'));

		add_action('plugins_loaded', 
			array($this, 'onPluginsLoaded'));
	}

	public function onActivatePlugin() {
		if (!self::_currentUserCanActivatePlugins()) {
			write_log('Attempted to activate plug-in without appropriate access permissions.');
			return;
		}

		$installer = self::_getInstaller();
		$testInstallationErrorCode = $installer->checkRequirements();
		if (!$this->_wasInstallationTestSuccessful($testInstallationErrorCode)) {
			$message = $this->_getInstallationErrorMessage($testInstallationErrorCode);
			$this->_abortPluginInstallation($message);
		} else {
			if (!$installer->activate()) {
				$message = __('Could not activate plug-in: activation failure.', 'abp01-trip-summary');
				$this->_displayActivationErrrorMessage($message);
			}
		}
	}

	private static function _currentUserCanActivatePlugins() {
		return current_user_can('activate_plugins');
	}

	private static function _getInstaller() {
		return abp01_get_installer();
	}

	private function _wasInstallationTestSuccessful($testInstallationErrorCode) {
		return $testInstallationErrorCode === Abp01_Installer::ALL_REQUIREMENTS_MET;
	}

	private function _getInstallationErrorMessage($installationErrorCode) {
		$this->_loadTextDomain();
		$errors = $this->_getInstallationErrorTranslations();
		return isset($errors[$installationErrorCode]) 
			? $errors[$installationErrorCode] 
			: __('Could not activate plug-in: requirements not met.', 'abp01-trip-summary');
	}

	private function _getInstallationErrorTranslations() {
		$env = $this->getEnv();
		return array(
			Abp01_Installer::INCOMPATIBLE_PHP_VERSION 
				=> sprintf(esc_html__('Minimum required PHP version is %s', 'abp01-trip-summary'), $env->getRequiredPhpVersion()), 
			Abp01_Installer::INCOMPATIBLE_WP_VERSION 
				=> sprintf(esc_html__('Minimum required WP version is %s', 'abp01-trip-summary'), $env->getRequiredWpVersion()), 
			Abp01_Installer::SUPPORT_LIBXML_NOT_FOUND 
				=> esc_html__('LIBXML support was not found on your system', 'abp01-trip-summary'), 
			Abp01_Installer::SUPPORT_MYSQLI_NOT_FOUND 
				=> esc_html__('Mysqli extension was not found on your system or is not fully compatible', 'abp01-trip-summary'), 
			Abp01_Installer::SUPPORT_MYSQL_SPATIAL_NOT_FOUND 
				=> esc_html__('MySQL spatial support was not found on your system', 'abp01-trip-summary'),
			Abp01_Installer::COULD_NOT_DETECT_INSTALLATION_CAPABILITIES 
				=> esc_html__('We could not check whether your system fully supports this plug-in.', 'abp01-trip-summary')
		);
	}

	private function _displayActivationErrrorMessage($message) {
		$installer = self::_getInstaller();
		$displayMessage = abp01_append_error($message, 
			$installer->getLastError());
			
		$displayTitle = __('Activation error', 
			'abp01-trip-summary');
			
		wp_die($displayMessage, $displayTitle);
	}

	private function _abortPluginInstallation($message) {
		deactivate_plugins(plugin_basename(ABP01_PLUGIN_MAIN));
		$this->_displayActivationErrrorMessage($message);
	}

	public function onDeactivatePlugin() {
		if (!self::_currentUserCanActivatePlugins()) {
			write_log('Attempted to uninstall plug-in without appropriate access permissions.');
			return;
		}

		$installer = self::_getInstaller();
		if (!$installer->deactivate()) {
			wp_die(abp01_append_error('Could not deactivate plug-in', $installer->getLastError()), 
				'Deactivation error');
		}
	}

	public static function onUninstallPlugin() {
		if (!self::_currentUserCanActivatePlugins()) {
			write_log('Attempted to uninstall plug-in without appropriate access permissions.');
			return;
		}

		$installer = self::_getInstaller();
		if (!$installer->uninstall()) {
			wp_die(abp01_append_error('Could not uninstall plug-in', $installer->getLastError()), 
				'Uninstall error');
		}
	}

	public function onPluginsLoaded() {
		$this->_configureScriptIncludes();
		$this->_loadTextDomain();
		$this->_initView();
		$this->_increaseExecutionLimits();
		$this->_updateIfNeeded();
		$this->_setupPluginModules();
		$this->_setupPluginMenu();
		$this->_loadPluginModules();
	}

	private function _configureScriptIncludes() {
		Abp01_Includes::configure(ABP01_PLUGIN_MAIN, true);
	}

	private function _loadTextDomain() {
		load_plugin_textdomain(
			'abp01-trip-summary', 
			false, 
			plugin_basename(ABP01_LANG_DIR)
		);
	}

	private function _initView() {
		$this->getView()->initView();
	}

	private function _increaseExecutionLimits() {
		abp01_increase_limits(ABP01_MAX_EXECUTION_TIME_MINUTES);
	}

	private function _updateIfNeeded() {
		self::_getInstaller()->updateIfNeeded();
	}

	private function _setupPluginModules() {
		$this->_pluginModuleHost = new Abp01_PluginModules_PluginModuleHost($this, array(
			Abp01_PluginModules_RestApiEnhancementsPluginModule::class,
			Abp01_PluginModules_PluginListingPageCustomizationPluginModule::class,
			Abp01_PluginModules_SettingsPluginModule::class,
			Abp01_PluginModules_LookupDataManagementPluginModule::class,
			Abp01_PluginModules_MaintenancePluginModule::class,
			Abp01_PluginModules_JsonLdFrontendDataPluginModule::class,
			Abp01_PluginModules_PostListingCustomizationPluginModule::class,
			Abp01_PluginModules_DownloadTrackDataPluginModule::class,
			Abp01_PluginModules_GetTrackDataPluginModule::class,
			Abp01_PluginModules_AdminTripSummaryEditorPluginModule::class,
			Abp01_PluginModules_FrontendViewerPluginModule::class,
			Abp01_PluginModules_TeaserTextsSyncPluginModule::class,
			Abp01_PluginModules_AdminTripSummaryAuditLogPluginModule::class,
			Abp01_PluginModules_RouteLogPluginModule::class,
			Abp01_PluginModules_SystemLogsManagementPluginModule::class,
			Abp01_PluginModules_HelpPluginModule::class,
			Abp01_PluginModules_AboutPagePluginModule::class
		));
	}

	private function _setupPluginMenu() {
		$menuItems = $this->_pluginModuleHost->getMenuItems();
		$this->_pluginMenu = new Abp01_PluginMenu($menuItems);
		$this->_pluginMenu->register();
	}

	private function _loadPluginModules() {
		$this->_pluginModuleHost->load();
	}

	public function getRouteTrackProcessor() {
		if ($this->_routeTrackProcessor == null) {
			$this->_routeTrackProcessor = new Abp01_Route_Track_Processor_Default($this->getDocumentParserFactory(), $this->getEnv());
		}
		return $this->_routeTrackProcessor;
	}

	public function getFileValidatorProvider() {
		if ($this->_fileValidatorProvider === null) {
			$this->_fileValidatorProvider = new Abp01_Transfer_Uploader_FileValidatorProvider();
		}
		return $this->_fileValidatorProvider;
	}

	public function getDocumentParserFactory() {
		if ($this->_documentParserFactory === null) {
			$this->_documentParserFactory = new Abp01_Route_Track_DocumentParser_Factory();
		}
		return $this->_documentParserFactory;
	}

	public function getTrackDownloadNonceProvider() {
		if ($this->_downloadTrackDataNonceProvider === null) {
			$this->_downloadTrackDataNonceProvider = new Abp01_NonceProvider_DownloadTrackData();
		}
		return $this->_downloadTrackDataNonceProvider;
	}

	public function getReadTrackDataNonceProvider() {
		if ($this->_readTrackDataNonceProvider === null) {
			$this->_readTrackDataNonceProvider = new Abp01_NonceProvider_ReadTrackData();
		}
		return $this->_readTrackDataNonceProvider;
	}

	public function getUrlHelper() {
		if ($this->_urlHelper === null) {
			$this->_urlHelper = new Abp01_UrlHelper($this->getTrackDownloadNonceProvider(), $this->getEnv());
		}
		return $this->_urlHelper;
	}

	public function getViewer() {
		if ($this->_viewer === null) {
			$this->_viewer = new Abp01_Viewer($this->getView());
		}
		return $this->_viewer;
	}

	public function getViewerDataSource() {
		if ($this->_viewerDataSource === null) {
			$this->_viewerDataSource = new Abp01_Viewer_DataSource_Default($this->getRouteManager(), 
				$this->getLookupForCurrentLang(), 
				$this->getViewerDataSourceCache());
		}
		return $this->_viewerDataSource;
	}

	public function getRestDataSource() {
		if ($this->_restDataSource === null) {
			$this->_restDataSource = new Abp01_Rest_DataSource_Default($this->getRouteManager(), 
				$this->getLookupForCurrentLang());
		}
		return $this->_restDataSource;
	}

	public function getChangeLogDataSource() {
		if ($this->_changeLogDataSource === null) {
			$this->_changeLogDataSource = new Abp01_ChangeLogDataSource_Cached(
				new Abp01_ChangeLogDataSource_ReadMe($this->_determineReadmeTxtFilePath()), 
				$this->getEnv()
			);
		}
		return $this->_changeLogDataSource;
	}

	public function getAuditLogProvider() {
		if ($this->_auditLogProvider === null) {
			$this->_auditLogProvider = new Abp01_AuditLog_Provider_Default($this->getEnv());
		}
		return $this->_auditLogProvider;
	}

	private function _determineReadmeTxtFilePath() {
		$regularFile = ABP01_PLUGIN_ROOT . '/readme.txt';
		if (is_readable($regularFile)) {
			return $regularFile;
		}

		$devFile = ABP01_PLUGIN_ROOT . '/README.txt';
		if (is_readable($devFile)) {
			return $devFile;
		}

		return null;
	}

	public function getViewerDataSourceCache() {
		if ($this->_viewerDataSourceCache === null) {
			$this->_viewerDataSourceCache = new Abp01_Viewer_DataSource_Cache_WpTransients();
		}
		return $this->_viewerDataSourceCache;
	}

	public function getMaintenanceToolRegistry() {
		if ($this->_maintenanceToolRegistry === null) {
			$registry = new Abp01_MaintenanceTool_Registry();
			
			if ($this->_shouldAddClearCacheTool()) {
				$registry->registerTool(new Abp01_MaintenanceTool_ClearCache($this->getEnv()));
			}
			
			if ($this->_shouldAddClearAllDataTool()) {
				$registry->registerTool(new Abp01_MaintenanceTool_ClearAllData($this->getRouteManager(), 
					$this->getEnv()));
			}
			
			if ($this->_shouldAddDetectMissingTracksTool()) {
				$registry->registerTool(new Abp01_MaintenanceTool_DetectMissingTracks($this->getRouteManager(), 
					$this->getRouteTrackProcessor()));
			}

			$additionalTools = $this->_getAdditionalMaintenanceTools();
			foreach ($additionalTools as $t) {
				if ($t instanceof Abp01_MaintenanceTool) {
					$registry->registerTool($t);
				}
			}

			$this->_maintenanceToolRegistry = 
				$registry;
		}
		return $this->_maintenanceToolRegistry;
	}

	private function _shouldAddClearCacheTool() {
		return apply_filters('abp01_enable_clear_cache_maintenance_tool', true);
	}

	private function _shouldAddClearAllDataTool() {
		return apply_filters('abp01_enable_clear_all_data_maintenance_tool', true);
	}

	private function _shouldAddDetectMissingTracksTool() {
		return apply_filters('abp01_enable_detect_missing_tracks_maintenance_tool', true);
	}

	private function _getAdditionalMaintenanceTools() {
		return apply_filters('abp01_get_additional_maintenance_tools', array());
	}

	public function getLookupForCurrentLang() {
		return new Abp01_Lookup();
	}

	public function getLogManager() {
		return abp01_get_log_manager();
	}

	public function getRouteManager() {
		return abp01_get_route_manager();
	}

	public function getRouteLogManager() {
		return abp01_get_route_log_manager();
	}

	public function getHelp() {
		return abp01_get_help();
	}

	public function getView() {
		return abp01_get_view();
	}

	public function getSettings() {
		return abp01_get_settings();
	}

	public function getEnv() {
		return abp01_get_env();
	}

	public function getAuth() {
		return abp01_get_auth();
	}
}