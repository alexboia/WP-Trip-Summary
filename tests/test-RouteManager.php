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

 class RouteManagerTests extends WP_UnitTestCase {
	use RouteInfoTestDataSets;
	use GenericTestHelpers;
	use RouteTrackTestDataHelpers;
	use DbTestHelpers;

	private $_testPostRouteData = array();

	/**
	 * @var IntegerIdGenerator
	 */
	private $_postIdGenerator;

	public function __construct($name = null, array $data = array(), $dataName = '') {
		$this->_postIdGenerator = new IntegerIdGenerator();
		parent::__construct($name, $data, $dataName);
	}

	public function setUp() {
		parent::setUp();
		$this->_installTestData();
	}

	private function _installTestData() {
		$this->_testPostRouteData = $this->_initRouteInfo();
	}

	private function _initRouteInfo() {
		$db = $this->_getDb();
		$testPostRouteData = array();

		$db->startTransaction();

		for ($i = 0; $i < 5; $i ++) {
			$postId = $this->_generatePostId();
			$routeInfoData = $this->_generateRandomRouteInfoWithType();
			$currentUserId = $this->_generateCurrentUserId();
			$track = $this->_generateRandomRouteTrack($postId);

			$routeInfo = $this->_createRouteInfoFromRouteInfoData($routeInfoData);

			$testPostRouteData[$postId] = array(
				'type' => $routeInfo->getType(),
				'routeInfo' => $routeInfo,
				'currentUserId' => $currentUserId,
				'track' => $track
			);

			$this->_generateAndSavePostData($postId);

			$this->_saveRouteInfo($postId, 
				$currentUserId, 
				$routeInfo);

			$this->_saveRouteInfoLookupAssociations($postId, 
				$routeInfo);

			$this->_saveRouteTrackData($postId, 
				$currentUserId, 
				$track);
		}

		$db->commit();

		return $testPostRouteData;
	}

	private function _createRouteInfoFromRouteInfoData($routeInfoData) {
		$type = $routeInfoData[0];
		$routeInfo = new Abp01_Route_Info($type);
		$routeInfo->setData($routeInfoData[1]);
		return $routeInfo;
	}

	private function _generateAndSavePostData($postId) {
		$db = $this->_getDb();
		$postsTableName = $this->_getEnv()
			->getWpPostsTableName();

		$db->insert($postsTableName, 
			$this->_generateWpPostData($postId));
	}

	private function _saveRouteInfo($postId, $currentUserId, Abp01_Route_Info $routeInfo) {
		$db = $this->_getDb();
		$routeDetailsTable = $this->_getEnv()
			->getRouteDetailsTableName();

		$db->rawQuery('INSERT INTO `' . $routeDetailsTable . '` (
			post_ID, 
			route_type, 
			route_data_serialized, 
			route_data_last_modified_at,
			route_data_last_modified_by
		) VALUES (
			?, ?, ?, CURRENT_TIMESTAMP, ?
		)', array(
			$postId,
			$routeInfo->getType(), 
			$routeInfo->toJson(),
			$currentUserId
		));
	}

	private function _saveRouteInfoLookupAssociations($postId, Abp01_Route_Info $routeInfo) {
		$db = $this->_getDb();
		$lookupDetailsTableName = $this->_getEnv()
			->getRouteDetailsLookupTableName();

		foreach ($routeInfo->getData() as $field => $value) {
			if (!$routeInfo->isLookupKey($field)) {
				continue;
			}

			if (!is_array($value)) {
				$value = array($value);
			}

			foreach ($value as $valueItem) {
				$db->rawQuery('INSERT INTO `' . $lookupDetailsTableName . '` (
					post_ID, lookup_ID
				) VALUES (
					?, ?
				)', array(
					$postId, $valueItem
				));
			}
		}
	}

	private function _saveRouteTrackData($postId, $currentUserId, Abp01_Route_Track $track) {
		$db = $this->_getDb();
		$proj = $this->_getProjSphericalMercator();
		$routeTrackTableName = $this->_getEnv()
			->getRouteTrackTableName();

		$bounds = $track->getBounds();
		$minCoord = $proj->forward($bounds->southWest->lat, 
			$bounds->southWest->lng);
		$maxCoord = $proj->forward($bounds->northEast->lat, 
			$bounds->northEast->lng);

		$db->rawQuery('INSERT INTO `' . $routeTrackTableName . '` (
			post_ID, 
			route_track_file, 
			route_track_file_mime_type,
			route_bbox,
			route_min_coord,
			route_max_coord,
			route_min_alt,
			route_max_alt,
			route_track_modified_at,
			route_track_modified_by
		) VALUES (
			?, ?, ?,
			ST_Envelope(LINESTRING(ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857), ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857))),
			ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857),
			ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857),
			?, ?,
			CURRENT_TIMESTAMP,
			?
		)', array(
			$postId, 
			$track->getFileName(),
			$track->getFileMimeType(),
			$minCoord['mercX'], $minCoord['mercY'], $maxCoord['mercX'], $maxCoord['mercY'],
			$minCoord['mercX'], $minCoord['mercY'],
			$maxCoord['mercX'], $maxCoord['mercY'],
			$track->minAlt,
			$track->maxAlt,
			$currentUserId
		));
	}

	protected function _generatePostId($excludeAdditionalIds = null) {
		if ($excludeAdditionalIds === null) {
			$excludeAdditionalIds = array();
		}

		return $this->_postIdGenerator
			->generateId($excludeAdditionalIds);
	}

	private function _generateCurrentUserId() {
		return self::_getFaker()->numberBetween(1, PHP_INT_MAX);
	}

	public function tearDown() {
		parent::tearDown();
		$this->_clearTestData();
	}

	private function _clearTestData() {
		$this->_clearAllRouteInfo();
		$this->_testPostRouteData = array();
	}

	private function _clearAllRouteInfo() {
		$env = $this->_getEnv();
		$db = $this->_getDb();

		$routeDetailsTableName = $env->getRouteDetailsTableName();
		$lookupDetailsTableName = $env->getRouteDetailsLookupTableName();
		$routeTrackTableName = $env->getRouteTrackTableName();
		$postsTableName = $env->getWpPostsTableName();

		$this->_truncateTables($db, 
			$lookupDetailsTableName, 
			$routeDetailsTableName, 
			$routeTrackTableName, 
			$postsTableName);
	}

	/**
	 * @dataProvider _getPerTypeRouteInfoDataSets
	 */
	public function test_canSaveRouteInfo_nonExistingForPost($type, $data) {
		$routeManager = $this->_getRouteManager();

		$routeInfo = new Abp01_Route_Info($type);
		$routeInfo->setData($data);

		$postId = $this->_generatePostId();
		$currentUserId = $this->_generateCurrentUserId();

		$result = $routeManager->saveRouteInfo($postId, 
			$routeInfo, 
			$currentUserId);

		$this->assertTrue($result);

		$this->_assertRouteInfoDataMatchesDbRow($routeInfo, 
			$postId,
			$currentUserId);
	}

	private function _assertRouteInfoDataMatchesDbRow(Abp01_Route_Info $expectedRouteInfo, $forPostId, $modifiedByUserId) {
		$dbRouteInfoData = $this->_readDbRouteInfoData($forPostId);

		$this->_assertRouteInfoMatchesDbRouteInfoData($expectedRouteInfo, 
			$dbRouteInfoData,
			$modifiedByUserId);

		$dbRouteInfoLookupData = $this->_readDbRouteInfoLookupData($forPostId);

		$this->_assertRouteInfoMatchesDbRouteInfoLookupData($expectedRouteInfo, 
			$dbRouteInfoLookupData);
	}

	private function _readDbRouteInfoData($forPostId) {
		$db = $this->_getDb();
		$db->where('post_ID', $forPostId);
		return $db->getOne($this->_getEnv()->getRouteDetailsTableName());
	}

	private function _readDbRouteInfoLookupData($forPostId) {
		$db = $this->_getDb();
		$db->where('post_ID', $forPostId);
		return $db->getValue($this->_getEnv()->getRouteDetailsLookupTableName(), 
			'lookup_ID', 
			null);
	}

	private function _assertRouteInfoMatchesDbRouteInfoData(Abp01_Route_Info $expectedRouteInfo, $dbRouteInfoData, $modifiedByUserId) {
		$this->assertNotEmpty($dbRouteInfoData);
		$this->assertEquals($expectedRouteInfo->getType(), 
			$dbRouteInfoData['route_type']);
		$this->assertEquals($modifiedByUserId, 
			$dbRouteInfoData['route_data_last_modified_by']);

		$dbRouteInfo = $this->_constructRouteInfoFromInfoData($dbRouteInfoData);

		$this->_assertRouteInfoInstancesDataMatches($expectedRouteInfo, 
			$dbRouteInfo);
	}

	private function _assertRouteInfoMatchesDbRouteInfoLookupData(Abp01_Route_Info $expectedRouteInfo, $dbRouteInfoLookupData) {
		$lookupKeys = $expectedRouteInfo->getAllLookupFields();

		$this->assertEquals(!empty($lookupKeys), 
			!empty($dbRouteInfoLookupData));
		$this->assertEquals(count($lookupKeys), 
			count($dbRouteInfoLookupData));

		foreach ($lookupKeys as $key) {
			$value = $expectedRouteInfo->$key;
			if (!is_array($value)) {
				$value = array($value);
			}
			
			foreach ($value as $v) {
				$this->assertTrue(in_array($v, $dbRouteInfoLookupData));
			}
		}
	}

	private function _constructRouteInfoFromInfoData($routeInfoData) {
		return Abp01_Route_Info::fromJson($routeInfoData['route_type'], 
			$routeInfoData['route_data_serialized']);
	}

	public function test_canSaveRouteInfo_existingForPost() {
		$routeManager = $this->_getRouteManager();

		foreach ($this->_testPostRouteData as $postId => $postRouteData) {
			$type = $postRouteData['type'];
			$data = $this->_generateRandomRouteInfoForType($postRouteData['type']);

			$routeInfo = new Abp01_Route_Info($type);
			$routeInfo->setData($data);

			$currentUserId = $this->_generateCurrentUserId();

			$result = $routeManager->saveRouteInfo($postId, 
				$routeInfo,
				$currentUserId);

			$this->assertTrue($result);

			$this->_assertRouteInfoDataMatchesDbRow($routeInfo, 
				$postId,
				$currentUserId);
		}
	}

	public function test_canRemoveRouteInfo() {
		$routeManager = $this->_getRouteManager();
		$postIds = array_keys($this->_testPostRouteData);

		foreach ($postIds as $postId) {
			$routeManager->deleteRouteInfo($postId);
		}

		$this->_assertMissingRouteInfo($postIds);

		foreach ($postIds as $postId) {
			$this->assertFalse($routeManager->hasRouteInfo($postId));
		}
	}

	public function test_canCheckIfHasRouteInfo_postsWithRouteInfo() {
		$routeManager = $this->_getRouteManager();
		$postIds = array_keys($this->_testPostRouteData);

		foreach ($postIds as $postId) {
			$this->assertTrue($routeManager->hasRouteInfo($postId));
		}
	}

	public function test_canCheckIfHasRouteInfo_postsWithoutRouteInfo() {
		$routeManager = $this->_getRouteManager();

		for ($i = 0; $i < 10; $i ++) {
			$postId = $this->_generatePostId();
			$this->assertFalse($routeManager->hasRouteInfo($postId));
		}
	}

	public function test_canGetRouteInfo_postsWithRouteInfo() {
		$routeManager = $this->_getRouteManager();

		foreach ($this->_testPostRouteData as $postId => $postRouteData) {
			$expected = $postRouteData['routeInfo'];
			$actual = $routeManager->getRouteInfo($postId);

			$this->assertNotNull($actual);
			$this->_assertRouteInfoInstancesMatch($expected, $actual);
		}
	}

	public function test_canGetRouteInfo_postsWithoutRouteInfo() {
		$routeManager = $this->_getRouteManager();

		for ($i = 0; $i < 10; $i ++) {
			$postId = $this->_generatePostId();
			$this->assertNull($routeManager->getRouteInfo($postId));
		}
	}

	/**
	 * @dataProvider _generateRandomRouteTracks
	 */
	public function test_canSaveRouteTrack_nonExistingForPost($track) {
		$routeManager = $this->_getRouteManager();
		$currentUserId = $this->_generateCurrentUserId();
		$postId = $track->getPostId();

		$result = $routeManager->saveRouteTrack($track, $currentUserId);
		$this->assertTrue($result);

		$retrievedTrack = $routeManager->getRouteTrack($postId);
		$this->assertNotNull($retrievedTrack);

		$this->assertTrue($track->equals($retrievedTrack));
	}

	public function test_canSaveRouteTrack_existingForPost() {
		$routeManager = $this->_getRouteManager();
		
		foreach ($this->_testPostRouteData as $postId => $postRouteData) {
			$newTrack = $this->_generateRandomRouteTrack($postId);
			$currentUserId = $this->_generateCurrentUserId();

			$result = $routeManager->saveRouteTrack($newTrack, $currentUserId);
			$this->assertTrue($result);

			$retrievedTrack = $routeManager->getRouteTrack($postId);
			$this->assertNotNull($retrievedTrack);

			$this->assertTrue($newTrack->equals($retrievedTrack));
		}
	}

	public function test_canGetRouteTrack_postsWithRouteTrack() {
		$routeManager = $this->_getRouteManager();

		foreach ($this->_testPostRouteData as $postId => $postRouteData)    {
			$track = $routeManager->getRouteTrack($postId);
			$this->assertNotNull($track);
			$this->assertTrue($track->equals($postRouteData['track']));
		}
	}

	public function test_canGetRouteTrack_postsWithoutRouteTrack() {
		$routeManager = $this->_getRouteManager();

		for ($i = 0; $i < 10; $i ++) {
			$postId = $this->_generatePostId();
			$this->assertNull($routeManager->getRouteTrack($postId));
		}
	}

	public function test_canRemoveRouteTrack() {
		$routeManager = $this->_getRouteManager();
		$postIds = array_keys($this->_testPostRouteData);

		foreach ($postIds as $postId) {
			$routeManager->deleteRouteTrack($postId);
		}

		$this->_assertMissingRouteTracks($postIds);

		foreach ($postIds as $postId) {
			$this->assertFalse($routeManager->hasRouteTrack($postId));
		}
	}

	public function test_canCheckIfHasRouteTrack_postsWithRouteTrack() {
		$routeManager = $this->_getRouteManager();
		$postIds = array_keys($this->_testPostRouteData);

		foreach ($postIds as $postId) {
			$this->assertTrue($routeManager->hasRouteTrack($postId));
		}
	}

	public function test_canCheckIfHasRouteTrack_postsWithoutRouteTrack() {
		$routeManager = $this->_getRouteManager();

		for ($i = 0; $i < 10; $i ++) {
			$postId = $this->_generatePostId();
			$this->assertFalse($routeManager->hasRouteTrack($postId));
		}
	}

	public function test_canCheckIfCanGetTripSummaryStatus_postsWithRouteInfoANDRouteTrack() {
		$routeManager = $this->_getRouteManager();
		$postIds = array_keys($this->_testPostRouteData);

		$tripSummaryInfo = $routeManager->getTripSummaryStatusInfo($postIds);

		$this->assertEquals(count($postIds), 
			count($tripSummaryInfo));

		foreach ($postIds as $postId) {
			$this->assertTrue(isset($tripSummaryInfo[$postId]));

			$postTripSummaryInfo = $tripSummaryInfo[$postId];

			$this->assertTrue($postTripSummaryInfo['has_route_details']);
			$this->assertTrue($postTripSummaryInfo['has_route_track']);

			$this->_asseryPostTripSummaryInfoMatchesIndividualChecks($postId, 
				$postTripSummaryInfo, 
				$routeManager);
		}
	}

	public function test_canCheckIfCanGetTripSummaryStatus_postsWithoutRouteInfoANDRouteTrack() {
		$postIds = array();
		$routeManager = $this->_getRouteManager();

		for ($i = 0; $i < 10; $i ++) {
			$postIds[] = $this->_generatePostId();
		}

		$this->_createWpPosts($postIds);
		$tripSummaryInfo = $routeManager->getTripSummaryStatusInfo($postIds);

		$this->assertEquals(count($postIds), 
			count($tripSummaryInfo));

		foreach ($postIds as $postId) {
			$this->assertTrue(isset($tripSummaryInfo[$postId]));

			$postTripSummaryInfo = $tripSummaryInfo[$postId];

			$this->assertFalse($postTripSummaryInfo['has_route_details']);
			$this->assertFalse($postTripSummaryInfo['has_route_track']);

			$this->_asseryPostTripSummaryInfoMatchesIndividualChecks($postId, 
				$postTripSummaryInfo, 
				$routeManager);
		}
	}

	private function _asseryPostTripSummaryInfoMatchesIndividualChecks($postId, 
		$postTripSummaryInfo, 
		Abp01_Route_Manager_Default $routeManager) {
		$this->assertEquals($postTripSummaryInfo['has_route_details'], 
			$routeManager->hasRouteInfo($postId));
		$this->assertEquals($postTripSummaryInfo['has_route_track'], 
			$routeManager->hasRouteTrack($postId));
	}

	private function _assertRouteInfoInstancesMatch(Abp01_Route_Info $expected, Abp01_Route_Info $actual) {      
		$this->_assertRouteInfoInstancesDataMatches($expected, 
			$actual);

		$this->assertEquals($expected->getType(), 
			$actual->getType());

		$this->assertEquals($expected->isBikingTour(), 
			$actual->isBikingTour());
		$this->assertEquals($expected->isTrainRideTour(), 
			$actual->isTrainRideTour());
		$this->assertEquals($expected->isHikingTour(), 
			$actual->isHikingTour());
	}

	private function _assertRouteInfoInstancesDataMatches(Abp01_Route_Info $expected, Abp01_Route_Info $actual) {
		$expectedData = $expected->getData();
		$actualData = $actual->getData();

		foreach ($expectedData as $eKey => $eValue) {
			$this->assertTrue(isset($actualData[$eKey]));
			
			$aValue = $actualData[$eKey];
			$this->assertEquals($eValue, $aValue);
		}
	}

	private function _assertMissingRouteInfo($postIds) {
		$this->assertEquals(0, $this->_countRouteDetailsRecordsForPostIds($postIds));
		$this->assertEquals(0, $this->_countRouteDetailsLookupRecordsForPostIds($postIds));
	}

	private function _countRouteDetailsRecordsForPostIds($postIds) {
		return $this->_countRecordsByColumnInValueList($this->_getDb(), 
			$this->_getEnv()->getRouteDetailsTableName(), 
			'post_ID', 
			$postIds);
	}

	private function _countRouteDetailsLookupRecordsForPostIds($postIds) {
		return $this->_countRecordsByColumnInValueList($this->_getDb(), 
			$this->_getEnv()->getRouteDetailsLookupTableName(), 
			'post_ID', 
			$postIds);
	}

	private function _assertMissingRouteTracks($postIds) {
		$this->assertEquals(0, $this->_countRouteTrackRecordsForPostIds($postIds));
	}

	private function _countRouteTrackRecordsForPostIds($postIds) {
		return $this->_countRecordsByColumnInValueList($this->_getDb(), 
			$this->_getEnv()->getRouteTrackTableName(), 
			'post_ID', 
			$postIds);
	}
 }