<?php

use StepanDalecky\KmlParser\Entities\LineString;
use StepanDalecky\KmlParser\LatLngAlt;
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

 class LibKmlParserLatLngAltTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canParseTupleString_withAlt() {
		$latLng = new LatLngAlt('-90.86948943473118,48.25450093195546,110');

		$this->assertTrue($latLng->hasLongitude());
		$this->assertTrue($latLng->hasLatitude());
		$this->assertTrue($latLng->hasAltitude());

		$this->assertEquals(-90.86948943473118, $latLng->getLongitude(), '', 0.0001);
		$this->assertEquals(48.25450093195546, $latLng->getLatitude(), '', 0.0001);
		$this->assertEquals(110, $latLng->getAltitude(), '', 0.0001);

		$this->assertFalse($latLng->isEmpty());
	}

	public function test_canParseTupleString_withoutAlt() {
		$latLng = new LatLngAlt('-90.86948943473118,48.25450093195546');

		$this->assertTrue($latLng->hasLongitude());
		$this->assertTrue($latLng->hasLatitude());
		$this->assertFalse($latLng->hasAltitude());

		$this->assertEquals(-90.86948943473118, $latLng->getLongitude(), '', 0.0001);
		$this->assertEquals(48.25450093195546, $latLng->getLatitude(), '', 0.0001);
		$this->assertNull($latLng->getAltitude());

		$this->assertFalse($latLng->isEmpty());
	}

	public function test_tryParseEmptyTupleString() {
		$latLng = new LatLngAlt('');

		$this->assertFalse($latLng->hasLongitude());
		$this->assertFalse($latLng->hasLatitude());
		$this->assertFalse($latLng->hasAltitude());

		$this->assertNull($latLng->getLongitude());
		$this->assertNull($latLng->getLatitude());
		$this->assertNull($latLng->getAltitude());

		$this->assertTrue($latLng->isEmpty());
	}
 }