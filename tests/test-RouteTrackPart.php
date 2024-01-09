<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

class RouteTrackPartTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use RouteTrackPointTestDataHelpers;

	const MIN_GEN_LATLNG = 0;

	const MAX_GEN_LATLNG = 10;

	const MIN_GEN_ALT = 0;

	const MAX_GEN_ALT = 10;

	public function test_initialStateCorrect_withoutName() {
		$this->_runRouteTrackInitialStateTests(null);
	}

	private function _runRouteTrackInitialStateTests($expectedName) {
		$trackPart = $expectedName 
			? new Abp01_Route_Track_Part($expectedName) 
			: new Abp01_Route_Track_Part();

		$this->_assertCorrectTrackLinesCount($trackPart, 0);
		$this->assertEmpty($trackPart->getLines());
		$this->assertEquals($expectedName, $trackPart->getName());
	}

	private function _assertCorrectTrackLinesCount(Abp01_Route_Track_Part $part, $expectedCount) {
		$this->assertEquals($expectedCount, $part->getLinesCount());
		if ($expectedCount > 0) {
			$this->assertFalse($part->isEmpty());
		} else {
			$this->assertTrue($part->isEmpty());
		}
	}

	public function test_initialStateCorrect_withName() {
		$faker = $this->_getFaker();
		$this->_runRouteTrackInitialStateTests($faker->sentence(3));
	}

	public function test_canAddLine_withoutAltitude() {
		$trackPart = new Abp01_Route_Track_Part();
		foreach ($this->_generateLinesWithoutAlt() as $line) {
			$trackPart->addLine($line);
		}

		$this->_assertCorrectBounds($trackPart);

		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$trackPart->getMinimumLatitude());
		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$trackPart->getMinimumLongitude());

		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$trackPart->getMaximumLatitude());
		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$trackPart->getMaximumLongitude());

		$this->assertEquals(0, 
			$trackPart->getMinimumAltitude());
		$this->assertEquals(0, 
			$trackPart->getMaximumAltitude());
	}

	/**
	 * @return Abp01_Route_Track_Line[]
	 */
	private function _generateLinesWithoutAlt() {
		$points = $this->_generatePointsWithoutAltitude(self::MIN_GEN_LATLNG, self::MAX_GEN_LATLNG);
		return $this->_createLinesFromPoints($points);
	}

	private function _createLinesFromPoints(array $points) {
		$lines = array();
		$countPoints = count($points);

		for ($iPoint = 0; $iPoint < $countPoints - 1; $iPoint ++) {
			$startPoint = $points[$iPoint];
			$endPoint = $points[$iPoint + 1];
			$lines[] = $this->_createLineFromPoints($startPoint, 
				$endPoint);
		}

		return $lines;
	}

	private function _createLineFromPoints(Abp01_Route_Track_Point $startPoint, Abp01_Route_Track_Point $endPoint) {
		$line = new Abp01_Route_Track_Line();
		$line->addPoint($startPoint);
		$line->addPoint($endPoint);
		return $line;
	}

	private function _assertCorrectBounds(Abp01_Route_Track_Part $trackPart) {
		$bounds = $trackPart->getBounds();
		$this->assertNotNull($bounds);

		$this->assertEquals($trackPart->getMinimumLatitude(), 
			$bounds->getSouthWest()->getLatitude());
		$this->assertEquals($trackPart->getMinimumLongitude(), 
			$bounds->getSouthWest()->getLongitude());

		$this->assertEquals($trackPart->getMaximumLatitude(), 
			$bounds->getNorthEast()->getLatitude());
		$this->assertEquals($trackPart->getMaximumLongitude(), 
			$bounds->getNorthEast()->getLongitude());

		$this->assertEquals($trackPart->getMaximumLatitude(), 
			$bounds->getNorthWest()->getLatitude());
		$this->assertEquals($trackPart->getMinimumLongitude(), 
			$bounds->getNorthWest()->getLongitude());

		$this->assertEquals($trackPart->getMinimumLatitude(), 
			$bounds->getSouthEast()->getLatitude());
		$this->assertEquals($trackPart->getMaximumLongitude(), 
			$bounds->getSouthEast()->getLongitude());
	}

	public function test_canAddLine_withAltitude() {
		$trackPart = new Abp01_Route_Track_Part();
		foreach ($this->_generateLinesWithAlt() as $line) {
			$trackPart->addLine($line);
		}

		$this->_assertCorrectBounds($trackPart);

		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$trackPart->getMinimumLatitude());
		$this->assertEquals(self::MIN_GEN_LATLNG, 
			$trackPart->getMinimumLongitude());

		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$trackPart->getMaximumLatitude());
		$this->assertEquals(self::MAX_GEN_LATLNG - 1, 
			$trackPart->getMaximumLongitude());

		$this->assertEquals(self::MIN_GEN_ALT, 
			$trackPart->getMinimumAltitude());
		$this->assertEquals(self::MAX_GEN_ALT - 1, 
			$trackPart->getMaximumAltitude());
	}

	/**
	 * @return Abp01_Route_Track_Line[]
	 */
	private function _generateLinesWithAlt() {
		$points = $this->_generatePointsWithAltitude(self::MIN_GEN_LATLNG, self::MAX_GEN_LATLNG, 
			self::MIN_GEN_ALT, 
			self::MAX_GEN_ALT);

		return $this->_createLinesFromPoints($points);
	}
}