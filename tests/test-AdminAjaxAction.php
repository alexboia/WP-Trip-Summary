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

class AdminAjaxActionTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	private $_savedServerRequestMethod = null;

	protected function setUp(): void {
		parent::setUp();
		$this->_resetTestDoublesStates();
		$this->_storeCurrentRequestMethod();
	}

	private function _resetTestDoublesStates() {
		Abp01DieState::resetDieCall();
		Abp01SendHeaderState::clearSendHeaderCalls();
	}

	private function _storeCurrentRequestMethod() {
		$this->_savedServerRequestMethod = isset($_SERVER['REQUEST_METHOD'])
			? $_SERVER['REQUEST_METHOD']
			: null;
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_resetTestDoublesStates();
		$this->_restorePreviousRequestMethod();
	}

	private function _restorePreviousRequestMethod() {
		if ($this->_savedServerRequestMethod !== null) {
			$_SERVER['REQUEST_METHOD'] = $this->_savedServerRequestMethod;
		} else {
			unset($_SERVER['REQUEST_METHOD']);
		}
	}

	public function test_canBeRegistered_withoutAuthenticationRequired() {
		$this->_runAdminAjaxActionRegistrationTest(false);
	}

	private function _createAdminAjaxExecutorWithRandomResponse() {
		return $this->_createAdminAjaxActionExecutor($this->_constructExpectedAdminAjaxResponse());
	}

	private function _runAdminAjaxActionRegistrationTest($requiresAuthentication) {
		$executorCallback = $this->_createAdminAjaxExecutorWithRandomResponse();

		$ajaxAction = Abp01_AdminAjaxAction::create('actioncode1', $executorCallback)
			->setRequiresAuthentication($requiresAuthentication)
			->register();

		$expectedWpActionCallback = array($ajaxAction, 
			'executeAndSendJsonThenExit');

		$hasWpActionHook = has_action('wp_ajax_actioncode1', 
			$expectedWpActionCallback);

		$this->assertTrue(is_int($hasWpActionHook));

		$hasNoProviWpActionHook = has_action('wp_ajax_nopriv_actioncode1', 
			$expectedWpActionCallback);

		if (!$requiresAuthentication) {
			$this->assertTrue(is_int($hasNoProviWpActionHook));
		} else {
			$this->assertFalse($hasNoProviWpActionHook);
		}
	}

	public function test_canBeRegistered_withAuthenticationRequired() {
		$this->_runAdminAjaxActionRegistrationTest(true);
	}

	public function test_canExecute_defaultSetup() {
		$expectedResponse = $this->_constructExpectedAdminAjaxResponse();
		$executorCallback = $this->_createAdminAjaxActionExecutor($expectedResponse);

		$ajaxAction = Abp01_AdminAjaxAction::create('actioncode1', 
			$executorCallback);

		$this->_runAdminAjaxActionTest($ajaxAction, 
			true, 
			$expectedResponse);
	}

	private function _constructExpectedAdminAjaxResponse() {
		$faker = $this->_getFaker();

		$expectedResponse = abp01_get_ajax_response(array(
			'prop1' => $faker->sentence(),
			'prop2' => $faker->numberBetween()
		));

		return $expectedResponse;
	}

	private function _createAdminAjaxActionExecutor($expectedResponse) {
		return function() use ($expectedResponse) {
			return $expectedResponse;
		};
	}

	private function _runAdminAjaxActionTest(Abp01_AdminAjaxAction $ajaxAction, $shouldExecute, $expectedResponse = null) {
		$this->_resetTestDoublesStates();
		$expectedSerializedResponse = json_encode($expectedResponse);

		if ($shouldExecute) {
			$actualResponse = $ajaxAction->execute();
			$this->assertEquals($expectedResponse, $actualResponse);
			$this->assertFalse(Abp01DieState::hasDieBeenCalled());

			$this->_resetTestDoublesStates();
			$ajaxAction->executeAndSendJsonThenExit();
			$this->assertTrue(Abp01DieState::hasDieBeenCalledWithArgs($expectedSerializedResponse));
		} else {
			$actualResponse = $ajaxAction->execute();
			$this->assertEmpty($actualResponse);
			$this->assertTrue(Abp01DieState::hasDieBeenCalled());

			$this->_resetTestDoublesStates();
			$ajaxAction->executeAndSendJsonThenExit();
			$this->assertTrue(Abp01DieState::hasDieBeenCalledWithArgs());
		}
	}

	public function test_canExecute_defaultSetup_httpMethodRestriction() {
		$expectedResponse = $this->_constructExpectedAdminAjaxResponse();
		$executorCallback = $this->_createAdminAjaxActionExecutor($expectedResponse);

		$ajaxAction = Abp01_AdminAjaxAction::create('actioncode1', 
			$executorCallback);

		$this->_runAdminAjaxActionTestWithHttpMethodRestrictions($ajaxAction, 
			true,
			$expectedResponse);
	}

	private function _runAdminAjaxActionTestWithHttpMethodRestrictions(Abp01_AdminAjaxAction $ajaxAction, $shouldExecute, $expectedResponse = null) {
		foreach ($this->_getHttpMethods() as $methodAllowed) {
			$ajaxAction->onlyForHttpMethod($methodAllowed);

			$this->_setCurrentHttpMethod($methodAllowed);

			if ($shouldExecute) {
				$this->_runAdminAjaxActionTest($ajaxAction, 
					true, 
					$expectedResponse);
			} else {
				$this->_runAdminAjaxActionTest($ajaxAction, 
					false);
			}

			foreach ($this->_getHttpMethods($methodAllowed) as $notAllowedMethod) {
				$this->_setCurrentHttpMethod($notAllowedMethod);
				$this->_runAdminAjaxActionTest($ajaxAction, 
					false);
			}
		}
	}

	public function test_canExecute_withCallbackAuthorization_isAuthorized_allHttpMethods() {
		$this->_runAdminAjaxActionWithCallbackAuthorizationTest(true);
	}

	public function test_canExecute_withCallbackAuthorization_isNotAuthorized_allHttpMethods() {
		$this->_runAdminAjaxActionWithCallbackAuthorizationTest(false);
	}

	private function _runAdminAjaxActionWithCallbackAuthorizationTest($isAuthorized) {
		$expectedResponse = $this->_constructExpectedAdminAjaxResponse();
		$executorCallback = $this->_createAdminAjaxActionExecutor($expectedResponse);
		$authorizationCallback = $this->_createAuthorizationCallback($isAuthorized);

		$ajaxAction = Abp01_AdminAjaxAction::create('actioncode1', $executorCallback)
			->authorizeByCallback($authorizationCallback);

		if ($isAuthorized) {
			$this->_runAdminAjaxActionTest($ajaxAction, 
				true, 
				$expectedResponse);	
		} else {
			$this->_runAdminAjaxActionTest($ajaxAction, 
				false);
		}
	}

	public function test_canExecute_withCallbackAuthorization_isAuthorized_httpMethodRestriction() {
		$this->_runAdminAjaxActionWithCallbackAuthorizationTestWithHttpMethodRestrictions(true);
	}

	public function test_canExecute_withCallbackAuthorization_isNotAuthorized_httpMethodRestriction() {
		$this->_runAdminAjaxActionWithCallbackAuthorizationTestWithHttpMethodRestrictions(false);
	}

	private function _runAdminAjaxActionWithCallbackAuthorizationTestWithHttpMethodRestrictions($isAuthorized) {
		$expectedResponse = $this->_constructExpectedAdminAjaxResponse();
		$executorCallback = $this->_createAdminAjaxActionExecutor($expectedResponse);
		$authorizationCallback = $this->_createAuthorizationCallback($isAuthorized);

		$ajaxAction = Abp01_AdminAjaxAction::create('actioncode1', $executorCallback)
			->authorizeByCallback($authorizationCallback);

		if ($isAuthorized) {
			$this->_runAdminAjaxActionTestWithHttpMethodRestrictions($ajaxAction, 
				true, 
				$expectedResponse);
		} else {
			$this->_runAdminAjaxActionTestWithHttpMethodRestrictions($ajaxAction, 
				false);
		}
	}

	private function _createAuthorizationCallback($isAuthorized) {
		return function() use ($isAuthorized) {
			return $isAuthorized;
		};
	}

	private function _getHttpMethods($without = null) {
		$methods = array(
			Abp01_AdminAjaxAction::HTTP_DELETE,
			Abp01_AdminAjaxAction::HTTP_GET,
			Abp01_AdminAjaxAction::HTTP_PATCH,
			Abp01_AdminAjaxAction::HTTP_POST,
			Abp01_AdminAjaxAction::HTTP_PUT
		);

		if (!empty($without)) {
			$methods = array_filter($methods, function($element) use ($without) {
				return $element != $without;
			});
		}

		return $methods;
	}

	private function _setCurrentHttpMethod($method) {
		$_SERVER['REQUEST_METHOD'] = $method;
	}
}