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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Installer_Context {
	/**
	 * 
	 * @var array<string, \Exception|WP_Error|null>
	 */
	private $_errors = array();

	/**
	 * 
	 * @var array<string, \Exception|WP_Error|null>
	 */
	private array $_hookErrors = array();

	private \Exception|WP_Error|null $_lastError = null;

	/**
	 * @param string $key
	 * @param \Exception|WP_Error $error 
	 * @return void 
	 */
	public function pushError($key, \Exception|WP_Error $error) {
		$this->_errors[$key] = $error;
		$this->_lastError = $error;
	}

	public function getErrors() {
		return $this->_errors;
	}

	public function hasErrors() {
		return !empty($this->_errors);
	}

	/**
	 * @param string $key
	 * @param \Exception|WP_Error $error 
	 * @return void 
	 */
	public function pushHookError($key, \Exception|WP_Error $error) {
		$this->_hookErrors[$key] = $error;
		$this->_lastError = $error;
	}

	public function getHookErrors() {
		return $this->_hookErrors;
	}

	public function hasHookErrors() {
		return !empty($this->_hookErrors);
	}

	public function isSuccessful() {
		return !$this->hasErrors() && !$this->hasHookErrors();
	}

	public function reset() {
		$this->_errors = array();
		$this->_hookErrors = array();
		$this->_lastError = null;
	}

	public function getLastError(): \Exception|WP_Error|null {
		return $this->_lastError;
	}
}