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

class Abp01_Installer_Step_Activate implements Abp01_Installer_Step {
	/**
	 * @var \Exception|\WP_Error
	 */
	private $_lastError;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var bool
	 */
	private $_installLookupData;

	public function __construct(Abp01_Env $env, $installLookupData) {
		$this->_env = $env;
		$this->_installLookupData = 
			($installLookupData === true);
	}

    public function execute() { 
		$this->_reset();
		try {
			if (!$this->_installStorageDirectoryAndAssets()) {
				//Ensure no partial directory and file structure remains
				$this->_removeStorageDirectories();
				return false;
			}

			if (!$this->_installSchema()) {
				//Ensure no partial directory and file structure remains
				$this->_removeStorageDirectories();
				return false;
			}

			if (!$this->_installData()) {
				//Ensure no partial directory and file structure remains
				$this->_removeStorageDirectories();
				//Remove schema as well
				$this->_uninstallSchema();
				return false;
			} else {
				if ($this->_createCapabilities()) {
					return $this->_setCurrentVersion();
				} else {
					return false;
				}
			}
		} catch (Exception $e) {
			$this->_lastError = $e;
		}
		return false;
	}

	private function _installStorageDirectoryAndAssets() {
		$step = new Abp01_Installer_Step_InstallStorageDirectoryAndAssets($this->_env);
		return $this->_executeStep($step);
	}

	private function _executeStep(Abp01_Installer_Step $step) {
		$result = $step->execute();
		$this->_lastError = $step->getLastError();
		return $result;
	}

	private function _removeStorageDirectories() {
		$step = new Abp01_Installer_Step_RemoveStorageDirectories($this->_env);
		return $this->_executeStep($step);
	}

	private function _installSchema() {
		$step = new Abp01_Installer_Step_InstallSchema($this->_env);
		$result = $this->_executeStep($step);

		if (!$result) {
			$this->_uninstallSchema();
		}

		return $result;
	}

	private function _uninstallSchema() {
		$step = new Abp01_Installer_Step_UninstallSchema($this->_env);
		return $this->_executeStep($step);
	}

	private function _installData() {
		if (!$this->_installLookupData) {
			return true;
		}

		$step = new Abp01_Installer_Step_InstallData($this->_env);
		return $this->_executeStep($step);
	}

	private function _createCapabilities() {
		$step = new Abp01_Installer_Step_CreateCapabilities();
		return $this->_executeStep($step);
	}

	private function _reset() {
		$this->_lastError = null;
	}

	private function _setCurrentVersion() {
		$step = new Abp01_Installer_Step_SetCurrentVersion($this->_env);
		return $this->_executeStep($step);
	}

    public function getLastError() { 
		return $this->_lastError;
	}
}