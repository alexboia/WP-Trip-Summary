<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class Abp01_PluginModules_PluginModuleHost {
    /**
     * @var Abp01_PluginModules_PluginModule[]
     */
    private $_pluginModules;

    /**
     * @var Abp01_PluginModules_PluginModuleActivator
     */
    private $_pluginModuleActivator;

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
     * @var Abp01_ChangeLogDataSource
     */
    private $_changeLogDataSource;

    /**
     * @var Abp01_Viewer
     */
    private $_viewer;

    /**
     * @var Abp01_UrlHelper
     */
    private $_urlHelper;

    public function __construct(array $pluginModulesClasses) {
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
            Abp01_PluginModules_PluginModuleHost::class => function() {
                return $this;
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
            Abp01_ChangeLogDataSource::class => function() {
                return $this->getChangeLogDataSource();
            },
            Abp01_UrlHelper::class => function() {
                return $this->getUrlHelper();
            },
            Abp01_Viewer::class => function() {
                return $this->getViewer();
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
            Abp01_Help::class => function() {
                return $this->getHelp();
            },
            Abp01_Auth::class => function() {
                return $this->getAuth();
            }
        );
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
            $this->_urlHelper = new Abp01_UrlHelper($this->getEnv(), $this->getTrackDownloadNonceProvider());
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

    public function getChangeLogDataSource() {
        if ($this->_changeLogDataSource === null) {
            $this->_changeLogDataSource = new Abp01_ChangeLogDataSource_Cached(
                new Abp01_ChangeLogDataSource_ReadMe($this->_determineReadmeTxtFilePath()), 
                $this->getEnv()
            );
        }
        return $this->_changeLogDataSource;
    }

    private function _determineReadmeTxtFilePath() {
		return ABP01_PLUGIN_ROOT . '/readme.txt';
	}

    public function getViewerDataSourceCache() {
        if ($this->_viewerDataSourceCache === null) {
            $this->_viewerDataSourceCache = new Abp01_Viewer_DataSource_Cache_WpTransients();
        }
        return $this->_viewerDataSourceCache;
    }

    public function getLookupForCurrentLang() {
        return new Abp01_Lookup();
    }

    public function getRouteManager() {
        return abp01_get_route_manager();
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