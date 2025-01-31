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

class RouteTrackLineTests extends WP_UnitTestCase {
	use RouteTrackPointTestDataHelpers;

	const MIN_GEN_LATLNG = 0;

	const MAX_GEN_LATLNG = 10;

	const MIN_GEN_ALT = 0;

	const MAX_GEN_ALT = 10;

	public function test_initialLineStateCorrect() {
		$line = new Abp01_Route_Track_Line();
		$this->_assertCorrectTrackPointsCount($line, 0);
		$this->assertEmpty($line->getTrackPoints());
	}

	public function test_canAddPoint_withoutAltitude() {
		$line = new Abp01_Route_Track_Line();

		foreach ($this->_generatePointsWithoutAltitude(self::MIN_GEN_LATLNG, self::MAX_GEN_LATLNG) as $point) {
			$line->addPoint($point);
		}

		$this->_assertCorrectTrackPointsCount($line, 100);

		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$line->getMinimumLatitude());
		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$line->getMinimumLongitude());

		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$line->getMaximumLatitude());
		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$line->getMaximumLongitude());

		$this->assertEquals(0, 
			$line->getMinimumAltitude());
		$this->assertEquals(0, 
			$line->getMaximumAltitude());

		$this->_assertCorrectBounds($line);
	}

	private function _assertCorrectTrackPointsCount(Abp01_Route_Track_Line $line, $expectedCount) {
		$this->assertEquals($expectedCount, $line->getTrackPointsCount());
		if ($expectedCount > 0) {
			$this->assertFalse($line->isEmpty());
		} else {
			$this->assertTrue($line->isEmpty());
		}
	}

	private function _assertCorrectBounds(Abp01_Route_Track_Line $line) {
		$bounds = $line->getBounds();
		$this->assertNotNull($bounds);

		$this->assertEquals($line->getMinimumLatitude(), 
			$bounds->getSouthWest()->getLatitude());
		$this->assertEquals($line->getMinimumLongitude(), 
			$bounds->getSouthWest()->getLongitude());

		$this->assertEquals($line->getMaximumLatitude(), 
			$bounds->getNorthEast()->getLatitude());
		$this->assertEquals($line->getMaximumLongitude(), 
			$bounds->getNorthEast()->getLongitude());

		$this->assertEquals($line->getMaximumLatitude(), 
			$bounds->getNorthWest()->getLatitude());
		$this->assertEquals($line->getMinimumLongitude(), 
			$bounds->getNorthWest()->getLongitude());

		$this->assertEquals($line->getMinimumLatitude(), 
			$bounds->getSouthEast()->getLatitude());
		$this->assertEquals($line->getMaximumLongitude(), 
			$bounds->getSouthEast()->getLongitude());
	}

	public function test_canAddPoint_withAltitude() {
		$line = new Abp01_Route_Track_Line();

		foreach ($this->_generatePointsWithAltitude(0, 10, 0, 10) as $point) {
			$line->addPoint($point);
		}

		$this->_assertCorrectTrackPointsCount($line, 1000);

		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$line->getMinimumLatitude());
		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$line->getMinimumLongitude());

		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$line->getMaximumLatitude());
		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$line->getMaximumLongitude());

		$this->assertEquals(self::MIN_GEN_ALT, 
			$line->getMinimumAltitude());
		$this->assertEquals(self::MAX_GEN_ALT - 1, 
			$line->getMaximumAltitude());

		$this->_assertCorrectBounds($line);
	}
}