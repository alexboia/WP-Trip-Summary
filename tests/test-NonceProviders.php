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

class NonceProvidersTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canGenerateNonce() {
		$providersInfos = $this->_getProvidersInfosForTesting();
		foreach ($providersInfos as $providerInfo) {
			$provider = $providerInfo['provider'];
			$this->_runNonceGenerationTest_noResourceId($provider);
			$this->_runNonceGenerationTest_withResourceId($provider);
		}
	}

	private function _runNonceGenerationTest_noResourceId(Abp01_NonceProvider $provider) {
		$nonce = $provider->generateNonce();
		$this->assertNotEmpty($nonce);
	}

	private function _runNonceGenerationTest_withResourceId(Abp01_NonceProvider $provider) {
		$nonce = $provider->generateNonce($this->_generateNonEmptyAscii());
		$this->assertNotEmpty($nonce);
	}

	public function test_canVerifyGeneratedNonce() {
		$providersInfos = $this->_getProvidersInfosForTesting();
		foreach ($providersInfos as $providerInfo) {
			$provider = $providerInfo['provider'];
			$urlParamName = $providerInfo['url_param_name'];
			$this->_runNonceVerificationTest_noResourceId($provider, $urlParamName);
			$this->_runNonceVerificationTest_withResourceId($provider, $urlParamName);
		}
	}

	private function _runNonceVerificationTest_noResourceId(Abp01_NonceProvider $provider, $urlParamName) {
		$nonce = $provider->generateNonce();
		$this->_storeNonceToRequestContext($urlParamName, $nonce);
		$this->assertTrue($provider->validateNonce());
	}

	private function _runNonceVerificationTest_withResourceId(Abp01_NonceProvider $provider, $urlParamName) {
		$faker = $this->_getFaker();
		$resourceId = $faker->randomNumber();
		$nonce = $provider->generateNonce($resourceId);
		$this->_storeNonceToRequestContext($urlParamName, $nonce);
		$this->assertTrue($provider->validateNonce($resourceId));
	}

	private function _storeNonceToRequestContext($urlParamName, $nonce) {
		$_REQUEST[$urlParamName] = $nonce;
	}

	public function test_canDetectInvalidNonce() {
		$providersInfos = $this->_getProvidersInfosForTesting();
		foreach ($providersInfos as $providerInfo) {
			$provider = $providerInfo['provider'];
			$urlParamName = $providerInfo['url_param_name'];
			$this->_runInvalidNonceVerificationTest_noResourceId($provider, $urlParamName);
			$this->_runInvalidNonceVerificationTest_withResourceId($provider, $urlParamName);
		}
	}

	private function _runInvalidNonceVerificationTest_noResourceId(Abp01_NonceProvider $provider, $urlParamName) {
		$nonce = $this->_generateNonEmptyAscii();
		$this->_storeNonceToRequestContext($urlParamName, $nonce);
		$this->assertFalse($provider->validateNonce());
	}

	private function _runInvalidNonceVerificationTest_withResourceId(Abp01_NonceProvider $provider, $urlParamName) {
		$faker = $this->_getFaker();
		$nonce = $this->_generateNonEmptyAscii();
		$resourceId = $faker->randomNumber();
		$this->_storeNonceToRequestContext($urlParamName, $nonce);
		$this->assertFalse($provider->validateNonce($resourceId));
	}

	public function test_canCheckIfHasNonceInCurrentContext_whenHasNonce() {
		$providersInfos = $this->_getProvidersInfosForTesting();
		foreach ($providersInfos as $providerInfo) {
			$provider = $providerInfo['provider'];
			$urlParamName = $providerInfo['url_param_name'];
			$this->_runNonceExistenceCheck_whenHasNonce($provider, $urlParamName);
		}
	}

	private function _runNonceExistenceCheck_whenHasNonce(Abp01_NonceProvider $provider, $urlParamName) {
		$nonce = $this->_generateNonEmptyAscii();
		$this->_storeNonceToRequestContext($urlParamName, $nonce);
		$this->assertTrue($provider->hasNonceInCurrentContext());
	}

	public function test_canCheckIfHasNonceInCurrentContext_whenHasEmptyNonce() {
		$providersInfos = $this->_getProvidersInfosForTesting();
		foreach ($providersInfos as $providerInfo) {
			$provider = $providerInfo['provider'];
			$urlParamName = $providerInfo['url_param_name'];
			$this->_runNonceExistenceCheck_whenHasEmptyNonce($provider, $urlParamName);
		}
	}

	private function _runNonceExistenceCheck_whenHasEmptyNonce(Abp01_NonceProvider $provider, $urlParamName) {
		$this->_storeNonceToRequestContext($urlParamName, '');
		$this->assertFalse($provider->hasNonceInCurrentContext());
		
		$this->_storeNonceToRequestContext($urlParamName, null);
		$this->assertFalse($provider->hasNonceInCurrentContext());
	}

	public function test_canCheckIfHasNonceInCurrentContext_whenNoNonce() {
		$providersInfos = $this->_getProvidersInfosForTesting();
		foreach ($providersInfos as $providerInfo) {
			$provider = $providerInfo['provider'];
			$urlParamName = $providerInfo['url_param_name'];
			$this->_runNonceExistenceCheck_whenNoNonce($provider, $urlParamName);
		}
	}

	private function _runNonceExistenceCheck_whenNoNonce(Abp01_NonceProvider $provider, $urlParamName) {
		$this->_ensureNoNonceInRequestContext($urlParamName);
		$this->assertFalse($provider->hasNonceInCurrentContext());
	}

	private function _ensureNoNonceInRequestContext($urlParamName) {
		if (isset($_REQUEST[$urlParamName])) {
			unset($_REQUEST[$urlParamName]);
		}
	}

	private function _getProvidersInfosForTesting() {
		return array(
			array(
				'provider' => new Abp01_NonceProvider_Default('sample_action_code_1', 'abp01_nonce'),
				'url_param_name' => 'abp01_nonce'
			),
			array(
				'provider' => new Abp01_NonceProvider_Default('sample_action_code_2', 'abp01_nonce_test'),
				'url_param_name' => 'abp01_nonce_test'
			),
			array(
				'provider' => new Abp01_NonceProvider_ReadTrackData(),
				'url_param_name' => 'abp01_nonce_get'
			),
			array(
				'provider' => new Abp01_NonceProvider_DownloadTrackData(),
				'url_param_name' => 'abp01_nonce_download'
			)
		);
	}
}