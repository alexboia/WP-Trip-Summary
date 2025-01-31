<?Php
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

class RouteTrackInfoTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canCreate() {
		foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $unitSystemSymbol => $label) {
			$this->_runTrackInfoCreationTests($unitSystemSymbol);
		}
	}

	private function _runTrackInfoCreationTests($unitSystemSymbol) {
		$faker = $this->_getFaker();

		$minAltitude = $faker->numberBetween(0, 1000);
		$maxAltitude = $minAltitude + $faker->numberBetween(0, 1000);

		$minAltitudeObj = new Abp01_UnitSystem_Value_Height($minAltitude, $unitSystemSymbol);
		$maxAltitudeObj = new Abp01_UnitSystem_Value_Height($maxAltitude, $unitSystemSymbol);

		$info = new Abp01_Route_Track_Info($minAltitudeObj, 
			$maxAltitudeObj);

		$this->assertEquals($minAltitudeObj, $info->getMinAltitude());
		$this->assertEquals($maxAltitudeObj, $info->getMaxAltitude());
	}

	public function test_canConvertToPlainObject() {
		foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $unitSystemSymbol => $label) {
			$this->_runConversionToPlainObjectTests($unitSystemSymbol);
		}
	}

	private function _runConversionToPlainObjectTests($unitSystemSymbol) {
		$info = $this->_generateRandomRouteTrackInfo($unitSystemSymbol);

		$infoObj = $info->toPlainObject();
		$this->assertNotNull($infoObj);

		$this->assertNotNull($infoObj->minAltitude);
		$this->assertNotNull($infoObj->maxAltitude);

		$this->_assertAltitudeMatchesPlainObject($info->getMinAltitude(), 
			$infoObj->minAltitude);
		$this->_assertAltitudeMatchesPlainObject($info->getMaxAltitude(), 
			$infoObj->maxAltitude);
	}

	private function _generateRandomRouteTrackInfo($unitSystemSymbol) {
		$faker = $this->_getFaker();

		$minAltitude = $faker->numberBetween(0, 1000);
		$maxAltitude = $minAltitude + $faker->numberBetween(0, 1000);

		$minAltitudeObj = new Abp01_UnitSystem_Value_Height($minAltitude, $unitSystemSymbol);
		$maxAltitudeObj = new Abp01_UnitSystem_Value_Height($maxAltitude, $unitSystemSymbol);

		$info = new Abp01_Route_Track_Info($minAltitudeObj, 
			$maxAltitudeObj);

		return $info;
	}

	private function _assertAltitudeMatchesPlainObject(Abp01_UnitSystem_Value_Height $altitude, 
		stdClass $altitudeObj) {
		$this->assertEquals($altitude->getValue(), 
			$altitudeObj->value);
		$this->assertEquals($altitude->getUnitSystem()->getHeightUnit(), 
			$altitudeObj->unit);
	}
}