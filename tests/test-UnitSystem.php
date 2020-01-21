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