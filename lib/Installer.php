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
	 * @var array An array of cached lookup data items definitions
	 */
	private $_cachedDefinitions = null;

	/**
	 * Creates a new installer instance
	 * @param bool $installLookupData Whether or not to install lookup data
	 */
	public function __construct($installLookupData = true) {
		$this->_env = Abp01_Env::getInstance();
		$this->_installLookupData = $installLookupData;
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

	private function _update($version, $installedVersion) {
		$this->_reset();
		$result = true;

		if (empty($installedVersion)) {
			//If no installed version is set, this is the very first version, 
			//  we need to run all updates in order
			$result = $this->_updateTo02Beta() 
				&& $this->_updateTo021() 
				&& $this->_updateTo022()
				&& $this->_updateTo024();
		} else {
			//...otherwise, we need to see 
			//  which installed version this is
			switch ($installedVersion) {
				case '0.2b':
					$result = $this->_updateTo021() 
						&& $this->_updateTo022()
						&& $this->_updateTo024();
				break;
				case '0.2.1':
					$result = $this->_updateTo022() 
						&& $this->_updateTo024();
				break;
				case '0.2.2':
				case '0.2.3':
					$result = $this->_updateTo024();
					break;
			}
		}

		//Finally, run the update to 0.2.7, 
		//  if the pervious updates (if there were any), 
		//  were successful
		if ($result) {
			$result = $this->_updateTo027();
		}

		if ($result) {
			update_option(self::OPT_VERSION, $version);
		}
		return $result;
	}

	private function _updateTo02Beta() {
		$step = new Abp01_Installer_Step_Update_UpdateTo02Beta($this->_env);
		return $this->_executeStep($step);
	}

	private function _updateTo021() {
		$step = new Abp01_Installer_Step_Update_UpdateTo021($this->_env);
		return $this->_executeStep($step);
	}

	private function _executeStep(Abp01_Installer_Step $step) {
		$result = $step->execute();
		$this->_lastError = $step->getLastError();
		return $result;
	}

	private function _ensureStorageDirectories() {
		$result = true;
		$rootStorageDir = $this->_env->getRootStorageDir();
		
		if (!is_dir($rootStorageDir)) {
			@mkdir($rootStorageDir);
		}

		if (is_dir($rootStorageDir)) {
			$tracksStorageDir = $this->_env->getTracksStorageDir();
			if (!is_dir($tracksStorageDir)) {
				@mkdir($tracksStorageDir);
			}

			if (is_dir($tracksStorageDir)) {
				$cacheStorageDir = $this->_env->getCacheStorageDir();
				if (!is_dir($cacheStorageDir)) {
					@mkdir($cacheStorageDir);
				}

				$result = is_dir($cacheStorageDir);
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}

		return $result;
	}

	private function _removeStorageDirectories() {
		$step = new Abp01_Installer_Step_RemoveStorageDirectories($this->_env);
		return $this->_executeStep($step);
	}

	private function _updateTo022() {
		$step = new Abp01_Installer_Step_Update_UpdateTo022($this->_env);
		return $this->_executeStep($step);
	}

	private function _updateTo024() {
		$step = new Abp01_Installer_Step_Update_UpdateTo024($this->_env);
		return $this->_executeStep($step);
	}

	private function _updateTo027() {
		$step = new Abp01_Installer_Step_Update_UpdateTo027($this->_env);
		return $this->_executeStep($step);
	}

	private function _installStorageDirsSecurityAssets() {
		$step = new Abp01_Installer_Step_InstallStorageDirectoryAndAssets($this->_env);
		return $this->_executeStep($step);
	}

	/**
	 * Checks the current plug-in package version, the currently installed version
	 *  and runs the update operation if they differ.
	 * 
	 * @return bool The operation result: true if succeeded, false otherwise
	 */
	public function updateIfNeeded() {
		$result = true;
		$version = $this->_getVersion();
		$installedVersion = $this->_getInstalledVersion();

		if ($this->_isUpdatedNeeded($version, $installedVersion)) {
			$result = $this->_update($version, $installedVersion);
		}

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
					update_option(self::OPT_VERSION, $this->_getVersion());
					return true;
				} else {
					return false;
				}
			}
		} catch (Exception $e) {
			$this->_lastError = $e;
		}
		return false;
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
		try {
			return $this->_removeCapabilities();
		} catch (Exception $e) {
			$this->_lastError = $e;
		}
		return false;
	}

	public function uninstall() {
		$this->_reset();
		try {
			return $this->deactivate()
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
		$result = false;
		if ($this->_ensureStorageDirectories()) {
			$result = $this->_installStorageDirsSecurityAssets();
		}
		return $result;
	}

	private function _createCapabilities() {
		Abp01_Auth::getInstance()->installCapabilities();
		return true;
	}

	private function _removeCapabilities() {
		Abp01_Auth::getInstance()->removeCapabilities();
		return true;
	}

	private function _uninstallVersion() {
		delete_option(self::OPT_VERSION);
		return true;
	}

	private function _purgeSettings() {
		abp01_get_settings()->purgeAllSettings();
		return true;
	}

	private function _purgeChangeLogCache() {
		Abp01_ChangeLogDataSource_Cached::clearCache();
		return true;
	}

	private function _installData() {
		if (!$this->_installLookupData) {
			return true;
		}

		$step = new Abp01_Installer_Step_InstallData($this->_env);
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

	private function _reset() {
		$this->_lastError = null;
	}
}