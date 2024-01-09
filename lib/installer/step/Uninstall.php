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

class Abp01_Installer_Step_Uninstall implements Abp01_Installer_Step {
	/**
	 * @var \Exception|\WP_Error
	 */
	private $_lastError;

	private $_env;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}
	
    public function execute() { 
		$this->_reset();
		try {
			return $this->_deactivate()
				&& $this->_uninstallSchema()
				&& $this->_purgeSettings()
				&& $this->_purgeChangeLogCache()
				&& $this->_removeStorageDirectories()
				&& $this->_uninstallVersion();
		} catch (Exception $e) {
			$this->_lastError = $e;
		}
		return false;
	}

	private function _deactivate() {
		$step = new Abp01_Installer_Step_Deactivate();
		return $this->_executeStep($step);
	}

	private function _executeStep(Abp01_Installer_Step $step) {
		$result = $step->execute();
		$this->_lastError = $step->getLastError();
		return $result;
	}

	private function _purgeSettings() {
		$step = new Abp01_Installer_Step_PurgeSettings();
		return $this->_executeStep($step);
	}

	private function _purgeChangeLogCache() {
		Abp01_ChangeLogDataSource_Cached::clearCache();
		return true;
	}

	private function _uninstallSchema() {
		$step = new Abp01_Installer_Step_UninstallSchema($this->_env);
		return $this->_executeStep($step);
	}

	private function _removeStorageDirectories() {
		$step = new Abp01_Installer_Step_RemoveStorageDirectories($this->_env);
		return $this->_executeStep($step);
	}

	private function _uninstallVersion() {
		$step = new Abp01_Installer_Step_UnsetCurrentVersion();
		return $this->_executeStep($step);
	}

	private function _reset() {
		$this->_lastError = null;
	}

    public function getLastError() { 
		return $this->_lastError;
	}
}