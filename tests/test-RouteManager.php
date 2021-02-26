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
            $hasCachedTrackDocument = $i % 2 == 0;

            $routeInfo = $this->_createRouteInfoFromRouteInfoData($routeInfoData);

            $testPostRouteData[$postId] = array(
                'type' => $routeInfo->getType(),
                'routeInfo' => $routeInfo,
                'currentUserId' => $currentUserId,
                'track' => $track,
                'hasCachedTrackDocument' => $hasCachedTrackDocument
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

            $this->_generateAndSaveGpxDocument($postId, 
                $hasCachedTrackDocument);
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
            route_bbox,
            route_min_coord,
            route_max_coord,
            route_min_alt,
            route_max_alt,
            route_track_modified_at,
            route_track_modified_by
        ) VALUES (
            ?, ?, 
            ST_Envelope(LINESTRING(ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857), ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857))),
            ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857),
            ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857),
            ?, ?,
            CURRENT_TIMESTAMP,
            ?
        )', array(
            $postId, 
            $track->getFile(),
            $minCoord['mercX'], $minCoord['mercY'], $maxCoord['mercX'], $maxCoord['mercY'],
            $minCoord['mercX'], $minCoord['mercY'],
            $maxCoord['mercX'], $maxCoord['mercY'],
            $track->minAlt,
            $track->maxAlt,
            $currentUserId
        ));
    }

    private function _generateAndSaveGpxDocument($postId, $hasCachedTrackDocument) {
        $gpx = $this->_getFaker()
            ->gpx();

        $this->_storeGpxDocument($postId, $gpx['content']['text']);
        if ($hasCachedTrackDocument) {
            $this->_prepareAndStoreCachedTrackDocument($postId, $gpx['content']['text']);
        }
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
        $this->_removeTestGpxAndCachedTrackDocumentFiles();
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

    private function _removeTestGpxAndCachedTrackDocumentFiles() {
        $tracksDir = $this->_getEnv()->getTracksStorageDir();
        $this->_removeAllFiles($tracksDir, '*.gpx');

        $cacheDir = $this->_getEnv()->getCacheStorageDir();
        $this->_removeAllFiles($cacheDir, '*.cache');
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

        $this->_assertRouteInfoDataMatchesDbRow($postId, 
            $currentUserId, 
            $routeInfo);
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

            $this->_assertRouteInfoDataMatchesDbRow($postId, 
                $currentUserId, 
                $routeInfo);
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

    public function test_canGetOrCreateDisplayableTrackDocument_postWithTrackFiles() {
        $routeManager = $this->_getRouteManager();
        foreach ($this->_testPostRouteData as $postId => $testPostRouteData) {
            $trackDocument = $routeManager->getOrCreateDisplayableTrackDocument($testPostRouteData['track']);

            $this->assertNotEmpty($trackDocument);
            $this->assertNotEmpty($trackDocument->getBounds());
            $this->assertNotEmpty($trackDocument->getStartPoint());
            $this->assertNotEmpty($trackDocument->getEndPoint());
            $this->assertNotEmpty($trackDocument->parts);

            $this->assertFileExists($routeManager->getTrackFilePath($postId));
            $this->assertFileExists($routeManager->getTrackDocumentCacheFilePath($postId));

            $this->assertFileExists($this->_getGpxFilePath($postId));
            $this->assertFileExists($this->_getCachedTrackDocumentFilePath($postId));

            $this->_assertFileNotEmpty($routeManager->getTrackFilePath($postId));
            $this->_assertFileNotEmpty($routeManager->getTrackDocumentCacheFilePath($postId));
        }
    }

    public function test_tryGetOrCreateDisplayableTrackDocument_postWithTrackFiles() {
        $routeManager = $this->_getRouteManager();

        for ($i = 0; $i < 10; $i++) {
            $postId = $this->_generatePostId();
            $track = $this->_generateRandomRouteTrack($postId);
            $trackDocument = $routeManager->getOrCreateDisplayableTrackDocument($track);

            $this->assertEmpty($trackDocument);
            $this->_assertTrackFilesDoNotExist($routeManager, $postId);
        }
    }

    public function test_canDeleteTrackFiles_postWithTrackFiles() {
        $routeManager = $this->_getRouteManager();
        foreach ($this->_testPostRouteData as $postId => $testPostRouteData) {
            $routeManager->deleteTrackFiles($postId);
            $this->_assertTrackFilesDoNotExist($routeManager, $postId);
        }
    }

    public function test_canDeleteTrackFiles_postWithoutTrackFiles() {
        $routeManager = $this->_getRouteManager();

        for ($i = 0; $i < 10; $i++) {
            $postId = $this->_generatePostId();
            $routeManager->deleteTrackFiles($postId);
            $this->_assertTrackFilesDoNotExist($routeManager, $postId);            
        }
    }

    private function _assertRouteInfoDataMatchesDbRow($postId, 
        $currentUserId, 
        Abp01_Route_Info $routeInfo) {

        $env = $this->_getEnv();
        $db = $this->_getDb();
        $lookupKeys = $routeInfo->getAllLookupFields();

        $db->where('post_ID', $postId);
        $dbRouteData = $db->getOne($env->getRouteDetailsTableName());

        $db->where('post_ID', $postId);
        $dbRouteLookupData = $db->getValue($env->getRouteDetailsLookupTableName(), 
            'lookup_ID', 
            null);

        $this->assertNotEmpty($dbRouteData);
        $this->assertEquals($routeInfo->getType(), 
            $dbRouteData['route_type']);
        $this->assertEquals($currentUserId, 
            $dbRouteData['route_data_last_modified_by']);

        $dbRouteInfo = Abp01_Route_Info::fromJson($dbRouteData['route_type'], 
            $dbRouteData['route_data_serialized']);

        foreach ($dbRouteInfo->getData() as $key => $value) {
            $this->assertEquals($routeInfo->$key, $value);
        }

        $this->assertEquals(!empty($lookupKeys), 
            !empty($dbRouteLookupData));
        $this->assertEquals(count($lookupKeys), 
            count($dbRouteLookupData));

        foreach ($lookupKeys as $key) {
            $value = $routeInfo->$key;
            if (!is_array($value)) {
                $value = array($value);
            }
            
            foreach ($value as $v) {
                $this->assertTrue(in_array($v, $dbRouteLookupData));
            }
        }
    }

    private function _assertTrackFilesDoNotExist($routeManager, $postId) {
        $this->assertFileNotExists($routeManager->getTrackFilePath($postId));
        $this->assertFileNotExists($routeManager->getTrackDocumentCacheFilePath($postId));

        $this->assertFileNotExists($this->_getGpxFilePath($postId));
        $this->assertFileNotExists($this->_getCachedTrackDocumentFilePath($postId));
    }

    private function _asseryPostTripSummaryInfoMatchesIndividualChecks($postId, 
        $postTripSummaryInfo, 
        Abp01_Route_Manager_Default $routeManager) {
        $this->assertEquals($postTripSummaryInfo['has_route_details'], 
            $routeManager->hasRouteInfo($postId));
        $this->assertEquals($postTripSummaryInfo['has_route_track'], 
            $routeManager->hasRouteTrack($postId));
    }

    private function _assertRouteInfoInstancesMatch(Abp01_Route_Info $expected, 
        Abp01_Route_Info $actual) {

        $expectedData = $expected->getData();
        $actualData = $actual->getData();

        foreach ($expectedData as $eKey => $eValue) {
            $this->assertTrue(isset($actualData[$eKey]));
            
            $aValue = $actualData[$eKey];
            $this->assertEquals($eValue, $aValue);
        }

        $this->assertEquals($expected->getType(), 
            $actual->getType());

        $this->assertEquals($expected->isBikingTour(), 
            $actual->isBikingTour());
        $this->assertEquals($expected->isTrainRideTour(), 
            $actual->isTrainRideTour());
        $this->assertEquals($expected->isHikingTour(), 
            $actual->isHikingTour());
    }

    private function _assertMissingRouteInfo($postIds) {
        $env = $this->_getEnv();
        $db = $this->_getDb();

        $db->where('post_ID', $postIds, 'IN');
        $result = $db->getOne($env->getRouteDetailsTableName(), 'COUNT(*) as cnt');
        $this->assertEquals(0, $result['cnt']);

        $db->where('post_ID', $postIds, 'IN');
        $result = $db->getOne($env->getRouteDetailsLookupTableName(), 'COUNT(*) as cnt');
        $this->assertEquals(0, $result['cnt']);
    }

    private function _assertMissingRouteTracks($postIds) {
        $env = $this->_getEnv();
        $db = $this->_getDb();

        $db->where('post_ID', $postIds, 'IN');
        $result = $db->getOne($env->getRouteTrackTableName(), 'COUNT(*) as cnt');
        $this->assertEquals(0, $result['cnt']);
    }
 }