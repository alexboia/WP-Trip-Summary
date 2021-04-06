<?Php
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

class RouteTrackTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use RouteTrackBboxTestDataHelpers;

	public function test_canConstruct_withoutAltitudeInformation() {
		$this->_runTrackCreationTests(false);
	}

	private function _runTrackCreationTests($withAltitudeInformation) {
		$faker = $this->_getFaker();

		$postId = $faker->numberBetween();
		$bbox = $this->_generateRandomRouteTrackBoundingBox();
		$mimeType = $faker->mimeType;
		$fileName = $this->_randomFileName();
		
		if ($withAltitudeInformation) {
			$minimumAltitude = $faker->numberBetween(0, 1000);
			$maximumAltitude = $minimumAltitude + $faker->numberBetween(0, 1000);

			$track = new Abp01_Route_Track($postId, 
				$fileName, 
				$mimeType, 
				$bbox,
				$minimumAltitude,
				$maximumAltitude);
		} else {
			$minimumAltitude = 0;
			$maximumAltitude = 0;

			$track = new Abp01_Route_Track($postId, 
				$fileName, 
				$mimeType, 
				$bbox);
		}

		$this->assertEquals($postId, $track->getPostId());
		$this->assertEquals($fileName, $track->getFileName());
		$this->assertEquals($mimeType, $track->getFileMimeType());
		$this->assertEquals($bbox, $track->getBounds());
		$this->assertEquals($minimumAltitude, $track->getMinimumAltitude());
		$this->assertEquals($maximumAltitude, $track->getMaximumAltitude());
	}

	public function test_canConstruct_withAltitudeInformation() {
		$this->_runTrackCreationTests(true);
	}

	public function test_canTestEquality_equalTracks_withAltitudeInformation() {
		$this->_runTrackEqualityTests(true);
	}

	private function _runTrackEqualityTests($withAltitudeInformation) {
		$track1 = $this->_generateRandomRouteTrack($withAltitudeInformation);

		if ($withAltitudeInformation) {
			$track2 = new Abp01_Route_Track($track1->getPostId(), 
				$track1->getFileName(), 
				$track1->getFileMimeType(), 
				$track1->getBounds(), 
				$track1->getMinimumAltitude(), 
				$track1->getMaximumAltitude());
		} else {
			$track2 = new Abp01_Route_Track($track1->getPostId(), 
				$track1->getFileName(), 
				$track1->getFileMimeType(), 
				$track1->getBounds());
		}

		$this->assertTrue($track1->equals($track2));
		$this->assertTrue($track2->equals($track1));

		$this->assertTrue($track1->equals($track1));
		$this->assertTrue($track2->equals($track2));
	}

	private function _generateRandomRouteTrack($withAltitudeInformation) {
		$faker = $this->_getFaker();

		$postId = $faker->numberBetween();
		$bbox = $this->_generateRandomRouteTrackBoundingBox();
		$mimeType = $faker->mimeType;
		$fileName = $this->_randomFileName();
		
		if ($withAltitudeInformation) {
			$minimumAltitude = $faker->numberBetween(0, 1000);
			$maximumAltitude = $minimumAltitude + $faker->numberBetween(0, 1000);

			$track = new Abp01_Route_Track($postId, 
				$fileName, 
				$mimeType, 
				$bbox,
				$minimumAltitude,
				$maximumAltitude);
		} else {
			$minimumAltitude = 0;
			$maximumAltitude = 0;

			$track = new Abp01_Route_Track($postId, 
				$fileName, 
				$mimeType, 
				$bbox);
		}

		return $track;
	}

	public function test_canTestEquality_equalTracks_withoutAltitudeInformation() {
		$this->_runTrackEqualityTests(false);
	}

	public function test_canTestEquality_nonEqualTracks_withAltitudeInformation() {
		$this->_runTrackInequalityTests(true);
	}

	private function _runTrackInequalityTests($withAltitudeInformation) {
		$track1 = $this->_generateRandomRouteTrack($withAltitudeInformation);
		$track2 = $this->_generateRandomRouteTrack($withAltitudeInformation);

		$this->assertFalse($track1->equals($track2));
		$this->assertFalse($track2->equals($track1));
	}

	public function test_canTestEquality_nonEqualTracks_withoutAltitudeInformation() {
		$this->_runTrackInequalityTests(false);
	}

	public function test_canConstructDisplayableInfo_withoutAltitudeInformation() {
		$track = $this->_generateRandomRouteTrack(false);
		foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $unitSystemSymbol => $label) {
			$info = $track->constructDisplayableInfo($unitSystemSymbol);
			$this->assertNotNull($info);
			$this->assertEquals(0, $info->getMinAltitude()->getValue());
			$this->assertEquals(0, $info->getMaxAltitude()->getValue());
		}
	}

	public function test_canConstructDisplayableInfo_withAltitudeInformation() {
		$track = $this->_generateRandomRouteTrack(true);
		foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $unitSystemSymbol => $label) {
			$expectedMinAlt = Abp01_UnitSystem_Value_Height::convertHeightTo($track->getMinimumAltitude(), 
				$unitSystemSymbol);
			$expectedMaxAlt = Abp01_UnitSystem_Value_Height::convertHeightTo($track->getMaximumAltitude(), 
				$unitSystemSymbol);
			
			$info = $track->constructDisplayableInfo($unitSystemSymbol);
			$this->assertNotNull($info);
			$this->assertEquals($expectedMinAlt, $info->getMinAltitude()->getValue());
			$this->assertEquals($expectedMaxAlt, $info->getMaxAltitude()->getValue());
		}
	}
}