<?php

use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

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

class RouteTrackProcessorTests extends WP_UnitTestCase {
	use AssertionRenames;
	use RouteTrackTestDataHelpers;

	private $_testPostRouteTrackData = array();

	/**
	 * @var IntegerIdGenerator
	 */
	private $_postIdGenerator;

	public function __construct($name = null, array $data = array(), $dataName = '') {
		$this->_postIdGenerator = new IntegerIdGenerator();
		parent::__construct($name, $data, $dataName);
	}

	protected function setUp(): void {
		parent::setUp();
		$this->_installTestData();
	}

	private function _installTestData() {
		$this->_testPostRouteTrackData = $this->_initRouteTrackInfo();
	}

	private function _initRouteTrackInfo() {
		$testPostRouteTrackData = array();

		foreach ($this->_getTestGpsDocumentFormatsInfo() as $testFormatInfo) {
			for ($i = 0; $i < 5; $i ++) {
				$postId = $this->_generatePostId();
				$hasCachedTrackDocument = $i % 2 == 0;

				$document = $this->_generateAndSaveGpsDocument($postId, 
					$hasCachedTrackDocument,
					$testFormatInfo);

				$track = $this->_createRouteTrackFromGeneratedDocumentData($postId, 
					$document, 
					$testFormatInfo);

				$testPostRouteTrackData[$postId] = array(
					'track' => $track,
					'document' => $document,
					'hasCachedTrackDocument' => $hasCachedTrackDocument,
					'formatInfo' => $testFormatInfo
				);
			}
		}

		return $testPostRouteTrackData;
	}

	private function _getTestGpsDocumentFormatsInfo() {
		return array(
			new GpxDocumentFormatTestInfo(),
			new GeoJsonDocumentFormatTestInfo()
		);
	}

	private function _generateAndSaveGpsDocument($postId, $hasCachedTrackDocument, GpsDocumentFormatTestInfo $formatInfo) {
		$document = $formatInfo->generateDocument();

		$this->_storeTrackDocument($postId, 
			$document['content']['text'], 
			$formatInfo->getExtension());

		if ($hasCachedTrackDocument) {
			$this->_prepareAndStoreCachedTrackDocument($postId, 
				$document['content']['text'], 
				$formatInfo->createParserInstance());
		}

		return $document;
	}

	private function _createRouteTrackFromGeneratedDocumentData($postId, $document, GpsDocumentFormatTestInfo $formatInfo) {
		$bounds = $document['data']['content']['bounds'];
		return new Abp01_Route_Track($postId, 
			$this->_getTrackDocumentFileName($postId, $formatInfo->getExtension()), 
			$formatInfo->getDefaultMimeType(),
			new Abp01_Route_Track_Bbox($bounds['minLat'], 
				$bounds['minLng'], 
				$bounds['maxLat'], 
				$bounds['maxLng']), 
			$bounds['minAlt'],
			$bounds['maxAlt']);
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_clearTestData();
	}

	private function _clearTestData() {
		$this->_removeTestGpxAndCachedTrackDocumentFiles();
		$this->_testPostRouteTrackData = array();
	}

	private function _removeTestGpxAndCachedTrackDocumentFiles() {
		$tracksDir = $this->_getEnv()->getTracksStorageDir();
		foreach ($this->_getTestGpsDocumentFormatsInfo() as $testFormatInfo) {
			$this->_removeAllFiles($tracksDir, sprintf('*.%s', $testFormatInfo->getExtension()));
		}

		$cacheDir = $this->_getEnv()->getCacheStorageDir();
		$this->_removeAllFiles($cacheDir, '*.cache');
	}

	public function test_canProcessInitialTrackSourceFile_postWithTrackFiles() {
		$trackProcessor = $this->_getRouteTrackProcessor();
		foreach ($this->_testPostRouteTrackData as $postId => $testPostRouteData) {
			$expectedTrack = $testPostRouteData['track'];
			$testFormatInfo = $testPostRouteData['formatInfo'];

			$trackFileMimeType = $testFormatInfo->getDefaultMimeType();
			$trackFilePath = $this->_getTrackDocumentFilePath($postId, 
				$testFormatInfo->getExtension());

			$track = $trackProcessor->processInitialTrackSourceFile($postId, 
				$trackFilePath, 
				$trackFileMimeType);

			$this->assertTrue($expectedTrack->equals($track));
		}
	}

	public function test_canGetOrCreateDisplayableTrackDocument_postWithTrackFiles() {
		$trackProcessor = $this->_getRouteTrackProcessor();
		foreach ($this->_testPostRouteTrackData as $postId => $testPostRouteData) {
			$testFormatInfo = $testPostRouteData['formatInfo'];
			$trackDocument = $trackProcessor->getOrCreateDisplayableTrackDocument($testPostRouteData['track']);
			
			$mimeType = $testFormatInfo->getDefaultMimeType();
			$extension = $testFormatInfo->getExtension();

			$this->_assertTrackDocumentNotEmpty($trackDocument);

			$this->assertFileExists($trackProcessor->constructTrackFilePathForPostId($postId, 
				$mimeType));

			$this->assertFileExists($this->_getTrackDocumentFilePath($postId, 
				$extension));

			$this->assertFileExists($this->_getCachedOriginalTrackDocumentFilePath($postId));
			$this->assertFileExists($this->_getCachedTrackDocumentFilePath($postId));

			$this->_assertFileNotEmpty($trackProcessor->constructTrackFilePathForPostId($postId, 
				$mimeType));
		}
	}
	
	private function _assertTrackDocumentNotEmpty($trackDocument) {
		$this->assertNotEmpty($trackDocument);
		$this->assertNotEmpty($trackDocument->getBounds());
		$this->assertNotEmpty($trackDocument->getStartPoint());
		$this->assertNotEmpty($trackDocument->getEndPoint());
		$this->assertNotEmpty($trackDocument->parts);
	}

	public function test_canGetOrCreateDisplayableAltitudeProfile_postWithTrackFiles() {
		foreach (Abp01_UnitSystem::getAvailableUnitSystems() as $unitSystemSymbol => $label) {
			$unitSystem = Abp01_UnitSystem::create($unitSystemSymbol);
			for ($stepPoints = 1; $stepPoints <= 10; $stepPoints ++) {
				$this->_runProfileCreationTest($unitSystem, $stepPoints);
			}
		}
	}

	private function _runProfileCreationTest(Abp01_UnitSystem $unitSystem, $stepPoints) {
		$trackProcessor = $this->_getRouteTrackProcessor();
		foreach ($this->_testPostRouteTrackData as $postId => $testPostRouteData) {
			$track = $testPostRouteData['track'];
			
			$profile = $trackProcessor->getOrCreateDisplayableAltitudeProfile($track, 
				$unitSystem, 
				$stepPoints);

			$cachedDocument = $this->_readDocumentFromCachedFile($postId);

			$this->_assertProfileCorrespondsToSourceDocument($cachedDocument, 
				$profile, 
				$unitSystem, 
				$stepPoints);
		}
	}

	private function _assertProfileCorrespondsToSourceDocument(Abp01_Route_Track_Document $document, 
		Abp01_Route_Track_AltitudeProfile $profile, 
		Abp01_UnitSystem $unitSystem,
		$stepPoints) {

		$prevDistance = null;
		$expectedMinAlt = $this->_computeExpectedProfileMinimumAltitude($document, 
			$unitSystem);
		$expectedMaxAlt = $this->_computeExpectedProfileMaximumAltitude($document, 
			$unitSystem);

		$expectedProfilePointsCount = ceil($document->computeTotalPointsCount() 
			/ $stepPoints);

		$this->assertNotNull($profile);
		$this->assertNotNull($document);

		$this->assertEquals($stepPoints, 
			$profile->getStepPoints());
		$this->assertEquals($expectedProfilePointsCount, 
			$profile->getProfilePointCount());
		$this->assertEquals($unitSystem->getDistanceUnit(), 
			$profile->getDistanceUnit());
		$this->assertEquals($unitSystem->getHeightUnit(), 
			$profile->getHeightUnit());

		foreach ($profile->getProfilePoints() as $point) {
			$this->assertArrayHasKey('displayAlt', $point);
			$this->assertArrayHasKey('displayDistance', $point);
			$this->assertArrayHasKey('coord', $point);

			$this->assertLessThanOrEqual($expectedMaxAlt, 
				$point['displayAlt']);
			$this->assertGreaterThanOrEqual($expectedMinAlt, 
				$point['displayAlt']);

			if ($prevDistance !== null) {
				$this->assertGreaterThanOrEqual($prevDistance, 
					$point['displayDistance']);
			} else {
				$point['displayDistance'] = $prevDistance;
			}
		}
	}

	private function _computeExpectedProfileMinimumAltitude(Abp01_Route_Track_Document $document, 
		Abp01_UnitSystem $unitSystem) {
		return Abp01_UnitSystem_Value_Height::convertHeightTo(round($document->getMinAlt(), 2), 
			$unitSystem);
	}

	private function _computeExpectedProfileMaximumAltitude(Abp01_Route_Track_Document $document, 
		Abp01_UnitSystem $unitSystem) {
		return Abp01_UnitSystem_Value_Height::convertHeightTo(round($document->getMaxAlt(), 2), 
			$unitSystem);
	}

	public function test_tryGetOrCreateDisplayableTrackDocument_postWithoutTrackFiles() {
		$trackProcessor = $this->_getRouteTrackProcessor();

		foreach ($this->_getTestGpsDocumentFormatsInfo() as $testFormatInfo) {
			for ($i = 0; $i < 10; $i++) {
				$postId = $this->_generatePostId();
				$track = $this->_generateRandomRouteTrackWithMimeType($postId, 
					$testFormatInfo->getDefaultMimeType(),
					$testFormatInfo->getExtension());

				$trackDocument = $trackProcessor->getOrCreateDisplayableTrackDocument($track);

				$this->assertEmpty($trackDocument);
				$this->_assertTrackFilesDoNotExist($trackProcessor, 
					$postId, 
					$testFormatInfo);
			}
		}
	}

	private function _assertTrackFilesDoNotExist($trackProcessor, $postId, GpsDocumentFormatTestInfo $formatInfo) {
		$this->assertFileDoesNotExist($trackProcessor->constructTrackFilePathForPostId($postId, 
			$formatInfo->getDefaultMimeType()));

		$this->assertFileDoesNotExist($this->_getTrackDocumentFilePath($postId, 
			$formatInfo->getExtension()));

		$this->assertFileDoesNotExist($this->_getCachedOriginalTrackDocumentFilePath($postId));
		$this->assertFileDoesNotExist($this->_getCachedTrackDocumentFilePath($postId));
	}

	public function test_canDeleteTrackFiles_postWithTrackFiles() {
		$trackProcessor = $this->_getRouteTrackProcessor();
		foreach ($this->_testPostRouteTrackData as $postId => $testPostRouteData) {
			$testFormatInfo = $testPostRouteData['formatInfo'];
			$trackProcessor->deleteTrackFiles($postId);

			$this->_assertTrackFilesDoNotExist($trackProcessor, 
				$postId, 
				$testFormatInfo);
		}
	}

	public function test_canDeleteTrackFiles_postWithoutTrackFiles() {
		$trackProcessor = $this->_getRouteTrackProcessor();

		foreach ($this->_getTestGpsDocumentFormatsInfo() as $testFormatInfo) {
			for ($i = 0; $i < 10; $i++) {
				$postId = $this->_generatePostId();
				$trackProcessor->deleteTrackFiles($postId);
				$this->_assertTrackFilesDoNotExist($trackProcessor, 
					$postId, 
					$testFormatInfo);
			}
		}
	}

	protected function _generatePostId($excludeAdditionalIds = null) {
		if ($excludeAdditionalIds === null) {
			$excludeAdditionalIds = array();
		}

		return $this->_postIdGenerator
			->generateId($excludeAdditionalIds);
	}

	private function _getRouteTrackProcessor() {
		return new Abp01_Route_Track_Processor_Default(
			new Abp01_Route_Track_DocumentParser_Factory(), 
			abp01_get_env()
		);
	}
}