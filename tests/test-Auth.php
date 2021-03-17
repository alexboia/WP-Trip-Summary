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

class AuthTests extends WP_UnitTestCase {
	use TestAuthDataHelpers;

	private $_roleKey;

	private $_initialRoleData;

	private $_testPosts = array();

	private $_testUsers = array();

	private $_testRoleData = array();
	public function setUp() {
		parent::setUp();
		$this->_testRoleData = $this->_getTestRoleData();
		$this->_storeBuiltInRoleData();
		$this->_setupTestData();
		$this->_initRolesForCurrentSite();
	}

	private function _storeBuiltInRoleData() {
		$this->_roleKey = wp_roles()->role_key;
		$this->_initialRoleData = get_option($this->_roleKey, array());
	}

	private function _setupTestData() {
		foreach ($this->_testRoleData as $roleName => $roleData) {
			$userId = $this->_createTestUserWithRole($roleName);
			if ($this->_capabilityExistsInRoleData('edit_posts', $roleData)) {
				$this->_testPosts[$userId] = $this->_createTestPostAuthoredByUser($userId);
			}

			$this->_testUsers[$roleName] = $userId;
		}

		update_option($this->_roleKey, $this->_testRoleData);
	}

	private function _createTestUserWithRole($roleName) {
		return self::factory()->user->create(array(
			'role' => $roleName
		));
	}

	private function _createTestPostAuthoredByUser($userId) {
		//Avoid this: https://core.trac.wordpress.org/ticket/44416 
		//  (compact() will throw notice for undefined variables in PHP 7.3)
		error_reporting(E_ALL & ~E_NOTICE);
		
		$postId = self::factory()->post->create(array(
			'ID' => 0,
			'post_type' => 'post',
			'post_author' => $userId
		));

		//Restore error reporting
		error_reporting(E_ALL);

		return $postId;
	}

	private function _capabilityExistsInRoleData($capCode, $roleData) {
		$roleCaps = $roleData['capabilities'];
		return isset($roleCaps[$capCode]) 
			&& $roleCaps[$capCode] === true;
	}

	private function _capabilitiesExistsInRoleData($capCodes, $roleData) {
		$exist = true;
		foreach ($capCodes as $capCode) {
			if (!$this->_capabilityExistsInRoleData($capCode, $roleData)) {
				$exist = false;
				break;
			}
		}
		return $exist;
	}

	private function _capabilityExistsInTestRole($capCode, $roleName) {
		return $this->_capabilityExistsInRoleData($capCode, $this->_testRoleData[$roleName]);
	}

	private function _capabilitiesExistInTestRole($capCodes, $roleName) {
		return $this->_capabilitiesExistsInRoleData($capCodes, $this->_testRoleData[$roleName]);
	}

	public function tearDown() {
		parent::tearDown();
		$this->_restoreBuiltInRolesData();
		$this->_clearTestData();
		$this->_initRolesForCurrentSite();
	}

	private function _restoreBuiltInRolesData() {
		update_option($this->_roleKey, $this->_initialRoleData);
	}

	private function _clearTestData() {
		$this->_testPosts = array();
		$this->_initialRoleData = array();
		$this->_testUsers = array();
	}

	private function _initRolesForCurrentSite() {
		wp_roles()->for_site();
	}

	public function test_canCheckIfCapCanBeInstalledForRole_ourCapabilities() {
		$auth = $this->_getAuth();

		foreach ($auth->getCapabilities() as $roleName => $capCodes) {
			foreach ($capCodes as $capCode) {
				$expectedAllowed = true;
				$requiredCaps = $auth->getRequiredCapabilities($capCode);
				
				if (!empty($requiredCaps)) {
					$expectedAllowed = $this->_capabilitiesExistInTestRole($requiredCaps, $roleName);
				}

				if ($expectedAllowed) {
					$this->assertTrue($auth->capCanBeInstalledForRole($capCode, $roleName));
				} else {
					$this->assertFalse($auth->capCanBeInstalledForRole($capCode, $roleName));
				}
			}
		}
	}

	public function test_tryCheckIfCapCanBeInstalledForRole_otherCapabilities() {
		$auth = $this->_getAuth();

		foreach ($this->_testRoleData as $roleName => $roleData) {
			$capabilities = $roleData['capabilities'];
			foreach ($capabilities as $capCode => $enabled) {
				$this->assertFalse($auth->capCanBeInstalledForRole($capCode, $roleName));
			}
		}
	}

	public function test_canInstallCapabilities() {
		$auth = $this->_getAuth();
		$auth->installCapabilities();

		foreach ($auth->getCapabilities() as $roleName => $capCodes) {
			$role = get_role($roleName);
			foreach ($capCodes as $capCode) {
				if ($auth->capCanBeInstalledForRole($capCode, $roleName)) {
					$this->assertTrue($role->has_cap($capCode));
				} else {
					$this->assertFalse($role->has_cap($capCode));
				}
			}
		}
	}

	public function test_canRemoveCapabilities() {
		$auth = $this->_getAuth();
		
		$auth->installCapabilities();
		$auth->removeCapabilities();

		foreach ($auth->getCapabilities() as $roleName => $capCodes) {
			$role = get_role($roleName);
			foreach ($capCodes as $capCode) {
				$this->assertFalse($role->has_cap($capCode));
			}
		}
	}

	public function test_canCheckIfCanManagePluginSettings_whenCapabilitiesInstalled() {
		$auth = $this->_getAuth();
		$auth->installCapabilities();

		foreach ($auth->getCapabilities() as $roleName => $capCodes) {
			$userId = $this->_testUsers[$roleName];

			$expectedCanManageTripSummary = $this->_shouldBeAbleToManageTripSummary($roleName, 
				$capCodes);

			$this->_assertCanCheckIfCanManagePluginSettings($auth, 
				$userId, 
				$expectedCanManageTripSummary);
		}
	}

	private function _shouldBeAbleToManageTripSummary($forRoleName, $withCapCodes) {
		return in_array(Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, $withCapCodes) 
			&& $this->_getAuth()->capCanBeInstalledForRole(Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, $forRoleName);
	}

	public function test_tryCheckIfCanManagePluginSettings_whenCapabilitiesNotInstalled() {
		$auth = $this->_getAuth();

		foreach ($auth->getCapabilities() as $roleName => $capCodes) {
			$userId = $this->_testUsers[$roleName];
			$this->_assertCanCheckIfCanManagePluginSettings($auth, 
				$userId, 
				false);
		}
	}

	public function test_canCheckIfCanEditTripSummary_whenCapabilitiesInstalled_ownPosts() {
		$auth = $this->_getAuth();
		$auth->installCapabilities();

		foreach ($this->_testUsers as $roleName => $userId) {
			if (isset($this->_testPosts[$userId])) {
				$postId = $this->_testPosts[$userId];
				$expectedCanEditTripSummary = $this->_shouldBeAbleToEditTripSummary('edit_posts', 
					$roleName);

				$this->_assertCanEditTripSummary($auth, 
					$userId, 
					$postId, 
					$expectedCanEditTripSummary);
			}
		}
	}

	private function _shouldBeAbleToEditTripSummary($withCapability, $forRoleName) {
		return $this->_capabilityExistsInTestRole($withCapability, $forRoleName)
			&& $this->_getAuth()->capCanBeInstalledForRole(Abp01_Auth::CAP_EDIT_TRIP_SUMMARY, $forRoleName);
	}

	public function test_canCheckIfCanEditTripSummary_whenCapabilitiesInstalled_othersPosts() {
		$auth = $this->_getAuth();
		$auth->installCapabilities();

		foreach ($this->_testUsers as $roleName => $userId) {
			if (isset($this->_testPosts[$userId])) {
				$ownPostId = $this->_testPosts[$userId];

				foreach ($this->_testPosts as $postId) {
					if ($postId != $ownPostId) {
						$expectedCanEditTripSummary = $this->_shouldBeAbleToEditTripSummary('edit_others_posts', 
							$roleName);

						$this->_assertCanEditTripSummary($auth, 
							$userId, 
							$postId, 
							$expectedCanEditTripSummary);
					}
				}
			}
		}
	}

	public function test_tryCheckIfCanEditTripSummary_whenCapabilitiesNotInstalled_ownPosts() {
		$auth = $this->_getAuth();

		foreach ($this->_testUsers as $roleName => $userId) {
			if (isset($this->_testPosts[$userId])) {
				$postId = $this->_testPosts[$userId];
				$this->_assertCanEditTripSummary($auth, 
					$userId, 
					$postId, 
					false);
			}
		}
	}

	public function test_tryCheckIfCanEditTripSummary_whenCapabilitiesNotInstalled_othersPosts() {
		$auth = $this->_getAuth();

		foreach ($this->_testUsers as $roleName => $userId) {
			if (isset($this->_testPosts[$userId])) {
				$ownPostId = $this->_testPosts[$userId];

				foreach ($this->_testPosts as $postId) {
					if ($postId != $ownPostId) {
						$this->_assertCanEditTripSummary($auth, 
							$userId, 
							$postId, 
							false);
					}
				}
			}
		}
	}

	private function _assertCanCheckIfCanManagePluginSettings($auth, $userId, $expectedCanManageTripSummary) {
		wp_set_current_user($userId);

		if ($expectedCanManageTripSummary) {
			$this->assertTrue($auth->canManagePluginSettings());
		} else {
			$this->assertFalse($auth->canManagePluginSettings());
		}
	}

	private function _assertCanEditTripSummary(Abp01_Auth $auth, $userId, $postId, $expectedCanEditTripSummary) {
		wp_set_current_user($userId);

		if ($expectedCanEditTripSummary) {
			$this->assertTrue($auth->canEditPostTripSummary($postId));
		} else {
			$this->assertFalse($auth->canEditPostTripSummary($postId));
		}
	}

	private function _getAuth() {
		return abp01_get_auth();
	}
}