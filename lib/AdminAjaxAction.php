<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class Abp01_AdminAjaxAction {
	private $_actionCode;

	private $_callback;

	private $_requiresAuthentication = true;

	/**
	 * @var Abp01_NonceProvider
	 */
	private $_nonceProvider = null;

	/**
	 * @var Abp01_AdminAjaxAction_AuthorizationProvider
	 */
	private $_authProvider = null;

	public function __construct($actionCode, $callback) {
		$this->_actionCode = $actionCode;
		$this->_callback = $callback;

		$this->useAuthorizationProvider(new Abp01_AdminAjaxAction_AuthorizationProvider_AlwaysTrue());
		$this->useNonceProvider(new Abp01_NonceProvider_None());
	}

	public static function create($actionCode, $callback) {
		return new self($actionCode, $callback);
	}

	public function useDefaultNonceProvider($urlParamName) {
		$this->useNonceProvider(new Abp01_NonceProvider_Default($this->_actionCode, $urlParamName));
		return $this;
	}

	public function useNonceProvider(Abp01_NonceProvider $nonceProvider) {
		$this->_nonceProvider = $nonceProvider;
		return $this;
	}

	public function setRequiresAuthentication($requiresAuthentication) {
		$this->_requiresAuthentication = $requiresAuthentication;
		return $this;
	}

	public function authorizeByCapability($requiredCapability) {
		$this->useAuthorizationProvider(new Abp01_AdminAjaxAction_AuthorizationProvider_Simple($requiredCapability));
		return $this;
	}

	public function authorizeByCallback($callback) {
		$this->useAuthorizationProvider(new Abp01_AdminAjaxAction_AuthorizationProvider_Callback($callback));
		return $this;
	}

	public function useAuthorizationProvider(Abp01_AdminAjaxAction_AuthorizationProvider $authProvider) {
		$this->_authProvider = $authProvider;
		return $this;
	}

	public function generateNonce() {
		return $this->_nonceProvider->generateNonce();
	}

	public function isNonceValid() {
		return $this->_nonceProvider->valdidateNonce();
	}

	public function register() {
		$callback = array($this, 'executeAndSendJsonThenExit');

		add_action('wp_ajax_' . $this->_actionCode,
			$callback);

		if (!$this->_requiresAuthentication) {
			add_action('wp_ajax_nopriv_' . $this->_actionCode, 
				$callback);
		}

		return $this;
	}

	public function execute() {
		if (!$this->isNonceValid() 
			|| !$this->_currentUserCanExecute()) {
			die;
		}

		return call_user_func($this->_callback);
	}

	public function executeAndSendJsonThenExit() {
		$executionResult = $this->execute();
		$this->_sendJsonAndExit($executionResult);
	}

	private function _currentUserCanExecute() {
		return $this->_authProvider
			->isAuthorizedToExecuteAction();
	}

	private function _sendJsonAndExit($data) {
		abp01_send_json($data, true);
	}
}