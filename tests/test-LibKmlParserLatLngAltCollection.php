<?php

use StepanDalecky\KmlParser\Entities\LineString;
use StepanDalecky\KmlParser\LatLngAlt;
use StepanDalecky\KmlParser\LatLngAltCollection;
use StepanDalecky\KmlParser\Parser;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

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

 class LibKmlParserLatLngAltCollectionTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canParseTuplesString_withAlt() {
		$latLngCollection = new LatLngAltCollection('-122.364167,37.824787,50 -122.363917,37.824423,55');

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

	public function test_canParseTupleStrings_withoutAlt() {
		$latLngCollection = new LatLngAltCollection('-122.364167,37.824787 -122.363917,37.824423');

		$this->assertEquals(2, count($latLngCollection));

		$p0 = $latLngCollection[0];
		$this->assertTrue($p0->hasLongitude());
		$this->assertTrue($p0->hasLatitude());
		$this->assertFalse($p0->hasAltitude());
		$this->assertFalse($p0->isEmpty());

		$this->assertEquals(-122.364167, $p0->getLongitude(), '', 0.0001);
		$this->assertEquals(37.824787, $p0->getLatitude(), '', 0.0001);
		$this->assertNull($p0->getAltitude());

		$p1 = $latLngCollection[1];
		$this->assertTrue($p1->hasLongitude());
		$this->assertTrue($p1->hasLatitude());
		$this->assertFalse($p1->hasAltitude());
		$this->assertFalse($p1->isEmpty());

		$this->assertEquals(-122.363917, $p1->getLongitude(), '', 0.0001);
		$this->assertEquals(37.824423, $p1->getLatitude(), '', 0.0001);
		$this->assertNull($p1->getAltitude());
	}

	public function test_tryParseEmptyTupleString() {
		$latLngCollection = new LatLngAltCollection('');
		$this->assertEquals(0, count($latLngCollection));
	}
 }