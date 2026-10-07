<?php

use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

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

 class GpxDocumentParserTests extends WP_UnitTestCase {
	use ExpectException;
	use GenericTestHelpers;
	use TestDataFileHelpers;
	use RouteTrackDocumentTestHelpers;

	private static array $_randomGpxFilesTestInfo = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		foreach (self::_getRandomFileGenerationSpec() as $fileName => $options) {
			self::_generateAndAddRandomGpxFile($fileName, $options);
		}
	}

	private static function _getRandomFileGenerationSpec(): array {
		return GpxTestDataProvider::getRandomFileGenerationSpec();
	}

	private static function _generateAndAddRandomGpxFile(string $fileName, array $options): void {
		$faker = self::_getFaker();
		$gpxDocument = $faker->gpx(array_merge($options, array(
			'addNoPretty' => true
		)));

		$expectations = self::_saveDocumentAndDetermineExpectations($fileName, 
			$gpxDocument, 
			$options);

		self::$_randomGpxFilesTestInfo = array_merge(self::$_randomGpxFilesTestInfo, 
			$expectations);
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();
		self::_clearRandomGpxFiles();
	}

	private static function _clearRandomGpxFiles(): void {
		$fileNames = array_keys(self::$_randomGpxFilesTestInfo);
		self::_deleteAllDataFiles($fileNames);
		self::$_randomGpxFilesTestInfo = array();
	}

	public function test_canCheckIfSupported(): void {
		$this->assertEquals(function_exists('simplexml_load_string') && function_exists('simplexml_load_file'), 
			Abp01_Route_Track_DocumentParser_Gpx::isSupported());
	}

	public function test_canParse_correctDocument(): void {
		$testFiles = $this->_getValidTestFilesSpec();
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		
		foreach ($testFiles as $fileName => $testFileSpec) {
			$fileContents = $this->_readTestDataFileContents($fileName); 
			$document = $parser->parse($fileContents);
			
			$expectedDocumentData = $this->_determineExpectedDocumentData($testFiles,
				 $testFileSpec);

			if ($expectedDocumentData['document'] === true) {
				$this->assertNotNull($document);
				$this->_assertMetadataCorrect(
					$document, 
					$expectedDocumentData['metadata']
				);

				$this->_assertTrackPartsCorrect(
					$document, 
					$expectedDocumentData['trackParts']
				);

				if (!empty($expectedDocumentData['waypoints'])) {
					$this->_assertWaypointsCorrect(
						$document, 
						$expectedDocumentData['waypoints']
					);
				}
			} else {
				$this->assertNull($document);
			}
		}
	}

	/**
	 * @dataProvider emptyTrackSegmentsProvider
	 */
	public function test_canParse_omitsEmptyTrackSegments(string $trackSegments, array $expectedLines): void {
		$source = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<gpx version="1.1" creator="GpxDocumentParserTests" xmlns="http://www.topografix.com/GPX/1/1">'
			. '<trk><name>Empty segment regression</name>' . $trackSegments . '</trk></gpx>';

		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$document = $parser->parse($source);

		$this->assertInstanceOf(Abp01_Route_Track_Document::class, $document);
		$this->assertCount(1, $document->parts);
		
		$lines = $document->parts[0]->getLines();
		$this->assertCount(count($expectedLines), $lines);

		foreach ($expectedLines as $lineIndex => $expectedPoints) {
			$line = $lines[$lineIndex];
			$this->assertFalse($line->isEmpty());
			$this->assertCount(count($expectedPoints), $line->trackPoints);

			foreach ($expectedPoints as $pointIndex => $expectedCoordinate) {
				$coordinate = $line->trackPoints[$pointIndex]->coordinate;

				$this->assertSame($expectedCoordinate[0], 
					$coordinate->getLatitude());
				$this->assertSame($expectedCoordinate[1], 
					$coordinate->getLongitude());

				$this->assertEquals(0, 
					$coordinate->getAltitude());
			}
		}
	}

	public static function emptyTrackSegmentsProvider(): array {
		$emptySegments = array(
			'self-closing' => '<trkseg/>',
			'explicit closing tag' => '<trkseg></trkseg>',
			'whitespace only' => "<trkseg>\n\t </trkseg>",
			'comment only' => '<trkseg><!-- No points recorded --></trkseg>',
			'extensions only' => '<trkseg><extensions/></trkseg>',
			'no usable points' => '<trkseg><trkpt/><trkpt lat="45.1"/><trkpt lon="25.1"/></trkseg>'
		);

		$firstSegment = '<trkseg><trkpt lat="45.1" lon="25.1"/><trkpt lat="45.2" lon="25.2"/></trkseg>';
		$secondSegment = '<trkseg><trkpt lat="46.1" lon="26.1"/></trkseg>';
		$firstLine = array(array(45.1, 25.1), array(45.2, 25.2));
		$secondLine = array(array(46.1, 26.1));
		$cases = array(
			'no segments' => array('', array()),
			'only empty segments' => array(implode('', $emptySegments), array())
		);

		foreach ($emptySegments as $name => $emptySegment) {
			$cases[$name . ' - only segment'] = array($emptySegment, array());
			$cases[$name . ' - before valid segment'] = array(
				$emptySegment . $firstSegment,
				array($firstLine)
			);
			$cases[$name . ' - between valid segments'] = array(
				$firstSegment . $emptySegment . $secondSegment,
				array($firstLine, $secondLine)
			);
			$cases[$name . ' - after valid segment'] = array(
				$firstSegment . $emptySegment,
				array($firstLine)
			);
		}

		return $cases;
	}

	public function test_canParse_emptySegmentsInMtbSangeruDocument(): void {
		$source = $this->_readTestDataFileContents('mtb sangeru day 1.gpx');

		//Surround each real segment with empty ones, including at the start and end.
		$source = str_replace(
			array('<trkseg>', '</trkseg>'),
			array('<trkseg/><trkseg>', '</trkseg><trkseg/>'), 
			$source
		);

		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$document = $parser->parse($source);

		$this->assertInstanceOf(Abp01_Route_Track_Document::class, $document);
		$this->assertCount(1, $document->parts);
		$this->assertCount(91, $document->parts[0]->getLines());
		$this->assertSame(817, $document->computeTotalPointsCount());

		foreach ($document->parts[0]->getLines() as $line) {
			$this->assertFalse($line->isEmpty());
		}

		$testFiles = GpxTestDataProvider::getValidTestFilesSpec(array());
		$expect = $testFiles['mtb sangeru day 1.gpx']['expect'];

		$this->_assertTrackPartsCorrect(
			$document, 
			$expect['trackParts']
		);
	}

	/**
	 * @expectedException Abp01_Route_Track_DocumentParser_Exception
	 */
	public function test_tryParse_incorrectDocument(): void {
		$this->expectException(Abp01_Route_Track_DocumentParser_Exception::class);

		$testFiles = $this->_getInvalidTestFilesSpec();
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();

		foreach ($testFiles as $fileName) {
			$fileContents = $this->_readTestDataFileContents($fileName); 
			$document = $parser->parse($fileContents);
			$this->assertNull($document);
		}
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_nullData(): void {
		$this->expectException(InvalidArgumentException::class);
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$parser->parse(null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_emptyData(): void {
		$this->expectException(InvalidArgumentException::class);
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$parser->parse('');
	}

	private function _assertMetadataCorrect(Abp01_Route_Track_Document $actualDocument, array $expectMeta) {
		$this->assertNotNull($actualDocument->getMetadata());
		$this->assertTrue($this->_isMetadataNameCorrect(
			$actualDocument, 
			$expectMeta
		));
		$this->assertTrue($this->_isMetadataDescriptionCorrect(
			$actualDocument, 
			$expectMeta
		));
		$this->assertTrue($this->_areMetadataKeywordsCorrect(
			$actualDocument, 
			$expectMeta
		));
	}

	private function _assertWaypointsCorrect(Abp01_Route_Track_Document $actualDocument, array $expectWaypoints): void {
		$this->assertTrue($this->_areDocumentWayPointsCorrect(
			$actualDocument, 
			$expectWaypoints
		));
	}

	private function _assertTrackPartsCorrect(Abp01_Route_Track_Document $actualDocument, array $expectTrackPartsSpec): void {
		$this->assertTrue($this->_areAllTrackPartsCorrect(
			$actualDocument, 
			$expectTrackPartsSpec
		));
	}

	private function _getValidTestFilesSpec(): array {
		return GpxTestDataProvider::getValidTestFilesSpec(
			self::$_randomGpxFilesTestInfo
		);
	}

	private function _getInvalidTestFilesSpec(): array {
		return GpxTestDataProvider::getInvalidTestFilesSpec();
	}

	protected static function _getRootTestsDir(): string {
		return __DIR__;
	}
 }