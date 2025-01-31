<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class SimpleAuthorizationProviderTests extends WP_UnitTestCase {
	use TestAuthDataHelpers;
	
	private $_testUsers = array();

	protected function setUp(): void {
		parent::setUp();
		$this->_initRolesForCurrentSite();
		$this->_creatTestUsersForTestRoles();
	}
	
	private function _initRolesForCurrentSite() {
		wp_roles()->for_site();
	}

	private function _creatTestUsersForTestRoles() {
		foreach ($this->_getTestRolesIds() as $roleId)  {
			$this->_testUsers[$roleId] = $this->_createTestUserWithRole($roleId);
		}
	}

	private function _getTestRolesIds() {
		return array_keys(wp_roles()->get_names());
	}

	private function _createTestUserWithRole($roleId) {
		return self::factory()->user->create(array(
			'role' => $roleId
		));
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_clearTestUsersForTestRoles();
	}

	private function _clearTestUsersForTestRoles() {
		$this->_testUsers = array();
	}

	public function test_canCheckIfAuthorized_whenHasCapability() {
		foreach ($this->_testUsers as $roleId => $userId) {
			$roleCapabilities = $this->_getBuiltInCapabilitiesInRole($roleId);
			$userCapabilities = $this->_getActualCapabilitiesForUser($userId, $roleCapabilities);
			$this->_runUserAuthorizationTest($userId, $userCapabilities, true);
		}
	}

	private function _runUserAuthorizationTest($userId, $capabilities, $expectedAuthorized) {
		wp_set_current_user($userId);
		foreach ($capabilities as $capability) {
			$this->_runCapabilityAuthorizationTest($capability, $expectedAuthorized);
		}
	}

	private function _runCapabilityAuthorizationTest($capability, $expectedAuthorized) {
		$provider = $this->_createAuthorizationProvider($capability);
		$this->assertEquals($expectedAuthorized, $provider->isAuthorizedToExecuteAction());
	}

	public function test_canCheckIfAuthorized_whenDoesntHaveCapability() {
		foreach ($this->_testUsers as $roleId => $userId) {
			$capabilities = $this->_getCapabilitiesNotInRole($roleId);
			$this->_runUserAuthorizationTest($userId, $capabilities, false);
		}
	}

	private function _getCapabilitiesNotInRole($roleId) {
		$roleCapabilities = $this->_getBuiltInCapabilitiesInRole($roleId);
		$allCapabilities = $this->_getAllAvailableBuiltInCapabilities();
		return array_diff($allCapabilities, $roleCapabilities);
	}

	private function _createAuthorizationProvider($capability) {
		return new Abp01_AdminAjaxAction_AuthorizationProvider_Simple($capability);
	}
}