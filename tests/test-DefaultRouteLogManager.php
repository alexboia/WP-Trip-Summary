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

 class DefaultRouteLogManagerTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use DbTestHelpers;
	use RouteLogTestHelpers;

	const TEST_RECORD_COUNT = 100;

	/**
	 * @var array
	 */
	private $_testRouteLogEntryData = array();

	/**
	 * @var array
	 */
	private $_testPublicRouteLogEntryIds = array();

	/**
	 * @var array
	 */
	private $_testLastUsedVehicles = array();

	/**
	 * @var IntegerIdGenerator
	 */
	private $_idGenerator;

	public function __construct($name = null, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->_idGenerator = new IntegerIdGenerator();
	}

	protected function setUp(): void {
		parent::setUp();
		$this->_installTestData();
	}

	private function _installTestData() {
		$this->_clearAllRouteInfo();
		$this->_initRouteLogInfo();
	}

	private function _initRouteLogInfo() {
		$db = $this->_getDb();
		$routeLogTable = $this->_getEnv()->getRouteLogTableName();

		$db->startTransaction();

		for ($i = 0; $i < self::TEST_RECORD_COUNT; $i ++) {
			$postId = $this->_generatePostId();
			$currentUserId = $this->_generateCurrentUserId();

			$routeLogEntryData = $this->_generateRouteLogEntryData($postId, $currentUserId);
			$db->insert($routeLogTable, $routeLogEntryData);

			$logEntryId = $db->getInsertId();
			$routeLogEntryData['log_ID'] = $logEntryId;

			$this->_testRouteLogEntryData[$logEntryId] = $routeLogEntryData;
			if ($routeLogEntryData['log_is_public'] === 1) {
				if (!isset($this->_testPublicRouteLogEntryIds[$postId])) {
					$this->_testPublicRouteLogEntryIds[$postId] = array();
				}

				$this->_testPublicRouteLogEntryIds[$postId][] = $logEntryId;
			}

			$this->_testLastUsedVehicles[$postId] = 
				$routeLogEntryData['log_vehicle'];
		}

		$db->commit();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_clearTestData();
	}

	private function _clearTestData() {
		$this->_clearAllRouteInfo();
		$this->_testRouteLogEntryData = array();
	}

	private function _clearAllRouteInfo() {
		$db = $this->_getDb();
		$routeLogTable = $this->_getEnv()->getRouteLogTableName();
		$this->_truncateTables($db, $routeLogTable);
	}

	private function _getTestRouteLogEntryIds() {
		$logEntryIds = array_keys($this->_testRouteLogEntryData);
		return $logEntryIds;
	}

	private function _getTestRouteLogEntryPostIds() {
		$postIds = array_map(function($r){
			return intval($r['log_post_ID']);
		}, $this->_testRouteLogEntryData);

		return array_unique($postIds, 
			SORT_NUMERIC);
	}

	public function test_canGetLogEntryById_whenExists() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		foreach ($this->_testRouteLogEntryData as $logEntryId => $routeLogEntryData) {
			$postId = $routeLogEntryData['log_post_ID'];

			$expected = Abp01_Route_Log_Entry::fromDbArray($routeLogEntryData);
			$actual = $mgr->getLogEntryById($postId, $logEntryId);

			$this->assertNotNull($actual);
			$this->_assertLogEntriesMatch($expected, $actual);
		}
	}

	public function test_canGetLogEntryById_whenNotExists() {
		$faker = self::_getFaker();
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		
		$count = $faker->numberBetween(1, 10);
		$existingPostIds = $this->_getTestRouteLogEntryPostIds();
		$existingLogEntryIds = $this->_getTestRouteLogEntryIds();

		for ($i = 0; $i < $count; $i ++) {
			$postId = $this->_idGenerator->generateId($existingPostIds);
			$logEntryId = $this->_idGenerator->generateId($existingLogEntryIds);

			$logEntry = $mgr->getLogEntryById($postId, $logEntryId);
			$this->assertNull($logEntry);
		}
	}

	public function test_canGetLastUsedVehicle_forExistingData() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		foreach ($this->_testLastUsedVehicles as $postId => $vehicle) {
			$actualVehicle = $mgr->getLastUsedVehicle($postId);
			$this->assertEquals($vehicle, $actualVehicle);
		}
	}

	public function test_canGetLastUsedVehicle_afterAddNewLogEntry() {
		$postIds = $this->_getTestRouteLogEntryPostIds();
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();

		foreach ($postIds as $postId) {
			$routeLogEntryData = $this->_generateRouteLogEntryData($postId, null);
			$logEntry = Abp01_Route_Log_Entry::fromDbArray($routeLogEntryData);
			$expectedVehicle = $logEntry->vehicle;

			$mgr->saveLogEntry($logEntry);

			$actualVehicle = $mgr->getLastUsedVehicle($postId);
			$this->assertEquals($expectedVehicle, $actualVehicle);
		}
	}

	public function test_canAddNewLogEntry() {
		$faker = self::_getFaker();
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();

		$count = $faker->numberBetween(1, 10);
		$existingPostIds = $this->_getTestRouteLogEntryPostIds();

		for ($i = 0; $i < $count; $i ++) {
			$useExistingPostId = $faker->boolean();
			$postId = $useExistingPostId 
				? $faker->randomElement($existingPostIds)		
				: $this->_idGenerator->generateId($existingPostIds);

			$currentUserId = $this->_generateCurrentUserId();

			$routeLogEntryData = $this->_generateRouteLogEntryData($postId, $currentUserId);
			$logEntry = Abp01_Route_Log_Entry::fromDbArray($routeLogEntryData);

			$prevLogEntryCount = $this->_countLogEntriesForPost($postId);
			$mgr->saveLogEntry($logEntry);

			$this->assertGreaterThan(0, $logEntry->id);

			$newLogEntryCount = $this->_countLogEntriesForPost($postId);
			$this->assertEquals($prevLogEntryCount + 1, $newLogEntryCount);

			$readLogEntry = $mgr->getLogEntryById($postId, $logEntry->id);
			
			$this->assertNotNull($readLogEntry);
			$this->_assertLogEntriesMatch($logEntry, $readLogEntry);
		}
	}

	private function _countLogEntriesForPost($postId) {
		$db = $this->_getDb();
		$db->where('log_post_ID', $postId);
		return intval($db->getValue($this->_getEnv()->getRouteLogTableName(), 'COUNT(1)'));
	}

	public function test_canEditExistingLogEntry() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		foreach ($this->_testRouteLogEntryData as $logEntryId => $logEntryData) {
			$updatedByUserId = $this->_generateCurrentUserId();

			$logEntryData = $this->_updateRouteLogEntryData($logEntryData, $updatedByUserId);
			$logEntry = Abp01_Route_Log_Entry::fromDbArray($logEntryData);

			$prevLogEntryCount = $this->_countLogEntriesForPost($logEntry->postId);
			$mgr->saveLogEntry($logEntry);

			$this->assertEquals($logEntryId, $logEntry->id);

			$newLogEntryCount = $this->_countLogEntriesForPost($logEntry->postId);
			$this->assertEquals($prevLogEntryCount, $newLogEntryCount);

			$readLogEntry = $mgr->getLogEntryById($logEntry->postId, $logEntryId);

			$this->assertNotNull($readLogEntry);
			$this->_assertLogEntriesMatch($logEntry, $readLogEntry);
		}
	}

	public function test_canDeleteExistingLogEntry() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		foreach ($this->_testRouteLogEntryData as $logEntryId => $logEntryData) {
			$postId = $logEntryData['log_post_ID'];

			$prevLogEntryCount = $this->_countLogEntriesForPost($postId);
			$mgr->deleteLogEntry($postId, $logEntryId);

			$newLogEntryCount = $this->_countLogEntriesForPost($postId);
			$this->assertEquals($prevLogEntryCount - 1, $newLogEntryCount);

			$readLogEntry = $mgr->getLogEntryById($postId, $logEntryId);
			$this->assertNull($readLogEntry);
		}
	}

	public function test_canDeleteLog() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		foreach ($this->_testRouteLogEntryData as $logEntryId => $logEntryData) {
			$postId = $logEntryData['log_post_ID'];
			$mgr->deleteLog($postId);

			$newLogEntryCount = $this->_countLogEntriesForPost($postId);
			$this->assertEquals(0, $newLogEntryCount);

			$readLogEntry = $mgr->getLogEntryById($postId, $logEntryId);
			$this->assertNull($readLogEntry);
		}
	}

	public function test_canClearAllLogEntries() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		
		$mgr->clearAllLogEntries();
		$this->assertEquals(0, $this->_countAllLogEntries());

		foreach ($this->_testRouteLogEntryData as $logEntryId => $logEntryData) {
			$postId = $logEntryData['log_post_ID'];
			$readLogEntry = $mgr->getLogEntryById($postId, $logEntryId);
			$this->assertNull($readLogEntry);
		}
	}

	private function _countAllLogEntries() {
		$db = $this->_getDb();
		return intval($db->getValue($this->_getEnv()->getRouteLogTableName(), 'COUNT(1)'));
	}

	public function test_canGetPublicLog_whenExists() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();

		foreach ($this->_testPublicRouteLogEntryIds as $postId => $logEntryIds) {
			$log = $mgr->getPublicLog($postId);
			$this->assertNotNull($log);

			$this->assertEquals(count($logEntryIds), $log->getLogEntryCount());
			foreach ($log->getLogEntries() as $logEntry) {
				$this->assertContains($logEntry->id, $logEntryIds);
			}
		}
	}

	public function test_canGetPublicLog_whenDoesNotExist() {
		$faker = self::_getFaker();
		$excludePostIds = array_keys($this->_testPublicRouteLogEntryIds);
		$count = $faker->numberBetween(1, 10);
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();

		for ($i = 0; $i < $count; $i ++) {
			$postId = $this->_generatePostId($excludePostIds);
			$log = $mgr->getPublicLog($postId);
			$this->_assertLogNotNullButEmpty($log);
		}
	}

	private function _assertLogNotNullButEmpty(Abp01_Route_Log $log) {
		$this->assertNotNull($log);
		$this->assertEquals(0, $log->getLogEntryCount());
	}

	public function test_canGetAdminLog_whenExists() {
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();
		foreach ($this->_getTestRouteLogEntriesPerPostIds() as $postId => $logEntries) {
			$log = $mgr->getAdminLog($postId);

			$this->assertNotNull($log);
			$this->assertEquals(count($logEntries), $log->getLogEntryCount());

			foreach ($logEntries as $logEntryData) {
				$actualLogEntry = $this->_findFirst(
					$log->getLogEntries(), 
					function(Abp01_Route_Log_Entry $le) use ($logEntryData) {
						return $le->id == $logEntryData['log_ID'];
					}, 
					null);

				$this->assertNotNull($actualLogEntry);
				$this->_assertLogEntriesMatch(
					Abp01_Route_Log_Entry::fromDbArray($logEntryData), 
					$actualLogEntry
				);
			}
		}
	}

	private function _getTestRouteLogEntriesPerPostIds() {
		$entriesPerPosts = array();
		foreach ($this->_testRouteLogEntryData as $r) {
			$postId = $r['log_post_ID'];
			if (!isset($entriesPerPosts[$postId])) {
				$entriesPerPosts[$postId] = array();
			}

			$entriesPerPosts[$postId][$r['log_ID']] = $r;
		}
		return $entriesPerPosts;
	}

	public function test_canGetAdminLog_whenDoesNotExist() {
		$faker = self::_getFaker();
		$excludePostIds = $this->_getTestRouteLogEntryPostIds();
		$count = $faker->numberBetween(1, 10);
		$mgr = Abp01_Route_Log_Manager_Default::getInstance();

		for ($i = 0; $i < $count; $i ++) {
			$postId = $this->_generatePostId($excludePostIds);
			$log = $mgr->getAdminLog($postId);
			$this->_assertLogNotNullButEmpty($log);
		}
	}
 }