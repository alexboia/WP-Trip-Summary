<?php

use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

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

class RouteInfoTests extends WP_UnitTestCase {
	use ExpectException;
	use GenericTestHelpers;
	use RouteInfoTestDataSets;

	/**
	 * @dataProvider _getValidTypes
	 */
	public function test_canCreate_validType($type) {
		new Abp01_Route_Info($type);
	}

	/**
	 * @dataProvider _generateInvalidTypes
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryCreate_invalidType($type) {
		$this->expectException(InvalidArgumentException::class);
		new Abp01_Route_Info($type);
	}

	/**
	 * @dataProvider _getValidKeysDataSet
	 */
	public function test_canSet_validKey($type, $key, $value) {
		$info = new Abp01_Route_Info($type);
		$info->$key = $value;
		$this->_assertHasValue($info, $key, $value);
	}

	/**
	 * @dataProvider _generateInvalidFieldKeysDataSet
	 * @expectedException InvalidArgumentException
	 */
	public function test_canSet_invalidKey($type, $key, $value) {
		$this->expectException(InvalidArgumentException::class);
		$info = new Abp01_Route_Info($type);
		$info->$key = $value;
	}

	public function test_canCheckType() {
		$info = new Abp01_Route_Info(Abp01_Route_Info::BIKE);
		$this->assertTrue($info->isBikingTour());
		$this->assertFalse($info->isHikingTour());
		$this->assertFalse($info->isTrainRideTour());

		$info = new Abp01_Route_Info(Abp01_Route_Info::HIKING);
		$this->assertTrue($info->isHikingTour());
		$this->assertFalse($info->isBikingTour());
		$this->assertFalse($info->isTrainRideTour());

		$info = new Abp01_Route_Info(Abp01_Route_Info::TRAIN_RIDE);
		$this->assertFalse($info->isHikingTour());
		$this->assertFalse($info->isBikingTour());
		$this->assertTrue($info->isTrainRideTour());
	}

	/**
	 * @dataProvider _getValidTypes
	 */
	public function test_canGetType($type) {
		$info = new Abp01_Route_Info($type);
		$this->assertEquals($type, $info->getType());
	}

	/**
	 * @dataProvider _getValidTypes
	 */
	public function test_canSerializeToJson_empty($type) {
		$info = new Abp01_Route_Info($type);
		$this->assertEquals('[]', $info->toJson());
	}

	/**
	 * @dataProvider _getPerTypeRouteInfoDataSets
	 */
	public function test_canSerializeToJson($type, $data){
		$info = new Abp01_Route_Info($type);
		foreach ($data as $key => $value) {
			$info->$key = $value;
		}
		$this->assertEquals(json_encode($data), $info->toJson());
	}

	/**
	 * @dataProvider _getValidTypes
	 */
	public function test_canCreateFromJson_emptyJsonObject($type) {
		$info = Abp01_Route_Info::fromJson($type, '{}');
		$this->assertNotNull($info);

		$data = $info->getData();
		$this->assertEquals(0, count($data));
	}

	/**
	 * @dataProvider _getPerTypeRouteInfoDataSets
	 */
	public function test_canCreateFromJson($type, $data) {
		$json = json_encode($data);
		$info = Abp01_Route_Info::fromJson($type, $json);

		$this->assertNotNull($info);
		$this->_assertInfoHasData($info, $data);
	}

	/**
	 * @dataProvider _getValidTypes
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryCreateFromJson_emptyJsonInput($type) {
		$this->expectException(InvalidArgumentException::class);
		Abp01_Route_Info::fromJson($type, '');
	}

	/**
	 * @dataProvider _getPerTypeFields
	 */
	public function test_canGetLookupKey($type, $field, $descriptor) {
		$info = new Abp01_Route_Info($type);
		$expectedLookup = isset($descriptor['lookup']) ? $descriptor['lookup'] : null;

		$lookupKey = $info->getLookupKey($field);
		$this->assertEquals($expectedLookup, $lookupKey);
	}

	public function test_canGetAllLookupKeys() {
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$info = new Abp01_Route_Info($type);

			$actualLookupKeys = array();
			$allLookupKeys = $info->getAllLookupFields();
			
			foreach ($info->getValidFields() as $field => $descriptor) {
				if (isset($descriptor['lookup'])) {
					$actualLookupKeys[] = $field;
				}
			}

			$this->assertEquals($actualLookupKeys, $allLookupKeys);
		}
	}

	/**
	 * @dataProvider _getPerTypeRouteInfoDataSets
	 */
	public function test_canRemoveLookupValuesByLookupCategory($type, $data) {
		for ($i = 0; $i < 10; $i ++) {
			$info = new Abp01_Route_Info($type);
			foreach ($data as $key => $value) {
				$info->$key = $value;
			}

			$allFieldsInfo = $info->getValidFields();
			$lookupFieldNames = $info->getAllLookupFields();

			foreach ($lookupFieldNames as $fieldKey) {
				$fieldInfo = $allFieldsInfo[$fieldKey];		
				if (isset($fieldInfo['lookup'])) {
					$fieldValue = $info->$fieldKey;
					$isMultiple = isset($fieldInfo['multiple']) && $fieldInfo['multiple'] == true;

					$valueToRemove = $isMultiple 
						? $fieldValue[array_rand($fieldValue, 1)]
						: $fieldValue;

					$lookupKey = $fieldInfo['lookup'];
					$info->removeLookupValue($lookupKey, $valueToRemove);

					if (!empty($fieldValue)) {
						$newFieldValue = $info->$fieldKey;

						if ($isMultiple) {
							$diff = array_diff($fieldValue, $newFieldValue);
							$this->assertEquals(1, count($diff));
							$this->assertEquals($valueToRemove, $diff[0]);
						} else {
							$this->assertEmpty($newFieldValue);
						}
					}
				}
			}
		}
	}

	/**
	 * @dataProvider _getPerTypeRouteInfoDataSets
	 */
	public function test_canGetData($type, $data) {
		$info = new Abp01_Route_Info($type);
		foreach ($data as $field => $value) {
			$info->$field = $value;
		}

		$this->_assertInfoHasData($info, $data);
	}

	public function test_canStripTagsWhenSetting() {
		$info = new Abp01_Route_Info(Abp01_Route_Info::BIKE);
		$info->bikeAccess = '<script type="text/javascript">alert("Test")</script>';
		$this->assertEquals('', $info->bikeAccess);

		$info->bikeAccess = '<a href="test.html">Test</a>';
		$this->assertEquals('Test', $info->bikeAccess);

		$info->bikeAccess =  '<p class="article">Sample paragraph</p>';
		$this->assertEquals('Sample paragraph', $info->bikeAccess);
	}

	private function _assertInfoHasData(Abp01_Route_Info $info, $data) {
		$infoData = $info->getData();
		foreach ($data as $key => $value) {
			$this->assertTrue(array_key_exists($key, $infoData));
			$this->assertSame($value, $infoData[$key]);
		}
	}

	private function _assertHasValue(Abp01_Route_Info $info, $key, $value) {
		$data = $info->getData();
		$this->assertTrue(array_key_exists($key, $data));
		$this->assertEquals($value, $data[$key]);
	}
}