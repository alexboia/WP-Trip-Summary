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

class Abp01_Installer_Step_Update implements Abp01_Installer_Step {

	const OPT_VERSION = Abp01_Installer_Constants::OPT_VERSION;

	/**
	 * @var \Exception|\WP_Error|null
	 */
	private $_lastError;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

    public function execute() { 
		$result = true;
		$version = $this->_getVersion();
		$installedVersion = $this->_getInstalledVersion();

		if ($this->_isUpdatedNeeded($version, $installedVersion)) {
			$result = $this->_update($version, $installedVersion);
		}

		return $result;
	}

	private function _update($version, $installedVersion) {
		$result = true;
		$steps = $this->_computeRequiredSteps($installedVersion);

		foreach ($steps as $step) {
			$result = $result && $this->_executeUpdateStep($step);
			if (!$result) {
				break;
			}
		}

		return $result;
	}

	/**
	 * @return Abp01_Installer_Step[] 
	 */
	private function _computeRequiredSteps($installedVersion) {
		$steps = array(
			new Abp01_Installer_Step_Update_UpdateTo02Beta($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo021($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo022($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo024($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo027($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo029($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo030($this->_env),
			new Abp01_Installer_Step_Update_UpdateTo032($this->_env)
		);

		if (!empty($installedVersion)) {
			$steps = array_filter($steps, 
				function(Abp01_Installer_Step_Update_Interface $step) use($installedVersion) {
					return version_compare($installedVersion, 
						$step->getTargetVersion(), 
						'<');
				}
			);
		}

		$steps[] = new Abp01_Installer_Step_SetCurrentVersion($this->_env);
		return $steps;
	}

	private function _executeUpdateStep(Abp01_Installer_Step $step) {
		$result = $step->execute();
		$this->_lastError = $step->getLastError();
		return $result;
	}

	private function _getVersion() {
		return $this->_env->getVersion();
	}

	private function _isUpdatedNeeded($version, $installedVersion) {
		return $version != $installedVersion;
	}

	private function _getInstalledVersion() {
		$version = null;
		if (function_exists('get_option')) {
			$version = get_option(self::OPT_VERSION, null);
		}
		return $version;
	}

    public function getLastError() { 
		return $this->_lastError;
	}
}