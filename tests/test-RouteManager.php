<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

    private $_testRouteInfo = array();

    public function setUp() {
        parent::setUp();
        $this->_installTestData();
    }

    public function tearDown() {
        parent::tearDown();
        $this->_clearTestData();
    }

    /**
     * @dataProvider _getPerTypeDataSets
     */
    public function test_canSaveRouteInfo_nonExistingForPost($type, $data) {
        $routeManager = $this->_getRouteManager();

        $routeInfo = new Abp01_Route_Info($type);
        $routeInfo->setData($data);

        $postId = $this->_generatePostId();
        $currentUserId = $this->_generateCurrentUserId();

        $result = $routeManager->saveRouteInfo($postId, 
            $currentUserId, 
            $routeInfo);

        $this->assertTrue($result);

        $this->_assertRouteInfoDataMatchesDbRow($postId, 
            $currentUserId, 
            $routeInfo);
    }

    public function test_canSaveRouteInfo_existingForPost() {
        $routeManager = $this->_getRouteManager();

        foreach ($this->_testRouteInfo as $postId => $postRouteData) {
            $type = $postRouteData['type'];
            $data = $this->_getRandomDataSetForType($postRouteData['type']);

            $routeInfo = new Abp01_Route_Info($type);
            $routeInfo->setData($data);

            $currentUserId = $this->_generateCurrentUserId();

            $result = $routeManager->saveRouteInfo($postId, 
                $currentUserId, 
                $routeInfo);

            $this->assertTrue($result);

            $this->_assertRouteInfoDataMatchesDbRow($postId, 
                $currentUserId, 
                $routeInfo);
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

    private function _installTestData() {
        $this->_testRouteInfo = $this->_initRouteInfo();
    }

    private function _clearTestData() {
        $this->_clearAllRouteInfo();
        $this->_testRouteInfo = array();
    }

    private function _initRouteInfo() {
        $env = $this->_getEnv();
		$db = $this->_getDb();

        $testRouteInfo = array();
        $faker = $this->_getFaker();

		$table = $env->getRouteDetailsTableName();
		$lookupDetailsTableName = $env->getRouteDetailsLookupTableName();

        $db->startTransaction();

        for ($i = 0; $i < 3; $i ++) {
            $postId = $this->_generatePostId($testRouteInfo);
            $routeInfoData = $this->_getRandomDataSetWithType();
            $currentUserId = $this->_generateCurrentUserId();

            $type = $routeInfoData[0];
            $routeInfo = new Abp01_Route_Info($type);
            $routeInfo->setData($routeInfoData[1]);

            $testRouteInfo[$postId] = array(
                'type' => $type,
                'routeInfo' => $routeInfo,
                'currentUserId' => $currentUserId
            );

            $db->rawQuery('INSERT INTO `' . $table . '` (
                post_ID, 
                route_type, 
                route_data_serialized, 
                route_data_last_modified_at,
                route_data_last_modified_by
            ) VALUES (
                ?, ?, ?, CURRENT_TIMESTAMP, ?
            )', array(
                $postId,
                $type, 
                $routeInfo->toJson(),
                $currentUserId
            ));

            foreach ($routeInfo->getData() as $field => $value) {
                if (!$routeInfo->isLookupKey($field)) {
                    continue;
                }

                if (!is_array($value)) {
                    $value = array($value);
                }

                foreach ($value as $v) {
                    $db->rawQuery('INSERT INTO `' . $lookupDetailsTableName . '` (
                        post_ID, lookup_ID
                    ) VALUES (
                        ?, ?
                    )', array(
                        $postId, $v
                    ));
                }
            }
        }

        $db->commit();

        return $testRouteInfo;
    }

    private function _clearAllRouteInfo() {
        $env = $this->_getEnv();
		$db = $this->_getDb();

		$table = $env->getRouteDetailsTableName();
		$lookupDetailsTableName = $env->getRouteDetailsLookupTableName();

		$db->rawQuery('TRUNCATE TABLE `' . $lookupDetailsTableName . '`', null, 
            false);
            
        $db->rawQuery('TRUNCATE TABLE `' . $table . '`', null, 
			false);
    }

    private function _generatePostId($exclude = null) {
        if (empty($exclude) || !is_array($exclude)) {
            $exclude = $this->_testRouteInfo;
        }

        $faker = $this->_getFaker();
        
        $max = !empty($exclude) ? max(array_keys($exclude)) : 0;
        $postId = $faker->numberBetween($max + 1, $max + 1000);

        return $postId;
    }

    private function _generateCurrentUserId() {
        return $this->_getFaker()->numberBetween(1, PHP_INT_MAX);
    }
 }