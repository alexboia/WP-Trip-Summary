<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

	public function setUp() {
        parent::setUp();
        $this->_installTestData();
    }

    private function _installTestData() {
        $this->_testPostRouteTrackData = $this->_initRouteTrackInfo();
    }

	private function _initRouteTrackInfo() {
		$testPostRouteTrackData = array();

		for ($i = 0; $i < 5; $i ++) {
			$postId = $this->_generatePostId();
			$track = $this->_generateRandomRouteTrackWithMimeType($postId, 'application/gpx');
			$hasCachedTrackDocument = $i % 2 == 0;

			$testPostRouteTrackData[$postId] = array(
                'track' => $track,
                'hasCachedTrackDocument' => $hasCachedTrackDocument
            );

			$this->_generateAndSaveGpxDocument($postId, 
                $hasCachedTrackDocument);
		}

		return $testPostRouteTrackData;
	}

	private function _generateAndSaveGpxDocument($postId, $hasCachedTrackDocument) {
        $gpx = $this->_getFaker()
            ->gpx();

        $this->_storeGpxDocument($postId, $gpx['content']['text']);
        if ($hasCachedTrackDocument) {
            $this->_prepareAndStoreCachedTrackDocument($postId, $gpx['content']['text']);
        }
    }

	public function tearDown() {
        parent::tearDown();
        $this->_clearTestData();
    }

    private function _clearTestData() {
        $this->_removeTestGpxAndCachedTrackDocumentFiles();
        $this->_testPostRouteTrackData = array();
    }

	private function _removeTestGpxAndCachedTrackDocumentFiles() {
        $tracksDir = $this->_getEnv()->getTracksStorageDir();
        $this->_removeAllFiles($tracksDir, '*.gpx');

        $cacheDir = $this->_getEnv()->getCacheStorageDir();
        $this->_removeAllFiles($cacheDir, '*.cache');
    }
	
	public function test_canGetOrCreateDisplayableTrackDocument_postWithTrackFiles() {
        $trackProcessor = $this->_getRouteTrackProcessor();
        foreach ($this->_testPostRouteTrackData as $postId => $testPostRouteData) {
            $trackDocument = $trackProcessor->getOrCreateDisplayableTrackDocument($testPostRouteData['track']);
            $this->_assertTrackDocumentNotEmpty($trackDocument);

            $this->assertFileExists($trackProcessor->constructTrackFilePathForPostId($postId, 'application/gpx'));

            $this->assertFileExists($this->_getGpxFilePath($postId));
            $this->assertFileExists($this->_getCachedTrackDocumentFilePath($postId));

            $this->_assertFileNotEmpty($trackProcessor->constructTrackFilePathForPostId($postId, 'application/gpx'));
        }
    }

	private function _assertTrackDocumentNotEmpty($trackDocument) {
        $this->assertNotEmpty($trackDocument);
        $this->assertNotEmpty($trackDocument->getBounds());
        $this->assertNotEmpty($trackDocument->getStartPoint());
        $this->assertNotEmpty($trackDocument->getEndPoint());
        $this->assertNotEmpty($trackDocument->parts);
    }

	public function test_tryGetOrCreateDisplayableTrackDocument_postWithTrackFiles() {
        $trackProcessor = $this->_getRouteTrackProcessor();

        for ($i = 0; $i < 10; $i++) {
            $postId = $this->_generatePostId();
            $track = $this->_generateRandomRouteTrackWithMimeType($postId, 'application/gpx');
            $trackDocument = $trackProcessor->getOrCreateDisplayableTrackDocument($track);

            $this->assertEmpty($trackDocument);
            $this->_assertTrackFilesDoNotExist($trackProcessor, $postId);
        }
    }

	private function _assertTrackFilesDoNotExist($trackProcessor, $postId) {
        $this->assertFileNotExists($trackProcessor->constructTrackFilePathForPostId($postId, 'application/gpx'));
        $this->assertFileNotExists($this->_getGpxFilePath($postId));
        $this->assertFileNotExists($this->_getCachedTrackDocumentFilePath($postId));
    }

	public function test_canDeleteTrackFiles_postWithTrackFiles() {
        $trackProcessor = $this->_getRouteTrackProcessor();
        foreach ($this->_testPostRouteTrackData as $postId => $testPostRouteData) {
            $trackProcessor->deleteTrackFiles($postId);
            $this->_assertTrackFilesDoNotExist($trackProcessor, $postId);
        }
    }

    public function test_canDeleteTrackFiles_postWithoutTrackFiles() {
        $trackProcessor = $this->_getRouteTrackProcessor();

        for ($i = 0; $i < 10; $i++) {
            $postId = $this->_generatePostId();
            $trackProcessor->deleteTrackFiles($postId);
            $this->_assertTrackFilesDoNotExist($trackProcessor, $postId);
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
		return new Abp01_Route_Track_Processor_Default(new Abp01_Route_Track_DocumentParser_Factory(), 
			abp01_get_env());
	}
}