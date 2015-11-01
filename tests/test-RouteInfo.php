<?php
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
				$value = $faker->numberBetween(0, PHP_INT_MAX);
				break;
			case 'string':
				$value = $faker->text();
				break;
		}

		return $multiple ? array($value) : $value;
	}

	private function _assertHasValue(Abp01_Route_Info $info, $key, $value) {
		$data = $info->getData();
		$this->assertTrue(array_key_exists($key, $data));
		$this->assertEquals($value, $data[$key]);
	}
}