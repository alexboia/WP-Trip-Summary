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
class Abp01_PluginModules_PluginModuleActivator {
	private $_injectableServiceFactories = array();

	public function __construct(array $injectableServiceFactories) {
		$this->_injectableServiceFactories = $injectableServiceFactories;
	}

	public function isValidModuleClass($moduleClassName) {
		return !empty($moduleClassName) 
			&& $this->_moduleClassExtendsBaseModuleClass($moduleClassName);
	}

	private function _moduleClassExtendsBaseModuleClass($moduleClassName) {
		$parents = class_parents($moduleClassName, true);
		return $parents !== false 
			&& in_array('Abp01_PluginModules_PluginModule', $parents);
	}

	public function createModuleInstance($moduleClassName) {
		if (empty($moduleClassName)) {
			throw new InvalidArgumentException('Module class name may not be empty.');
		}

		if (!$this->isValidModuleClass($moduleClassName)) {
			throw new InvalidArgumentException('Module class <' . $moduleClassName . '> is not a valid module class.');
		}

		$moduleClass = new ReflectionClass($moduleClassName);
		$moduleDependencyClassNames = $this->_determineModuleDependencyClassNames($moduleClass);
		$moduleDependencyInstances = $this->_createModuleDependencyInstances($moduleDependencyClassNames);

		$moduleInstance = $moduleClass->newInstanceArgs($moduleDependencyInstances);
		return $moduleInstance;
	}

	private function _determineModuleDependencyClassNames(ReflectionClass $moduleClass) {
		$dependencyClassNames = array();
		$moduleClassConstructorParams = $this->_determineModuleClassConstructorParameters($moduleClass);

		foreach ($moduleClassConstructorParams as $constructorParamInfo) {
			$parameterType = $constructorParamInfo->getType();
			if ($parameterType != null) {
				$dependencyClassNames[] = $this->_determineDependencyClassName($parameterType);
			} else {
				throw new Abp01_PluginModules_Exception('Not all parameters of module class <' . $moduleClass->getName() . '> constructor have type information');
			}
		}

		return $dependencyClassNames;
	}

	private function _determineModuleClassConstructorParameters(ReflectionClass $moduleClass) {
		$constructorParams = array();
		$constructorInfo = $moduleClass->getConstructor();

		if ($constructorInfo != null) {
			$constructorParams = $constructorInfo->getParameters();    
		}

		return $constructorParams;
	}

	private function _determineDependencyClassName(ReflectionType $type) {
		return ($type instanceof ReflectionNamedType) 
			? $type->getName()
			: $type->__toString();
	}

	private function _createModuleDependencyInstances($dependencyClassNames) {
		$dependencyInstances = array();

		foreach ($dependencyClassNames as $dependencyClassName) {
			if ($this->_isClassDependencyInjectable($dependencyClassName)) {
				$dependencyInstances[] = $this->_createDependencyInstance($dependencyClassName);
			} else {
				throw new Abp01_PluginModules_Exception('Module dependency <' . $dependencyClassName . '> could not be resolved.');
			}
		}

		return $dependencyInstances;
	}

	private function _isClassDependencyInjectable($dependencyClassName) {
		return !empty($dependencyClassName) 
			&& isset($this->_injectableServiceFactories[$dependencyClassName]);
	}

	private function _createDependencyInstance($dependencyClassName) {
		$dependencyFactory = $this->_injectableServiceFactories[$dependencyClassName];
		return $dependencyFactory();
	}
}