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

use StepanDalecky\KmlParser\Entities\Kml;
use StepanDalecky\KmlParser\Exceptions\InvalidKmlRootElementException;
use StepanDalecky\KmlParser\Parser;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

 class LibKmlParserTests extends WP_UnitTestCase {
	use ExpectException;
	use GenericTestHelpers;
	use TestDataFileHelpers;

	public function test_canParseEmptyKml() {
		$fileContents = $this->_readTestDataFileContents('kml/test-kml-empty.kml'); 
		$kmlParser = Parser::fromString($fileContents);
		$kml = $kmlParser->getKml();
		$this->assertNotNull($kml);
		$this->_assertEmptyKml($kml);
	}

	private function _assertEmptyKml(Kml $kml) {
		$this->assertFalse($kml->hasDocument());
		$this->assertFalse($kml->hasFolder());
	}

	public function test_tryParseKmlFileWithInvalidRoot_shouldThrowException() {
		$this->expectException(InvalidKmlRootElementException::class);
		$fileContents = $this->_readTestDataFileContents('kml/test-kml-invalidRoot.kml'); 
		Parser::fromString($fileContents);
	}

	public function test_canParseKml_documentAsRoot_noFolders_singlePlacemark() {
		$fileContents = $this->_readTestDataFileContents('kml/test-kml-document-as-root-no-folders.kml'); 
		$kmlParser = Parser::fromString($fileContents);
		
		$kml = $kmlParser->getKml();
		$this->assertTrue($kml->hasDocument());
		$this->assertFalse($kml->hasFolder());

		$document = $kml->getDocument();
		$this->assertNotNull($document);

		$this->assertTrue($document->hasName());
		$this->assertEquals('Azuga - Zizin - Vama Buzaului - Brasov.kml', $document->getName());

		$this->assertTrue($document->hasPlacemarks());

		$placemarks = $document->getPlacemarks();
		$this->assertEquals(1, count($placemarks));

		$pk0 = $placemarks[0];
		$this->assertNotNull($pk0);
		$this->assertTrue($pk0->hasName());
		$this->assertEquals('Azuga - Zizin - Vama Buzaului - Brasov', $pk0->getName());

		$this->assertTrue($pk0->hasLineString());
		$this->assertFalse($pk0->hasLinearRing());
		$this->assertFalse($pk0->hasPoint());
		$this->assertFalse($pk0->hasPolygon());
		$this->assertFalse($pk0->hasMultiGeometry());

		$ls0 = $pk0->getLineString();
		$this->assertNotNull($ls0);
		$this->assertTrue($ls0->hasCoordinates());

		$coords0 = $ls0->getCoordinates();
		$this->assertNotNull($coords0);

		$latLngs0 = $coords0->getLatLngAltCollection();
		$this->assertNotNull($latLngs0);

		$this->assertGreaterThan(10, count($latLngs0));

		$p0 = $latLngs0[0];
		$this->assertTrue($p0->hasLongitude());
		$this->assertTrue($p0->hasLatitude());
		$this->assertTrue($p0->hasAltitude());
		$this->assertFalse($p0->isEmpty());

		$this->assertEquals(25.55129386596317, $p0->getLongitude(), '', 0.0001);
		$this->assertEquals(45.44035592109068, $p0->getLatitude(), '', 0.0001);
		$this->assertEquals(0, $p0->getAltitude(), '', 0.0001);


		$pN = $latLngs0[count($latLngs0) - 1];
		$this->assertTrue($pN->hasLongitude());
		$this->assertTrue($pN->hasLatitude());
		$this->assertTrue($pN->hasAltitude());
		$this->assertFalse($pN->isEmpty());

		$this->assertEquals(25.99871164164519, $pN->getLongitude(), '', 0.0001);
		$this->assertEquals(45.65004792577662, $pN->getLatitude(), '', 0.0001);
		$this->assertEquals(0, $pN->getAltitude(), '', 0.0001);
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
 }