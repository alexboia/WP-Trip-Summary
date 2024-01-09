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

class AuditLogProviderTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use DbTestHelpers;
	
	const TEST_RECORD_COUNT = 5;

	const TEST_EMPTY_ROUTE_INFO = '{}';

	const TEST_MIN_LAT = 0;

	const TEST_MIN_LNG = 0;

	const TEST_MAX_LAT = 90;

	const TEST_MAX_LNG = 180;

	const TEST_MIN_ALT = 0;

	const TEST_MAX_ALT = 2542;

	const TEST_GPX_FILE_NAME = 'sample.gpx';

	const TEST_GPX_MIME_TYPE = 'application/gpx+xml';

	/**
	 * @var IntegerIdGenerator
	 */
	private $_postIdGenerator;

	/**
	 * @var array
	 */
	private $_testAuditLogData = array();

	/**
	 * @var TestRouteDataProvider
	 */
	private $_testRouteDataProvider;
	
	public function __construct($name = null, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->_postIdGenerator = new IntegerIdGenerator();
		$this->_testRouteDataProvider = new TestRouteDataProvider();
	}

	protected function setUp(): void {
		parent::setUp();
		$this->_installTestData();
		self::commit_transaction();
	}

	private function _installTestData() {
		$this->_testAuditLogData = $this->_initAuditLogData();
	}

	private function _initAuditLogData() {
		$db = $this->_getDb();
		$testAuditLogData = array();

		$db->startTransaction();

		for ($i = 0; $i < self::TEST_RECORD_COUNT; $i ++) {
			$postId = $this->_generatePostId();
			
			$creationTimestamp = $this->_generateDbTimestamp();
			$lastModificationTimestamp = $this->_generateDbTimestamp();
			$lastModifiedByUser = $this->_generateLastModifiedByUser();

			$testAuditLogData[$postId] = array(
				'created_at' => $creationTimestamp,
				'last_modified_at' => $lastModificationTimestamp,
				'last_modified_by' => $lastModifiedByUser['user_id'],
				'last_modified_by_name' => $lastModifiedByUser['user_name']
			);

			$this->_generateAndSaveMockRouteInfoData($postId, 
				$lastModifiedByUser['user_id'], 
				$creationTimestamp,
				$lastModificationTimestamp);

			$this->_generateAndSaveMockRouteTrackData($postId, 
				$lastModifiedByUser['user_id'], 
				$creationTimestamp,
				$lastModificationTimestamp);
		}

		$db->commit();
		return $testAuditLogData;
	}

	private function _generateDbTimestamp() {
		$faker = $this->_getFaker();
		return $faker->dateTime->format('Y-m-d H:i:s');
	}

	private function _generateLastModifiedByUser() {
		$faker = $this->_getFaker();
		$userLogin = $faker->userName;

		/** @var WP_User $user */
		$userId = $this->factory()->user->create(array(
			'user_login' => $userLogin,
			'first_name' => $faker->firstName,
			'last_name' => $faker->lastName,
			'user_email' => $faker->email,
			'user_pass' => $faker->password
		));

		return array(
			'user_id' => $userId,
			'user_name' => $userLogin
		);
	}

	private function _generateAndSaveMockRouteInfoData($postId, 
		$lastModifiedBy, 
		$createdAt, 
		$lastModifiedAt) {

		$routeInfo = array(
			'post_id' => $postId,
			'route_type' => $this->_randomRouteInfoType(),
			'route_data_serialized' => self::TEST_EMPTY_ROUTE_INFO,
			'route_data_created_at' => $createdAt,
			'route_data_last_modified_at' => $lastModifiedAt,
			'route_data_last_modified_by' => $lastModifiedBy
		);

		$this->_testRouteDataProvider
			->saveRouteInfo($routeInfo);
	}

	private function _randomRouteInfoType() {
		return $this->_getFaker()
			->randomElement(Abp01_Route_Info::getSupportedTypes());
	}

	private function _generateAndSaveMockRouteTrackData($postId, 
		$lastModifiedBy, 
		$createdAt, 
		$lastModifiedAt) {

		$routeTrack = array(
			'post_id' => $postId,
			'route_file_name' => self::TEST_GPX_FILE_NAME,
			'route_file_mime_type' => self::TEST_GPX_MIME_TYPE,
			'route_min_x' => self::TEST_MIN_LNG,
			'route_min_y' => self::TEST_MIN_LAT,
			'route_max_x' => self::TEST_MAX_LNG,
			'route_max_y' => self::TEST_MAX_LAT,
			'route_min_alt' => self::TEST_MIN_LAT,
			'route_max_alt' => self::TEST_MIN_LNG,
			'route_track_created_at' => $createdAt,
			'route_track_modified_at' => $lastModifiedAt,
			'route_track_modified_by' => $lastModifiedBy
		);

		$this->_testRouteDataProvider
			->saveRouteTrack($routeTrack);
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_clearTestData();
	}

	public function _clearTestData() {
		$this->_clearAllRouteInfo();
	}

	private function _clearAllRouteInfo() {
		$this->_testRouteDataProvider
			->clearAll();
	}

	public function test_canGetAuditLogForPostId_existingPostId() {
		$provider = $this->_getProvider();
		foreach ($this->_testAuditLogData as $postId => $expectedData) {
			$auditData = $provider->getAuditLogForPostId($postId);

			$this->assertNotNull($auditData);

			$this->assertEquals($expectedData['created_at'], 
				$auditData->getInfoCreatedAt());
			$this->assertEquals($expectedData['last_modified_at'], 
				$auditData->getInfoLastModifiedAt());
			$this->assertEquals($expectedData['last_modified_by'], 
				$auditData->getInfoLastModifiedByUserId());
			$this->assertEquals($expectedData['last_modified_by_name'], 
				$auditData->getInfoLastModifiedByUserName());

			$this->assertEquals($expectedData['created_at'], 
				$auditData->getTrackCreatedAt());
			$this->assertEquals($expectedData['last_modified_at'], 
				$auditData->getTrackLastModifiedAt());
			$this->assertEquals($expectedData['last_modified_by'], 
				$auditData->getTrackLastModifiedByUserId());
			$this->assertEquals($expectedData['last_modified_by_name'], 
				$auditData->getTrackLastModifiedByUserName());
		}
	}

	private function _getProvider() {
		return new Abp01_AuditLog_Provider_Default($this->_getEnv());
	}

	protected function _generatePostId($excludeAdditionalIds = null) {
		if ($excludeAdditionalIds === null) {
			$excludeAdditionalIds = array();
		}

		return $this->_postIdGenerator
			->generateId($excludeAdditionalIds);
	}
}