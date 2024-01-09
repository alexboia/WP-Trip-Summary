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

class Abp01_Installer {
	/**
	 * @var int Status code returned when all installation requirements have been met
	 */
	const ALL_REQUIREMENTS_MET = Abp01_Installer_RequirementStatusCode::ALL_REQUIREMENTS_MET;

	/**
	 * @var int Error code returned when an incompatible PHP version is detected upon installation
	 */
	const INCOMPATIBLE_PHP_VERSION = Abp01_Installer_RequirementStatusCode::INCOMPATIBLE_PHP_VERSION;

	/**
	 * @var int Error code returned when an incompatible WordPress version is detected upon installation
	 */
	const INCOMPATIBLE_WP_VERSION = Abp01_Installer_RequirementStatusCode::INCOMPATIBLE_WP_VERSION;

	/**
	 * @var int Error code returned when LIBXML is not found
	 */
	const SUPPORT_LIBXML_NOT_FOUND = Abp01_Installer_RequirementStatusCode::SUPPORT_LIBXML_NOT_FOUND;

	/**
	 * @var int Error code returned when MySQL Spatial extension is not found
	 */
	const SUPPORT_MYSQL_SPATIAL_NOT_FOUND = Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQL_SPATIAL_NOT_FOUND;

	/**
	 * @var int Error code returned when MySqli extension is not found
	 */
	const SUPPORT_MYSQLI_NOT_FOUND = Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQLI_NOT_FOUND;

	/**
	 * @var int Error code returned when the installation capabilities cannot be detected
	 */
	const COULD_NOT_DETECT_INSTALLATION_CAPABILITIES = Abp01_Installer_RequirementStatusCode::COULD_NOT_DETECT_INSTALLATION_CAPABILITIES;

	/**
	 * @var string WP options key for current plug-in version
	 */
	const OPT_VERSION = 'abp01.option.version';

	/**
	 * @var Abp01_Env The current instance of the plug-in environment
	 */
	private $_env;

	/**
	 * @var mixed The last occured error
	 */
	private $_lastError = null;

	/**
	 * @var bool Whether or not to install lookup data
	 */
	private $_installLookupData;

	/**
	 * Creates a new installer instance
	 * @param bool $installLookupData Whether or not to install lookup data
	 */
	public function __construct($installLookupData = true) {
		$this->_env = Abp01_Env::getInstance();
		$this->_installLookupData = $installLookupData;
	}

	/**
	 * Checks the current plug-in package version, the currently installed version
	 *  and runs the update operation if they differ.
	 * 
	 * @return bool The operation result: true if succeeded, false otherwise
	 */
	public function updateIfNeeded() {
		$step = new Abp01_Installer_Step_Update($this->_env);
		return $this->_executeStep($step);
	}

	private function _executeStep(Abp01_Installer_Step $step) {
		$result = $step->execute();
		$this->_lastError = $step->getLastError();
		return $result;
	}

	/**
	 * Checks whether the plug-in can be installed and returns 
	 *  a code that describes the reason it cannot be installed
	 *  or Installer::INSTALL_OK if it can.
	 * 
	 * @return int The error code that describes the result of the test.
	 */
	public function checkRequirements() {
		$this->_reset();

		try {
			$checker = $this->_getChecker();
			$result = $checker->check();
			if ($result !== self::ALL_REQUIREMENTS_MET) {
				$this->_lastError = $checker->getLastError();
			}

			return $result;
		} catch (Exception $e) {
			$this->_lastError = $e;
			return self::COULD_NOT_DETECT_INSTALLATION_CAPABILITIES;
		}
	}

	private function _getChecker() {
		return new Abp01_Installer_Requirement_Checker(
			new Abp01_Installer_Requirement_Provider_Default(
				$this->_env
			)
		);
	}

	/**
	 * Activates the plug-in. 
	 * If a step of the activation process fails, 
	 *  the plug-in attempts to rollback the steps that did successfully execute.
	 * The activation process is idempotent, that is, 
	 *  it will not perform the same operations twice.
	 * 
	 * @return bool True if the operation succeeded, false otherwise.
	 */
	public function activate() {
		$this->_reset();
		$step = new Abp01_Installer_Step_Activate(
			$this->_env, 
			$this->_installLookupData
		);
		return $this->_executeStep($step);
	}

	/**
	 * Deactivates the plug-in.
	 * If a step of the activation process fails, 
	 *  the plug-in attempts to rollback the steps 
	 *  that did successfully execute.
	 * 
	 * @return bool True if the operation succeeded, false otherwise. 
	 */
	public function deactivate() {
		$this->_reset();
		$step = new Abp01_Installer_Step_Deactivate();
		return $this->_executeStep($step);
	}

	public function uninstall() {
		$this->_reset();
		$step = new Abp01_Installer_Step_Uninstall($this->_env);
		return $this->_executeStep($step);
	}

	/**
	 * Ensures all the plug-in's storage directories are created, 
	 *  as well as any required assets.
	 * If a directory exists, it is not re-created, nor is it purged.
	 * If a file asset exists, it is overwritten.
	 * 
	 * @return bool True if the operation succeeded, false otherwise
	 */
	public function ensureStorageDirectoriesAndAssets() {
		$this->_installStorageDirectoryAndAssets();
	}

	public function getRequiredPhpVersion() {
		return $this->_env->getRequiredPhpVersion();
	}

	public function getRequiredWpVersion() {
		return $this->_env->getRequiredWpVersion();
	}

	/**
	 * Returns the last occurred exception or null if none found.
	 * 
	 * @return \Exception The last occurred exception.
	 */
	public function getLastError() {
		return $this->_lastError;
	}

	private function _installStorageDirectoryAndAssets() {
		$this->_reset();
		$step = new Abp01_Installer_Step_InstallStorageDirectoryAndAssets($this->_env);
		return $this->_executeStep($step);
	}

	private function _reset() {
		$this->_lastError = null;
	}
}