<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

use WpTripSummary\Env;

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Installer_Step_Activate implements Abp01_Installer_Step {
	private \Exception|\WP_Error|null $_lastError;

	private Env $_env;

	private bool $_installLookupData;

	public function __construct(Env $env, bool $installLookupData) {
		$this->_env = $env;
		$this->_installLookupData = ($installLookupData === true);
	}

    public function execute(): bool { 
		$this->_reset();
		$context = new Abp01_Installer_Context();
		$preInstallOk = $this->_executeHookStep(
			new Abp01_Installer_Step_RunPreInstallHooks($context),
			$context
		);

		if (!$preInstallOk || !$context->isSuccessful()) {
			return false;
		}

		$result = $this->_activate();
		$this->_recordStepError($context, $result);

		$this->_executeHookStep(
			new Abp01_Installer_Step_RunPostInstallHooks($context),
			$context
		);

		return $result;
	}

	private function _activate(): bool {
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

	private function _executeHookStep(Abp01_Installer_Step $step,
		Abp01_Installer_Context $context): bool {
		$result = $step->execute();
		$error = $step->getLastError();
		
		if ($error !== null) {
			$context->pushHookError(get_class($step), $error);
		}

		return $result;
	}

	private function _recordStepError(Abp01_Installer_Context $context,
		bool $result): void {
		if ($result) {
			return;
		}

		$error = $this->_lastError;
		if ($error === null) {
			$error = new RuntimeException(sprintf(
				'%s failed without reporting an error.',
				get_class($this)
			));
		}

		$context->pushError(get_class($this), $error);
	}

	private function _installStorageDirectoryAndAssets(): bool {
		$step = new Abp01_Installer_Step_InstallStorageDirectoryAndAssets($this->_env);
		return $this->_executeStep($step);
	}

	private function _executeStep(Abp01_Installer_Step $step): mixed {
		$result = $step->execute();
		$this->_lastError = $step->getLastError();
		return $result;
	}

	private function _removeStorageDirectories(): bool {
		$step = new Abp01_Installer_Step_RemoveStorageDirectories($this->_env);
		return $this->_executeStep($step);
	}

	private function _installSchema(): bool {
		$step = new Abp01_Installer_Step_InstallSchema($this->_env);
		$result = $this->_executeStep($step);

		if (!$result) {
			$this->_uninstallSchema();
		}

		return $result;
	}

	private function _uninstallSchema(): bool {
		$step = new Abp01_Installer_Step_UninstallSchema($this->_env);
		return $this->_executeStep($step);
	}

	private function _installData(): bool {
		if (!$this->_installLookupData) {
			return true;
		}

		$step = new Abp01_Installer_Step_InstallData($this->_env);
		return $this->_executeStep($step);
	}

	private function _createCapabilities(): bool {
		$step = new Abp01_Installer_Step_CreateCapabilities();
		return $this->_executeStep($step);
	}

	private function _reset(): void {
		$this->_lastError = null;
	}

	private function _setCurrentVersion(): bool {
		$step = new Abp01_Installer_Step_SetCurrentVersion($this->_env);
		return $this->_executeStep($step);
	}

    public function getLastError(): Exception|WP_Error|null { 
		return $this->_lastError;
	}
}