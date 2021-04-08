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
	const HTTP_GET = 'get';

	const HTTP_POST = 'post';

	const HTTP_PUT = 'put';

	const HTTP_PATCH = 'patch';

	const HTTP_DELETE = 'delete';

	const HTTP_ANY = '*';

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

	private $_allowedHttpMethods = array();

	/**
	 * @var Abp01_AdminAjaxAction_CurrentResourceProvider
	 */
	private $_currentResourceProvider;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct($actionCode, $callback) {
		$this->_env = abp01_get_env();
		$this->_actionCode = $actionCode;
		$this->_callback = $callback;

		$this->useCurrentResourceProvider(new Abp01_AdminAjaxAction_CurrentResourceProvider_None());
		$this->useAuthorizationProvider(new Abp01_AdminAjaxAction_AuthorizationProvider_AlwaysTrue());
		$this->useNonceProvider(new Abp01_NonceProvider_None());
		$this->allowAllHttpMethods();
	}

	public static function create($actionCode, $callback) {
		return new self($actionCode, $callback);
	}

	public function useCurrentResourceProvider(Abp01_AdminAjaxAction_CurrentResourceProvider $currentResourceProvider) {
		$this->_currentResourceProvider = $currentResourceProvider;
		return $this;
	}

	public function onlyForHttpMethod($httpMethod) {
		if (empty($httpMethod)) {
			throw new InvalidArgumentException('Http method may not be null');
		}

		$this->_allowedHttpMethods = array($httpMethod);
		return $this;
	}

	public function onlyForHttpGet() {
		return $this->onlyForHttpMethod(self::HTTP_GET);
	}

	public function onlyForHttpPost() {
		return $this->onlyForHttpMethod(self::HTTP_POST);
	}

	public function onlyForHttpPatch() {
		return $this->onlyForHttpMethod(self::HTTP_PATCH);
	}

	public function onlyForHttpDelete() {
		return $this->onlyForHttpMethod(self::HTTP_DELETE);
	}

	public function onlyForHttpPut() {
		return $this->onlyForHttpMethod(self::HTTP_PUT);
	}

	public function allowAllHttpMethods() {
		return $this->onlyForHttpMethod(self::HTTP_ANY);
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

	public function getCurrentResourceId() {
		return $this->_currentResourceProvider
			->getCurrentResourceId();
	}

	public function generateNonce() {
		$currentResourceId = $this
			->getCurrentResourceId();
		return $this->_nonceProvider
			->generateNonce($currentResourceId);
	}

	public function isNonceValid() {
		$currentResourceId = $this
			->getCurrentResourceId();
		return $this->_nonceProvider
			->validateNonce($currentResourceId);
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
			|| !$this->_isCurrentHttpMethodAllowed()
			|| !$this->_currentUserCanExecute()) {
			abp01_die();
		} else {
			return call_user_func($this->_callback);
		}
	}

	private function _isCurrentHttpMethodAllowed() {
		$currentMethod = $this->_env->getHttpMethod();
		return $this->_isHttpMethodAllowed($currentMethod)
			|| $this->_isHttpMethodAllowed(self::HTTP_ANY);
	}

	private function _isHttpMethodAllowed($method) {
		return in_array($method, $this->_allowedHttpMethods);
	}

	private function _currentUserCanExecute() {
		return $this->_authProvider
			->isAuthorizedToExecuteAction();
	}

	public function executeAndSendJsonThenExit() {
		$executionResult = $this->execute();
		$this->_sendJsonAndExit($executionResult);
	}

	private function _sendJsonAndExit($data) {
		abp01_send_json($data, true);
	}
}