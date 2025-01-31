<?php

use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

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

 class GpxDocumentParserTests extends WP_UnitTestCase {
	use ExpectException;
	use GenericTestHelpers;
	use TestDataFileHelpers;
	use RouteTrackDocumentTestHelpers;

	private static $_randomGpxFilesTestInfo = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		foreach (self::_getRandomFileGenerationSpec() as $fileName => $options) {
			self::_generateAndAddRandomGpxFile($fileName, $options);
		}
	}

	private static function _getRandomFileGenerationSpec() {
		return GpxTestDataProvider::getRandomFileGenerationSpec();
	}

	private static function _generateAndAddRandomGpxFile($fileName, $options) {
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

	private static function _clearRandomGpxFiles() {
		$fileNames = array_keys(self::$_randomGpxFilesTestInfo);
		self::_deleteAllDataFiles($fileNames);
		self::$_randomGpxFilesTestInfo = array();
	}

	public function test_canCheckIfSupported() {
		$this->assertEquals(function_exists('simplexml_load_string') && function_exists('simplexml_load_file'), 
			Abp01_Route_Track_DocumentParser_Gpx::isSupported());
	}

	public function test_canParse_correctDocument() {
		$testFiles = $this->_getValidTestFilesSpec();
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		
		foreach ($testFiles as $fileName => $testFileSpec) {
			$fileContents = $this->_readTestDataFileContents($fileName); 
			$document = $parser->parse($fileContents);
			
			$expectedDocumentData = $this->_determineExpectedDocumentData($testFiles,
				 $testFileSpec);

			if ($expectedDocumentData['document'] === true) {
				$this->assertNotNull($document);
				$this->_assertMetadataCorrect($document, $expectedDocumentData['metadata']);
				$this->_assertTrackPartsCorrect($document, $expectedDocumentData['trackParts']);

				if (!empty($expectedDocumentData['waypoints'])) {
					$this->_assertWaypointsCorrect($document, $expectedDocumentData['waypoints']);
				}
			} else {
				$this->assertNull($document);
			}
		}
	}

	/**
	 * @expectedException Abp01_Route_Track_DocumentParser_Exception
	 */
	public function test_tryParse_incorrectDocument() {
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
	public function test_tryParse_nullData() {
		$this->expectException(InvalidArgumentException::class);
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$parser->parse(null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_emptyData() {
		$this->expectException(InvalidArgumentException::class);
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$parser->parse('');
	}

	private function _assertMetadataCorrect(Abp01_Route_Track_Document $actualDocument, $expectMeta) {
		$this->assertNotNull($actualDocument->getMetadata());
		$this->assertTrue($this->_isMetadataNameCorrect($actualDocument, $expectMeta));
		$this->assertTrue($this->_isMetadataDescriptionCorrect($actualDocument, $expectMeta));
		$this->assertTrue($this->_areMetadataKeywordsCorrect($actualDocument, $expectMeta));
	}

	private function _assertWaypointsCorrect(Abp01_Route_Track_Document $actualDocument, $expectWaypoints) {
		$this->assertTrue($this->_areDocumentWayPointsCorrect($actualDocument, $expectWaypoints));
	}

	private function _assertTrackPartsCorrect(Abp01_Route_Track_Document $actualDocument, $expectTrackPartsSpec) {
		$this->assertTrue($this->_areAllTrackPartsCorrect($actualDocument, $expectTrackPartsSpec));
	}

	private function _getValidTestFilesSpec() {
		return GpxTestDataProvider::getValidTestFilesSpec(self::$_randomGpxFilesTestInfo);
	}

	private function _getInvalidTestFilesSpec() {
		return GpxTestDataProvider::getInvalidTestFilesSpec();
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
 }