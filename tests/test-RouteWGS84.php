<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

use WpTripSummary\Route\WGS84;

class RouteWGS84Tests extends WP_UnitTestCase {
	/**
	 * @dataProvider latitudeProvider
	 */
	public function test_canValidateLatitude($latitude, $expected) {
		$wgs84 = new WGS84();
		$this->assertSame($expected, $wgs84->isValidWgs84Lat($latitude));
	}

	public static function latitudeProvider() {
		return array(
			'south pole' => array(-90.0, true),
			'just inside southern limit' => array(-89.999999, true),
			'southern hemisphere' => array(-33.8688, true),
			'equator' => array(0.0, true),
			'northern hemisphere' => array(44.4268, true),
			'just inside northern limit' => array(89.999999, true),
			'north pole' => array(90.0, true),
			'below southern limit' => array(-90.000001, false),
			'above northern limit' => array(90.000001, false),
			'negative infinity' => array(-INF, false),
			'positive infinity' => array(INF, false),
			'not a number' => array(NAN, false)
		);
	}

	/**
	 * @dataProvider longitudeProvider
	 */
	public function test_canValidateLongitude($longitude, $expected) {
		$wgs84 = new WGS84();
		$this->assertSame($expected, $wgs84->isValidWgs84Lng($longitude));
	}

	public static function longitudeProvider() {
		return array(
			'western antimeridian' => array(-180.0, true),
			'just inside western limit' => array(-179.999999, true),
			'western hemisphere' => array(-120.0, true),
			'prime meridian' => array(0.0, true),
			'eastern hemisphere' => array(151.2093, true),
			'just inside eastern limit' => array(179.999999, true),
			'eastern antimeridian' => array(180.0, true),
			'below western limit' => array(-180.000001, false),
			'above eastern limit' => array(180.000001, false),
			'negative infinity' => array(-INF, false),
			'positive infinity' => array(INF, false),
			'not a number' => array(NAN, false)
		);
	}

	/**
	 * @dataProvider pointCoordinatesProvider
	 */
	public function test_canValidatePoint($latitude, $longitude, $expected) {
		$wgs84 = new WGS84();
		$point = $this->_createPoint($latitude, $longitude);
		$this->assertSame($expected, $wgs84->isvalidWgs84Point($point));
	}

	public static function pointCoordinatesProvider() {
		return array(
			'origin' => array(0, 0, true),
			'south west limits' => array(-90.0, -180.0, true),
			'south east limits' => array(-90.0, 180.0, true),
			'north west limits' => array(90.0, -180.0, true),
			'north east limits' => array(90.0, 180.0, true),
			'ordinary coordinates' => array(44.4268, 26.1025, true),
			'longitude larger than latitude limit' => array(-33.8688, 151.2093, true),
			'latitude below limit' => array(-90.000001, 26.1025, false),
			'latitude above limit' => array(90.000001, 26.1025, false),
			'longitude below limit' => array(44.4268, -180.000001, false),
			'longitude above limit' => array(44.4268, 180.000001, false),
			'both coordinates outside limits' => array(91.0, 181.0, false),
			'latitude negative infinity' => array(-INF, 0.0, false),
			'latitude positive infinity' => array(INF, 0.0, false),
			'latitude not a number' => array(NAN, 0.0, false),
			'longitude negative infinity' => array(0.0, -INF, false),
			'longitude positive infinity' => array(0.0, INF, false),
			'longitude not a number' => array(0.0, NAN, false)
		);
	}

	public function test_cannotValidatePoint_withoutCoordinate() {
		$wgs84 = new WGS84();
		$point = $this->_createPoint(44.4268, 26.1025);
		$point->coordinate = null;
		$this->assertFalse($wgs84->isvalidWgs84Point($point));
	}

	public function test_canValidateDocument_withMultiplePartsLinesAndWaypoints() {
		$wgs84 = new WGS84();
		$this->assertTrue($wgs84->isValidWgs84Document($this->_createValidDocument()));
	}

	/**
	 * @dataProvider invalidPointLocationsProvider
	 */
	public function test_cannotValidateDocument_withInvalidPoint($partIndex, $lineIndex, $pointIndex, $coordinate) {
		$wgs84 = new WGS84();
		$document = $this->_createValidDocument();
		$point = $partIndex === null
			? $document->waypoints[$pointIndex]
			: $document->parts[$partIndex]->lines[$lineIndex]->trackPoints[$pointIndex];
		$point->coordinate = $coordinate === null
			? null
			: new Abp01_Route_Track_Coordinate($coordinate[0], $coordinate[1]);

		$this->assertFalse($wgs84->isValidWgs84Document($document));
	}

	public static function invalidPointLocationsProvider() {
		$locations = array(
			'first part first line first point' => array(0, 0, 0),
			'first part first line last point' => array(0, 0, 1),
			'first part last line first point' => array(0, 1, 0),
			'first part last line last point' => array(0, 1, 1),
			'last part first line first point' => array(1, 0, 0),
			'last part first line last point' => array(1, 0, 1),
			'last part last line first point' => array(1, 1, 0),
			'last part last line last point' => array(1, 1, 1),
			'first waypoint' => array(null, null, 0),
			'last waypoint' => array(null, null, 1)
		);
		$invalidCoordinates = array(
			'invalid latitude' => array(90.000001, 26.1025),
			'invalid longitude' => array(44.4268, -180.000001),
			'missing coordinate' => null
		);
		$cases = array();
		foreach ($locations as $locationName => $location) {
			foreach ($invalidCoordinates as $coordinateName => $coordinate) {
				$cases[$locationName . ' - ' . $coordinateName] = array_merge($location, array($coordinate));
			}
		}
		return $cases;
	}

	public function test_canValidateEmptyDocument() {
		$wgs84 = new WGS84();
		$document = new Abp01_Route_Track_Document();
		$this->assertTrue($wgs84->isValidWgs84Document($document));
	}

	public function test_canValidateDocument_withNullCollections() {
		$wgs84 = new WGS84();
		$document = new Abp01_Route_Track_Document();
		$document->parts = null;
		$document->waypoints = null;
		$this->assertTrue($wgs84->isValidWgs84Document($document));
	}

	/**
	 * @dataProvider emptyCollectionsProvider
	 */
	public function test_canValidateDocument_checksRemainingPointsAfterEmptyCollection($collection, $emptyValue) {
		$wgs84 = new WGS84();
		$document = $this->_createValidDocument();
		switch ($collection) {
			case 'parts':
				$document->parts = $emptyValue;
				$remainingPoint = $document->waypoints[1];
				break;
			case 'waypoints':
				$document->waypoints = $emptyValue;
				$remainingPoint = $document->parts[1]->lines[1]->trackPoints[1];
				break;
			case 'lines':
				$document->parts[0]->lines = $emptyValue;
				$remainingPoint = $document->parts[1]->lines[1]->trackPoints[1];
				break;
			case 'trackPoints':
				$document->parts[0]->lines[0]->trackPoints = $emptyValue;
				$remainingPoint = $document->parts[0]->lines[1]->trackPoints[1];
				break;
		}

		$this->assertTrue($wgs84->isValidWgs84Document($document));
		$remainingPoint->coordinate->lng = 180.000001;
		$this->assertFalse($wgs84->isValidWgs84Document($document));
	}

	public static function emptyCollectionsProvider() {
		return array(
			'empty parts' => array('parts', array()),
			'null parts' => array('parts', null),
			'empty waypoints' => array('waypoints', array()),
			'null waypoints' => array('waypoints', null),
			'empty lines' => array('lines', array()),
			'null lines' => array('lines', null),
			'empty track points' => array('trackPoints', array()),
			'null track points' => array('trackPoints', null)
		);
	}

	private function _createValidDocument() {
		$document = new Abp01_Route_Track_Document();
		for ($partIndex = 0; $partIndex < 2; $partIndex++) {
			$part = new Abp01_Route_Track_Part();
			for ($lineIndex = 0; $lineIndex < 2; $lineIndex++) {
				$line = new Abp01_Route_Track_Line();
				$line->addPoint($this->_createPoint(44.4268, 26.1025));
				$line->addPoint($this->_createPoint(-33.8688, 151.2093));
				$part->addLine($line);
			}
			$document->addTrackPart($part);
		}
		$document->addWayPoint($this->_createPoint(-90.0, -180.0));
		$document->addWayPoint($this->_createPoint(90.0, 180.0));
		return $document;
	}

	private function _createPoint($latitude, $longitude) {
		return new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate($latitude, $longitude));
	}
}
