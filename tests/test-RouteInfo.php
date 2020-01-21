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

class RouteInfoTests extends WP_UnitTestCase {
	/**
	 * @dataProvider _getValidTypes
	 */
	public function testCanCreate_validType($type) {
		new Abp01_Route_Info($type);
	}

	/**
	 * @dataProvider _getInvalidTypes
	 * @expectedException InvalidArgumentException
	 */
	public function testTryCreate_invalidType($type) {
		new Abp01_Route_Info($type);
	}

	/**
	 * @dataProvider _getValidKeysDataSet
	 */
	public function testCanSet_validKey($type, $key, $value) {
		$info = new Abp01_Route_Info($type);
		$info->$key = $value;
		$this->_assertHasValue($info, $key, $value);
	}

	/**
	 * @dataProvider _getInvalidKeysDataSet
	 * @expectedException InvalidArgumentException
	 */
	public function testCanSet_invalidKey($type, $key, $value) {
		$info = new Abp01_Route_Info($type);
		$info->$key = $value;
	}

	public function testCanCheckType() {
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
	public function testCanGetType($type) {
		$info = new Abp01_Route_Info($type);
		$this->assertEquals($type, $info->getType());
	}

	/**
	 * @dataProvider _getValidTypes
	 */
	public function testCanSerializeToJson_empty($type) {
		$info = new Abp01_Route_Info($type);
		$this->assertEquals('[]', $info->toJson());
	}

	/**
	 * @dataProvider _getPerTypeDataSets
	 */
	public function testCanSerializeToJson($type, $data){
		$info = new Abp01_Route_Info($type);
		foreach ($data as $key => $value) {
			$info->$key = $value;
		}
		$this->assertEquals(json_encode($data), $info->toJson());
	}

	/**
	 * @dataProvider _getValidTypes
	 */
	public function testCanCreateFromJson_emptyJsonObject($type) {
		$info = Abp01_Route_Info::fromJson($type, '{}');
		$this->assertNotNull($info);

		$data = $info->getData();
		$this->assertEquals(0, count($data));
	}

	/**
	 * @dataProvider _getPerTypeDataSets
	 */
	public function testCanCreateFromJson($type, $data) {
		$json = json_encode($data);
		$info = Abp01_Route_Info::fromJson($type, $json);

		$this->assertNotNull($info);
		$this->_assertInfoHasData($info, $data);
	}

	/**
	 * @dataProvider _getValidTypes
	 * @expectedException InvalidArgumentException
	 */
	public function testTryCreateFromJson_emptyJsonInput($type) {
		Abp01_Route_Info::fromJson($type, '');
	}

	/**
	 * @dataProvider _getPerTypeFields
	 */
	public function testCanGetLookupKey($type, $field, $descriptor) {
		$info = new Abp01_Route_Info($type);
		$expectedLookup = isset($descriptor['lookup']) ? $descriptor['lookup'] : null;

		$lookupKey = $info->getLookupKey($field);
		$this->assertEquals($expectedLookup, $lookupKey);
	}

	/**
	 * @dataProvider _getPerTypeDataSets
	 */
	public function testCanGetData($type, $data) {
		$info = new Abp01_Route_Info($type);
		foreach ($data as $field => $value) {
			$info->$field = $value;
		}

		$this->_assertInfoHasData($info, $data);
	}

	public function testCanStripTagsWhenSetting() {
		$info = new Abp01_Route_Info(Abp01_Route_Info::BIKE);
		$info->bikeAccess = '<script type="text/javascript">alert("Test")</script>';
		$this->assertEquals('', $info->bikeAccess);

		$info->bikeAccess = '<a href="test.html">Test</a>';
		$this->assertEquals('Test', $info->bikeAccess);

		$info->bikeAccess =  '<p class="article">Sample paragraph</p>';
		$this->assertEquals('Sample paragraph', $info->bikeAccess);
	}

	public function _getPerTypeFields() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$info = new Abp01_Route_Info($type);
			foreach ($info->getValidFields() as $field => $descriptor) {
				$data[] = array($type, $field, $descriptor);
			}
		}
		return $data;
	}

	public function _getPerTypeDataSets() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$values = array();
			$info = new Abp01_Route_Info($type);
			foreach ($info->getValidFields() as $name => $descriptor) {
				$values[$name] = $this->_generateValue($descriptor);
			}
			$data[] = array(
				$type,
				$values
			);
		}
		return $data;
	}

	public function _getValidKeysDataSet() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$route = new Abp01_Route_Info($type);
			$fields = $route->getValidFields();
			foreach ($fields as $name => $descriptor) {
				$data[] = array(
					$type,
					$name,
					$this->_generateValue($descriptor)
				);
			}
		}
		return $data;
	}

	public function _getInvalidKeysDataSet() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$info = new Abp01_Route_Info($type);
			$data[] = array(
				$type, 
				$this->_generateWord($info->getValidFieldNames()),
				$this->_generateValue(null)
			);
		}
		return $data;
	}

	public function _getInvalidTypes() {
		$count = 5;
		$data = array();
		$types = Abp01_Route_Info::getSupportedTypes();
		while ($count > 0) {
			$data[] = array($this->_generateWord($types));
			$count --;
		}
		return $data;
	}

	public function _getValidTypes() {
		$data = array();
		$types = Abp01_Route_Info::getSupportedTypes();
		foreach ($types as $type) {
			$data[] = array($type);
		}
		return $data;
	}

	private function _generateWord($excluded) {
		$faker = Faker\Factory::create();
		$word = $faker->word;
		while (in_array($word, $excluded)) {
			$word = $faker->word;
		}
		return $word;
	}

	private function _generateValue($fieldDescriptor) {
		$faker = Faker\Factory::create();
		if (!$fieldDescriptor) {
			$fieldDescriptor = array(
				'type' => $faker->randomElement(array('int', 'float', 'string')),
				'multiple' => $faker->randomElement(array(true, false))
			);
		}
		
		$type = $fieldDescriptor['type'];
		$multiple = isset($fieldDescriptor['multiple']) ? $fieldDescriptor['multiple'] : false;
		$value = null;

		switch ($type) {
			case 'int':
				$value = $faker->numberBetween(0, PHP_INT_MAX);
				break;
			case 'float':
				$value = $faker->randomFloat(2, 0, null);
				break;
			case 'string':
				$value = $faker->word;
				break;
		}

		return $multiple ? array($value) : $value;
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