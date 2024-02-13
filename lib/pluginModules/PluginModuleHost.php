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
 * @package WP-Trip-Summary
 */
class Abp01_PluginModules_PluginModuleHost {
	/**
	 * @var Abp01_Plugin
	 */
	private $_plugin;

	/**
	 * @var Abp01_PluginModules_PluginModule[]
	 */
	private $_pluginModules;

	/**
	 * @var Abp01_PluginModules_PluginModuleActivator
	 */
	private $_pluginModuleActivator;

	/**
	 * @var Abp01_Logger
	 */
	private $_logger;

	public function __construct(Abp01_Plugin $plugin, array $pluginModulesClasses) {
		$this->_plugin = $plugin;
		$this->_logger = $plugin->getLogManager()->getLogger();
		$this->_pluginModuleActivator = $this->_createPluginModuleActivator();
		$this->_pluginModules = $this->_createModules($pluginModulesClasses);
	}

	private function _createPluginModuleActivator() {
		return new Abp01_PluginModules_PluginModuleActivator($this->_getInjectableServiceFactories());
	}

	private function _createModules(array $pluginModulesClasses) {
		$pluginModules = array();

		foreach ($pluginModulesClasses as $moduleClassName) {
			$pluginModules[] = $this->_createModuleInstance($moduleClassName);
		}

		return $pluginModules;
	}

	private function _createModuleInstance($moduleClassName) {
		$this->_logger->debug('Creating plugin module instance: <' . $moduleClassName . '>...');
		return $this->_pluginModuleActivator->createModuleInstance($moduleClassName);
	}

	private function _getInjectableServiceFactories() {
		$defaultInjectables = $this->_getDefaultInjectableServiceFactories();
		return apply_filters('abp01_get_injectable_service_factories', 
			$defaultInjectables, 
			$this);
	}

	private function _getDefaultInjectableServiceFactories() {
		return array(
			Abp01_Plugin::class => function() {
				return $this->_plugin;
			},
			Abp01_PluginModules_PluginModuleHost::class => function() {
				return $this;
			},
			Abp01_Route_Track_Processor::class => function() {
				return $this->getRouteTrackProcessor();
			},
			Abp01_Route_Track_FileNameProvider::class => function() {
				return $this->getRouteTrackProcessor();
			},
			Abp01_Transfer_Uploader_FileValidatorProvider::class => function() {
				return $this->getFileValidatorProvider();
			},
			Abp01_Route_Track_DocumentParser_Factory::class => function() {
				return $this->getDocumentParserFactory();
			},
			Abp01_NonceProvider_DownloadTrackData::class => function() {
				return $this->getTrackDownloadNonceProvider();
			},
			Abp01_NonceProvider_ReadTrackData::class => function() {
				return $this->getReadTrackDataNonceProvider();
			},
			Abp01_Viewer_DataSource_Cache::class => function() {
				return $this->getViewerDataSourceCache();
			},
			Abp01_Viewer_DataSource::class => function() {
				return $this->getViewerDataSource();
			},
			Abp01_Rest_DataSource::class => function() {
				return $this->getRestDataSource();
			},
			Abp01_ChangeLogDataSource::class => function() {
				return $this->getChangeLogDataSource();
			},
			Abp01_AuditLog_Provider::class => function() {
				return $this->getAuditLogProvider();
			},
			Abp01_UrlHelper::class => function() {
				return $this->getUrlHelper();
			},
			Abp01_Viewer::class => function() {
				return $this->getViewer();
			},
			Abp01_MaintenanceTool_Registry::class => function() {
				return $this->getMaintenanceToolRegistry();
			},
			Abp01_Settings::class => function() {
				return $this->getSettings();
			},
			Abp01_Env::class => function() { 
				return $this->getEnv();
			},
			Abp01_View::class => function() {
				return $this->getView();
			},
			Abp01_Route_Manager::class => function() {
				return $this->getRouteManager();
			},
			Abp01_Route_Log_Manager::class => function() {
				return $this->getRouteLogManager();
			},
			Abp01_Help::class => function() {
				return $this->getHelp();
			},
			Abp01_Auth::class => function() {
				return $this->getAuth();
			},
			Abp01_Logger_Manager::class => function() {
				return $this->getLogManager();
			},
			Abp01_Logger::class => function() {
				return $this->getLogger();
			}
		);
	}

	public function getMenuItems() {
		$menuItemsCollector = new Abp01_PluginMenuItemCollector();

		foreach ($this->_pluginModules as $module) {
			$moduleMenuItems = $module->getMenuItems();
			if (!is_array($moduleMenuItems)) {
				$moduleMenuItems = array();
			}

			$this->_logger->debug('Found <' . count($moduleMenuItems) . '> menu items for module <' . get_class($module) . '>.');

			if (!empty($moduleMenuItems)) {
				$menuItemsCollector->collectMenuItems($moduleMenuItems);
			}
		}

		return $menuItemsCollector->getCollectedMenuItems();
	}

	public function load() {
		foreach ($this->_pluginModules as $module) {
			$module->load();
		}
	}

	public function hasModule($moduleClass) {
		$hasModule = false;

		foreach ($this->_pluginModules as $pluginModule) {
			if (is_a($pluginModule, $moduleClass)) {
				$hasModule = true;
				break;
			}
		}

		return $hasModule;
	}

	public function getRouteTrackProcessor() {
		return $this->_plugin->getRouteTrackProcessor();
	}

	public function getFileValidatorProvider() {
		return $this->_plugin->getFileValidatorProvider();
	}

	public function getDocumentParserFactory() {
		return $this->_plugin->getDocumentParserFactory();
	}

	public function getTrackDownloadNonceProvider() {
		return $this->_plugin->getTrackDownloadNonceProvider();
	}

	public function getReadTrackDataNonceProvider() {
		return $this->_plugin->getReadTrackDataNonceProvider();
	}

	public function getUrlHelper() {
		return $this->_plugin->getUrlHelper();
	}

	public function getViewer() {
		return $this->_plugin->getViewer();
	}

	public function getViewerDataSource() {
		return $this->_plugin->getViewerDataSource();
	}

	public function getRestDataSource() {
		return $this->_plugin->getRestDataSource();
	}

	public function getChangeLogDataSource() {
		return $this->_plugin->getChangeLogDataSource();
	}

	public function getViewerDataSourceCache() {
		return $this->_plugin->getViewerDataSourceCache();
	}

	public function getLookupForCurrentLang() {
		return $this->_plugin->getLookupForCurrentLang();
	}

	public function getRouteManager() {
		return $this->_plugin->getRouteManager();
	}

	public function getLogManager() {
		return $this->_plugin->getLogManager();
	}

	public function getLogger() {
		return $this->getLogManager()->getLogger();
	}

	public function getRouteLogManager() {
		return $this->_plugin->getRouteLogManager();
	}

	public function getAuditLogProvider() {
		return $this->_plugin->getAuditLogProvider();
	}

	public function getHelp() {
		return $this->_plugin->getHelp();
	}

	public function getView() {
		return $this->_plugin->getView();
	}

	public function getSettings() {
		return $this->_plugin->getSettings();
	}

	public function getMaintenanceToolRegistry() {
		return $this->_plugin->getMaintenanceToolRegistry();
	}

	public function getEnv() {
		return $this->_plugin->getEnv();
	}

	public function getAuth() {
		return $this->_plugin->getAuth();
	}
}