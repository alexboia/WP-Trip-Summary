<?php

use StepanDalecky\KmlParser\Entities\Placemark;
use StepanDalecky\KmlParser\Parser;

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

 class LibKmlParserPlacemarkTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use TestDataFileHelpers;

	public function test_canParse_withPoint() {
		$this->_runPlacemarkTest('test-kml-placemark-with-point.kml', 
			function(Placemark $placemark) {
				$this->assertEquals('Placemark with Point', $placemark->getName());

				$this->assertTrue($placemark->hasPoint());
				$this->assertFalse($placemark->hasLineString());
				$this->assertFalse($placemark->hasLinearRing());
				$this->assertFalse($placemark->hasPolygon());
				$this->assertFalse($placemark->hasMultiGeometry());

				$point = $placemark->getPoint();
				$this->assertNotNull($point);
				$this->assertTrue($point->hasCoordinate());
				
				$coord = $point->getCoordinate();
				$this->assertNotNull($coord);

				$latLng = $coord->getLatLngAlt();
				$this->assertEquals(-90.86948943473118, $latLng->getLongitude());
				$this->assertEquals(48.25450093195546, $latLng->getLatitude());
				$this->assertEquals(123.456, $latLng->getAltitude());
			}
		);
	}

	private function _runPlacemarkTest($file, $asserter) {
		$fileContents = $this->_readTestDataFileContents($file); 
		$kmlParser = Parser::fromString($fileContents);
		$kml = $kmlParser->getKml();
		$document = $kml->getDocument();

		$placemarks = $document->getPlacemarks();
		$this->assertNotNull($placemarks);
		$this->assertEquals(1, count($placemarks));
		
		$pk0 = $placemarks[0];
		$this->assertNotNull($pk0);
		$asserter($pk0);
	}

	public function test_canParse_withLineString() {
		$this->_runPlacemarkTest('test-kml-placemark-with-lineString.kml', 
			function(Placemark $placemark) {
				$this->assertEquals('Placemark with LineString', $placemark->getName());

				$this->assertFalse($placemark->hasPoint());
				$this->assertTrue($placemark->hasLineString());
				$this->assertFalse($placemark->hasLinearRing());
				$this->assertFalse($placemark->hasPolygon());
				$this->assertFalse($placemark->hasMultiGeometry());

				$lineString = $placemark->getLineString();
				$this->assertNotNull($lineString);
				$this->assertTrue($lineString->hasCoordinates());

				$coords = $lineString->getCoordinates();
				$this->assertNotNull($coords);
				
				$latLngCollection = $coords->getLatLngAltCollection();
				$this->assertNotNull($latLngCollection);
				$this->assertEquals(2, count($latLngCollection));

				$p0 = $latLngCollection[0];
				$this->assertTrue($p0->hasLongitude());
				$this->assertTrue($p0->hasLatitude());
				$this->assertTrue($p0->hasAltitude());
				$this->assertFalse($p0->isEmpty());

				$this->assertEquals(-122.364167, $p0->getLongitude(), '', 0.0001);
				$this->assertEquals(37.824787, $p0->getLatitude(), '', 0.0001);
				$this->assertEquals(50, $p0->getAltitude(), '', 0.0001);

				$p1 = $latLngCollection[1];
				$this->assertTrue($p1->hasLongitude());
				$this->assertTrue($p1->hasLatitude());
				$this->assertTrue($p1->hasAltitude());
				$this->assertFalse($p1->isEmpty());

				$this->assertEquals(-122.363917, $p1->getLongitude(), '', 0.0001);
				$this->assertEquals(37.824423, $p1->getLatitude(), '', 0.0001);
				$this->assertEquals(55, $p1->getAltitude(), '', 0.0001);
			}
		);
	}

	public function test_canParse_withLinearRing() {
		
	}

	public function test_canParse_withPolygon() {
		
	}

	public function test_canParse_withMultiGeometry() {
		
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
 }