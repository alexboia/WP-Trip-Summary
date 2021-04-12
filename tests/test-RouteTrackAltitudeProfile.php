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

class RouteTrackAltitudeProfileTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use RouteTrackAltitudeProfileTestDataHelpers;

	public function test_correctlyCreated() {
		$faker = $this->_getFaker();
		foreach ($this->_getSupportedUnitSystems() as $unitSystemSymbol) {
			$stepPoints = $faker->numberBetween(10, 20);
			$pointCount = $faker->numberBetween(100, 200);
			$unitSystem = Abp01_UnitSystem::create($unitSystemSymbol);
			$profilePoints = $this->_generateRandomAltitudeProfilePoints($pointCount);

			$profile = new Abp01_Route_Track_AltitudeProfile($profilePoints, 
				$unitSystem->getDistanceUnit(), 
				$unitSystem->getHeightUnit(), 
				$stepPoints);

			$this->assertEquals(count($profilePoints), 
				$profile->getProfilePointCount());
			$this->assertEquals($profilePoints, 
				$profile->getProfilePoints());
			$this->assertEquals($unitSystem->getHeightUnit(), 
				$profile->getHeightUnit());
			$this->assertEquals($unitSystem->getDistanceUnit(), 
				$profile->getDistanceUnit());
			$this->assertEquals($stepPoints, 
				$profile->getStepPoints());
		}
	}

	private function _getSupportedUnitSystems() {
		return array_keys(Abp01_UnitSystem::getAvailableUnitSystems());
	}

	public function test_canSerializeDeserialize() {
		foreach ($this->_getSupportedUnitSystems() as $unitSystemSymbol) {
			$unitSystem = Abp01_UnitSystem::create($unitSystemSymbol);
			$profile = $this->_generateAltitudeProfile($unitSystem);

			$serializedProfile = $profile->serializeDocument();
			$this->assertNotNull($serializedProfile);

			$deserializedProfile = Abp01_Route_Track_AltitudeProfile::fromSerializedDocument($serializedProfile);
			$this->assertNotNull($deserializedProfile);

			$this->assertEquals($profile->getProfilePointCount(), 
				$deserializedProfile->getProfilePointCount());
			$this->assertEquals($profile->getProfilePoints(), 
				$deserializedProfile->getProfilePoints());
			$this->assertEquals($profile->getHeightUnit(), 
				$deserializedProfile->getHeightUnit());
			$this->assertEquals($profile->getDistanceUnit(), 
				$deserializedProfile->getDistanceUnit());
			$this->assertEquals($profile->getStepPoints(), 
				$deserializedProfile->getStepPoints());
		}
	}

	private function _generateAltitudeProfile(Abp01_UnitSystem $unitSystem) {
		$faker = $this->_getFaker();

		$stepPoints = $faker->numberBetween(10, 20);
		$pointCount = $faker->numberBetween(100, 200);
		$profilePoints = $this->_generateRandomAltitudeProfilePoints($pointCount);

		$profile = new Abp01_Route_Track_AltitudeProfile($profilePoints, 
			$unitSystem->getDistanceUnit(), 
			$unitSystem->getHeightUnit(), 
			$stepPoints);

		return $profile;
	}

	public function test_canConvertToPlainObject() {
		foreach ($this->_getSupportedUnitSystems() as $unitSystemSymbol) {
			$unitSystem = Abp01_UnitSystem::create($unitSystemSymbol);
			$profile = $this->_generateAltitudeProfile($unitSystem);

			$asPlainObject = $profile->toPlainObject();

			$this->assertEquals($profile->getProfilePoints(), 
				$asPlainObject->profile);
			$this->assertEquals($profile->getHeightUnit(), 
				$asPlainObject->heightUnit);
			$this->assertEquals($profile->getDistanceUnit(), 
				$asPlainObject->distanceUnit);
		}
	}

	public function test_canCheckIfHasBeenGeneratedFor_sameContext() {
		foreach ($this->_getSupportedUnitSystems() as $unitSystemSymbol) {
			$unitSystem = Abp01_UnitSystem::create($unitSystemSymbol);
			$profile = $this->_generateAltitudeProfile($unitSystem);

			$this->assertTrue($profile->hasBeenGeneratedFor($unitSystem, 
				$profile->getStepPoints()));
		}
	}

	public function test_canCheckIfHasBeenGeneratedFor_differentContext() {
		$faker = $this->_getFaker();
		foreach ($this->_getSupportedUnitSystems() as $unitSystemSymbol) {
			$unitSystem = Abp01_UnitSystem::create($unitSystemSymbol);
			$profile = $this->_generateAltitudeProfile($unitSystem);

			$differentStepPoints = $faker->numberBetween($profile->getStepPoints() + 1, 
				$profile->getStepPoints() + 20);
			$this->assertFalse($profile->hasBeenGeneratedFor($unitSystem, 
				$differentStepPoints));

			foreach ($this->_getSupportedUnitSystems() as $differentUnitSystemSymbol) {
				if ($differentUnitSystemSymbol != $unitSystemSymbol) {
					$differentUnitSystem = Abp01_UnitSystem::create($differentUnitSystemSymbol);
					$this->assertFalse($profile->hasBeenGeneratedFor($differentUnitSystem, 
						$profile->getStepPoints()));
					$this->assertFalse($profile->hasBeenGeneratedFor($differentUnitSystem, 
						$differentStepPoints));
				}
			}
		}
	}
}