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

class PluginModuleHostTests extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        SamplePluginModuleCallState::reset();
        SamplePluginModuleCreationState::reset();
    }

    public function tearDown() {
        parent::tearDown();
        SamplePluginModuleCallState::reset();
        SamplePluginModuleCreationState::reset();
    }

    public function test_providesBuiltInDependencies_whenCreatingModule() {
        $pluginHost = $this->_getPluginHost();

        $this->assertTrue($pluginHost->hasModule(RequiresAllSupportedDependenciesSamplePluginModule::class));
        $this->assertTrue($pluginHost->hasModule(RequiresSupportedDependenciesSamplePluginModule::class));
        
        $this->assertTrue(SamplePluginModuleCreationState::hasModuleTypeBeenConstructedWithArgumentTypes(RequiresAllSupportedDependenciesSamplePluginModule::class, array(
            Abp01_PluginModules_PluginModuleHost::class,
            Abp01_Settings::class,
            Abp01_Route_Manager::class,
            Abp01_View::class,
            Abp01_Help::class,
            Abp01_Env::class,
            Abp01_Auth::class
        )));

        $this->assertTrue(SamplePluginModuleCreationState::hasModuleTypeBeenConstructedWithArgumentTypes(RequiresSupportedDependenciesSamplePluginModule::class, array(
            Abp01_Settings::class,
            Abp01_Route_Manager::class,
            Abp01_View::class,
            Abp01_Help::class,
            Abp01_Env::class,
            Abp01_Auth::class
        )));
    }

    private function _getPluginHost($additionalModuleClasses = array()) {
        $moduleClasses = array_merge($additionalModuleClasses, array(
            RequiresAllSupportedDependenciesSamplePluginModule::class,
            RequiresSupportedDependenciesSamplePluginModule::class
        ));

        return new Abp01_PluginModules_PluginModuleHost(new Abp01_Plugin(), $moduleClasses);
    }

    public function test_providesAdditionalDependencies_whenCreatingModule() {
        $this->_registerCustomInjectableServiceFactories();
        $pluginHost = $this->_getPluginHostWithModuleRequiringCustomDependencies();

        $this->assertTrue($pluginHost->hasModule(HasOnlyCustomAvailableDependenciesPluginModule::class));

        $this->assertTrue(SamplePluginModuleCreationState::hasModuleTypeBeenConstructedWithArgumentTypes(HasOnlyCustomAvailableDependenciesPluginModule::class, array(
            SamplePluginModuleDependency::class,
            Abp01_Env::class,
            Abp01_Auth::class
        )));
    }

    private function _registerCustomInjectableServiceFactories() {
        add_filter('abp01_get_injectable_service_factories', 
            array($this, 'createSamplePluginModuleDependency'), 
            10, 2);
    }

    private function _getPluginHostWithModuleRequiringCustomDependencies() {
        return $this->_getPluginHost(array(
            HasOnlyCustomAvailableDependenciesPluginModule::class
        ));
    }

    public function createSamplePluginModuleDependency($factories, $pluginHost) {
        $factories[SamplePluginModuleDependency::class] = function() {
            return new SamplePluginModuleDependency();
        };

        return $factories;
    }

    public function test_canLoadAllRegisteredModules_whenModulesRegistered() {
        $this->_registerCustomInjectableServiceFactories();
        $pluginHost = $this->_getPluginHostWithModuleRequiringCustomDependencies();

        $pluginHost->load();

        $this->assertTrue(SamplePluginModuleCallState::hasCalledLoadForModuleCount(3));
        $this->assertTrue(SamplePluginModuleCallState::hasModuleLoadBeenCalledExactlyOnce(RequiresAllSupportedDependenciesSamplePluginModule::class));
        $this->assertTrue(SamplePluginModuleCallState::hasModuleLoadBeenCalledExactlyOnce(RequiresSupportedDependenciesSamplePluginModule::class));
        $this->assertTrue(SamplePluginModuleCallState::hasModuleLoadBeenCalledExactlyOnce(HasOnlyCustomAvailableDependenciesPluginModule::class));
    }

    public function test_canLoadAllRegisteredModules_whenNoModulesRegistered() {
        $pluginHost = $this->_getPluginHostNoModules();
        $pluginHost->load();
    }

    private function _getPluginHostNoModules() {
        return new Abp01_PluginModules_PluginModuleHost(new Abp01_Plugin(), array());
    }
}