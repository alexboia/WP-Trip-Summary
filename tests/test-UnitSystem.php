<?php
class UnitSystemTest extends WP_UnitTestCase {
	public function test_unitSystem_canCreateUnitSystem_existing() {
		$imperial = Abp01_UnitSystem::create(Abp01_UnitSystem::IMPERIAL);
		$this->_assertUnitSystemCorrect($imperial, 'Abp01_UnitSystem_Imperial');
		
		$metric = Abp01_UnitSystem::create(Abp01_UnitSystem::METRIC);
		$this->_assertUnitSystemCorrect($metric, 'Abp01_UnitSystem_Metric');
	}
	
	public function test_unitSystem_tryCreateUnitSystem_nonExistingOrEmpty() {
		$bogusSystem = Abp01_UnitSystem::create('bogus');
		$this->assertNull($bogusSystem);
		
		$empty = array(null, '');
		foreach ($empty as $eArg) {
			$emptySystem = Abp01_UnitSystem::create($eArg);
			$this->assertNull($emptySystem);
		}
	}
	
	public function test_unitSystem_canCheckIfSupported() {
		$supported = array(Abp01_UnitSystem::IMPERIAL, Abp01_UnitSystem::METRIC);
		foreach ($supported as $sArg) {
			$this->assertTrue(Abp01_UnitSystem::isSupported($sArg));
		}
		
		$unsupported = array('bogus', null, '');
		foreach ($unsupported as $sArg) {
			$this->assertFalse(Abp01_UnitSystem::isSupported($sArg));
		}
	}
	
	public function test_metricSystem_canYieldCorrectSymbols() {
		$metric = new Abp01_UnitSystem_Metric();
		$this->assertEquals('km', $metric->getDistanceUnit());
		$this->assertEquals('mm', $metric->getLengthUnit());
		$this->assertEquals('m', $metric->getHeightUnit());
	}
	
	public function test_imperialSystem_canYieldCorrectSymbols() {
		$imperial = new Abp01_UnitSystem_Imperial();
		$this->assertEquals('mi', $imperial->getDistanceUnit());
		$this->assertEquals('in', $imperial->getLengthUnit());
		$this->assertEquals('ft', $imperial->getHeightUnit());
	}
	
	private function _assertUnitSystemCorrect($unitSystem, $expectedClass) {
		$this->assertNotNull($unitSystem);
		$this->assertEquals($expectedClass, get_class($unitSystem));
	}
}