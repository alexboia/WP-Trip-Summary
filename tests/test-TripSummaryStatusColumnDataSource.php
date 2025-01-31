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

class TripSummaryStatusColumnDataSourceTests extends WP_UnitTestCase {
    use GenericTestHelpers;

    private $_oldWpQuery = null;

    /**
     * @var IntegerIdGenerator
     */
    private $_postIdGenerator;

    public function __construct($name = null, array $data = array(), $dataName = '') {
        $this->_postIdGenerator = new IntegerIdGenerator();
        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->_storeCurrentWpQuery();
    }

    private function _storeCurrentWpQuery() {
        if (isset($GLOBALS['wp_query'])) {
            $this->_oldWpQuery = $GLOBALS['wp_query'];
        } else {
            $this->_oldWpQuery = null;
        }
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->_restorePreviousWpQuery();
		Mockery::close();
    }

    private function _restorePreviousWpQuery() {
        $GLOBALS['wp_query'] = $this->_oldWpQuery;
        $this->_oldWpQuery = null;
    }

    public function test_canGetValueForPostId_whenPostIdHasValue() {
        $postIds = $this->_generatePostIds(10);
        $allStatusInfo = $this->_generateTripSummaryStatusInfoDataForPostIds($postIds);
        
        $this->_mockGlobalWpQueryForPostIdsWithRandomData($postIds);

        $mock = $this->_createRouteManagerMockWithStatusInfoForPostIds($postIds, 
            $allStatusInfo);

        $this->_runDataSourceGetExistingValueTestsForDataKey($mock, 
            $allStatusInfo, 
            'has_route_details');
        $this->_runDataSourceGetExistingValueTestsForDataKey($mock, 
            $allStatusInfo, 
            'has_route_track');
    }

    private function _mockGlobalWpQueryForPostIdsWithRandomData($postIds) {
        $query = new WP_Query();
        $query->query_vars = array();
        $query->posts = array();
        
        foreach ($postIds as $postId) {
            $postData = $this->_generateWpPostData($postId, OBJECT);
            $post = new WP_Post($postData);
            $query->posts[] = $post;
        }

        $query->post_count = count($postIds);
        $query->found_posts = $query->post_count;

        $GLOBALS['wp_query'] = $query;
    }

    /**
     * @return \Mockery\MockInterface|\Mockery\LegacyMockInterface|\Abp01_Route_Manager
     */
    private function _createRouteManagerMockWithStatusInfoForPostIds($postIds, $allStatusInfo) {
        $mock = Mockery::mock('Abp01_Route_Manager');
        $mock = $mock->shouldReceive('getTripSummaryStatusInfo')
            ->with($postIds)
            ->andReturn($allStatusInfo)
            ->getMock();

        return $mock;
    }

    private function _generatePostIds($count) {
        $postIds = array();
        
        for ($i = 0; $i < $count; $i ++) {
            $postIds[] = $this->_postIdGenerator->generateId();
        }

        return $postIds;
    }

    private function _generateTripSummaryStatusInfoDataForPostIds($postIds) {
        $faker = $this->_getFaker();
        $tripSummaryStatusInfoData = array();

        foreach ($postIds as $postId) {
            $tripSummaryStatusInfoData[$postId] = array(
                'has_route_details' => $faker->boolean(),
                'has_route_track' => $faker->boolean()
            );
        }

        return $tripSummaryStatusInfoData;
    }
    
    private function _runDataSourceGetExistingValueTestsForDataKey(Abp01_Route_Manager $routeManagerMock, $allStatusInfo, $dataKey) {
        $dataSource = new Abp01_Display_PostListing_TripSummaryStatusColumnDataSource($routeManagerMock, 
            $dataKey);

        foreach ($allStatusInfo as $postId => $postStatusInfo) {
            $actualPostStatusValue = $dataSource
                ->getValue($postId);

            $this->assertEquals($postStatusInfo[$dataKey], 
                $actualPostStatusValue);
        }
    }

    public function test_tryGetValueForPostId_whenPostIdDoesNotHaveValue() {
        $postIds = $this->_generatePostIds(10);
        $postIdsWithStatusInfo = $this->_generatePostIds(10);
        $allStatusInfo = $this->_generateTripSummaryStatusInfoDataForPostIds($postIdsWithStatusInfo);
        
        $this->_mockGlobalWpQueryForPostIdsWithRandomData($postIdsWithStatusInfo);

        $mock = $this->_createRouteManagerMockWithStatusInfoForPostIds($postIdsWithStatusInfo, 
            $allStatusInfo);

        $this->_runDataSourceGetNonExistingValueTestsForDataKey($mock, 
            $postIds, 
            'has_route_details');
        $this->_runDataSourceGetNonExistingValueTestsForDataKey($mock, 
            $postIds, 
            'has_route_track');
    }

    private function _runDataSourceGetNonExistingValueTestsForDataKey(Abp01_Route_Manager $routeManagerMock, $postIds, $dataKey) {
        $dataSource = new Abp01_Display_PostListing_TripSummaryStatusColumnDataSource($routeManagerMock, 
            $dataKey);

        foreach ($postIds as $postId) {
            $actualPostStatusValue = $dataSource
                ->getValue($postId);

            $this->assertFalse($actualPostStatusValue);
        }
    }
}