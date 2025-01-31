<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class RequiresSupportedDependenciesSamplePluginModule extends Abp01_PluginModules_PluginModule {
    private $_settings; 

    private $_routeManager; 

    private $_view;
    
    private $_help;
    
    public function __construct(Abp01_Settings $settings, 
        Abp01_Route_Manager $routeManager, 
        Abp01_View $view, 
        Abp01_Help $help,
        Abp01_Env $env,
        Abp01_Auth $auth) {

        parent::__construct($env, $auth);
            
        $this->_settings = $settings;
        $this->_routeManager = $routeManager;
        $this->_view = $view;
        $this->_help = $help;

        SamplePluginModuleCreationState::reportModuleConstructed(__CLASS__, func_get_args());
    }

    public function load() {
        SamplePluginModuleCallState::reportModuleLoadCalled(__CLASS__);
    }

    public function hasSettings() {
        return !empty($this->_settings);
    }

    public function hasEnv() {
        return !empty($this->_env);
    }

    public function hasRouteManager() {
        return !empty($this->_routeManager);
    }

    public function hasView() {
        return !empty($this->_view);
    }

    public function hasHelp() {
        return !empty($this->_help);
    }
}