<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class PluginModuleActivatorTests extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        SamplePluginModuleCreationState::reset();
    }

    public function tearDown() {
        parent::tearDown();
        SamplePluginModuleCreationState::reset();
    }

    public function test_canCheckIfModuleClassValid_validModuleClasses() {
        $validModuleClasses = array(
            NoDependenciesSamplePluginModule::class,
            RequiresOnlyUnsupportedDependenciesSamplePluginModule::class,
            RequiresSupportedDependenciesSamplePluginModule::class,
            RequiresSomeUnsupportedDependenciesSamplePluginModule::class,
            RequiresOneSupportedDependencySamplePluginModule::class
        );

        $activator = $this->_getPluginModuleActivator();
        foreach ($validModuleClasses as $className)  {
            $this->assertTrue($activator->isValidModuleClass($className));
        }
    }

    public function test_canCheckIfModuleClassValid_invalidModuleClasses() {
        $invalidModuleClasses = array(
            NotAValidModuleClassSamplePluginModule::class
        );

        $activator = $this->_getPluginModuleActivator();
        foreach ($invalidModuleClasses as $className)  {
            $this->assertFalse($activator->isValidModuleClass($className));
        }
    }

    public function test_canCreateModuleInstance_validModuleClass_noDependencies() {
        $activator = $this->_getPluginModuleActivator();
        $moduleInstance = $activator->createModuleInstance(NoDependenciesSamplePluginModule::class);

        $this->assertNotNull($moduleInstance);
        $this->assertTrue($moduleInstance instanceof NoDependenciesSamplePluginModule);
    }

    public function test_canCreateModuleInstance_validModuleClass_allSupportedDependencies() {
        $activator = $this->_getPluginModuleActivator();
        $moduleInstance = $activator->createModuleInstance(RequiresSupportedDependenciesSamplePluginModule::class);

        $this->assertNotNull($moduleInstance);
        $this->assertTrue($moduleInstance instanceof RequiresSupportedDependenciesSamplePluginModule);

        $this->assertTrue($moduleInstance->hasView());
        $this->assertTrue($moduleInstance->hasSettings());
        $this->assertTrue($moduleInstance->hasRouteManager());
        $this->assertTrue($moduleInstance->hasEnv());
        $this->assertTrue($moduleInstance->hasHelp());
    }

    public function test_canCreateModuleInstance_validModuleClass_someSupportedDependencies() {
        $activator = $this->_getPluginModuleActivator();
        $moduleInstance = $activator->createModuleInstance(RequiresOneSupportedDependencySamplePluginModule::class);

        $this->assertNotNull($moduleInstance);
        $this->assertTrue($moduleInstance instanceof RequiresOneSupportedDependencySamplePluginModule);

        $this->assertTrue($moduleInstance->hasHelp());
    }

    /**
     * @expectedException Abp01_PluginModules_Exception
     */
    public function test_tryCreateModuleInstance_validModuleClass_someUnsupportedDependencies() {
        $activator = $this->_getPluginModuleActivator();
        $activator->createModuleInstance(RequiresSomeUnsupportedDependenciesSamplePluginModule::class);
    }

    /**
     * @expectedException Abp01_PluginModules_Exception
     */
    public function test_tryCreateModuleInstance_validModuleClass_onlyUnsupportedDependencies() {
        $activator = $this->_getPluginModuleActivator();
        $activator->createModuleInstance(RequiresOnlyUnsupportedDependenciesSamplePluginModule::class);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_tryCreateModuleInstance_invalidModuleClass() {
        $activator = $this->_getPluginModuleActivator();
        $activator->createModuleInstance(NotAValidModuleClassSamplePluginModule::class);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_tryCreateModuleInstance_nullModuleClassName() {
        $activator = $this->_getPluginModuleActivator();
        $activator->createModuleInstance(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_tryCreateModuleInstance_emptyModuleClassName() {
        $activator = $this->_getPluginModuleActivator();
        $activator->createModuleInstance('');
    }

    private function _getPluginModuleActivator() {
        return new Abp01_PluginModules_PluginModuleActivator($this->_getInjectableServiceFactories());
    }

    private function _getInjectableServiceFactories() {
        return array(
            Abp01_Settings::class => function() {
                return abp01_get_settings();
            },
            Abp01_Env::class => function() { 
                return abp01_get_env();
            },
            Abp01_Auth::class => function() {
                return abp01_get_auth();
            },
            Abp01_View::class => function() {
                return abp01_get_view();
            },
            Abp01_Route_Manager::class => function() {
                return abp01_get_route_manager();
            },
            Abp01_Help::class => function() {
                return abp01_get_help();
            }
        );
    }
}