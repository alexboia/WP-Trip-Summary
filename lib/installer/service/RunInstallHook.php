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

if (!defined('ABP01_LOADED')) {
	exit;
}

class Abp01_Installer_Service_RunInstallHook {
	private \Exception|\WP_Error|null $_lastError;

	private Abp01_Installer_Context $_context;

	private string $_hookName;

	public function __construct(string $hookName, Abp01_Installer_Context $context) {
		$this->_hookName = $hookName;
		$this->_context = $context;
	}

    public function execute(): bool { 
		$this->_reset();
		$this->_runHooks();
		return !$this->hasError();
	}

	private function _runHooks(): void {
		try {
			do_action($this->_hookName, $this->_context);	
		} catch (Throwable $hookError) {
			$this->_context->pushHookError($this->_hookName, $hookError);
			$this->_lastError = $hookError;
		}
	}

    public function getLastError(): Exception|WP_Error|null { 
		return $this->_lastError;
	}

	private function _reset(): void {
		$this->_lastError = null;
	}

	public function hasError(): bool {
		return $this->_lastError !== null;
	}
}