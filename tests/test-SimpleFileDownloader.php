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

class SimpleFileDownloaderTests extends WP_UnitTestCase {
    use GenericTestHelpers;
    use TestDataFileHelpers;

    public function setUp() {
        parent::setUp();
        Abp01SendHeaderState::clearSendHeaderCalls();
        Abp01SetHttpResponseCodeState::clearCurrentResponseCode();
    }

    public function tearDown() {
        parent::tearDown();
        Abp01SendHeaderState::clearSendHeaderCalls();
        Abp01SetHttpResponseCodeState::clearCurrentResponseCode();
    }

    /**
     * @runInSeparateProcess
     * @dataProvider existingNonEmptyDataFilesProvider
     * @preserveGlobalState disabled
     */
    public function test_canSendFileWithMimeType_fileExists_notEmpty($testFile) {
        $this->_assertNoHeadersSent();

        $filePath = $this->_determineDataFilePath($testFile);
        $sentContent = $this->_runFileDownloadTestAndGetResult($filePath);

        $this->_assertSentContentsMatchesSourceFile($testFile, $sentContent);
        $this->_assertCorrectFileDownloadHeadersSent();
    }

    private function _assertNoHeadersSent() {
        $this->assertEquals(0, Abp01SendHeaderState::countSendHeaderCalls());
    }

    private function _runFileDownloadTestAndGetResult($filePath) {
        $downloader = new Abp01_Transfer_SimpleFileDownloader();
        
        ob_start();
        $downloader->sendFileWithMimeType($filePath, 'application/gpx');
        $sentContent = ob_get_clean();
        
        return $sentContent;
    }

    private function _assertSentContentsMatchesSourceFile($sourceFile, $sentContent) {
        $this->assertNotEmpty($sentContent);
        $this->assertEquals($this->_readTestDataFileContents($sourceFile), $sentContent);
    }

    private function _assertCorrectFileDownloadHeadersSent() {
        $this->assertEquals(3, Abp01SendHeaderState::countSendHeaderCalls());
        $this->assertTrue(Abp01SendHeaderState::hasHeaderWithName('Content-Type'));
        $this->assertTrue(Abp01SendHeaderState::hasHeaderWithName('Content-Length'));
        $this->assertTrue(Abp01SendHeaderState::hasHeaderWithName('Content-Disposition'));
    }

    /**
     * @runInSeparateProcess
     * @dataProvider existingEmptyDataFilesProvider
     * @preserveGlobalState disabled
     */
    public function test_canSendFileWithMimeType_fileExists_empty($testFile) {
        $this->_assertNoHeadersSent();

        $filePath = $this->_determineDataFilePath($testFile);
        $sentContent = $this->_runFileDownloadTestAndGetResult($filePath);

        $this->assertEmpty($sentContent);
        $this->_assertCorrectFileDownloadHeadersSent();
    }

    /**
     * @runInSeparateProcess
     * @dataProvider nonExistingDataFilesProvider
     * @preserveGlobalState disabled
     */
    public function test_trySendFileWithMimeType_fileDoesNotExist($testFile) {
        $this->_assertNoResponseCodeSet();

        $filePath = $this->_determineDataFilePath($testFile);
        $sentContent = $this->_runFileDownloadTestAndGetResult($filePath);

        $this->assertEmpty($sentContent);
        $this->assertTrue(Abp01SetHttpResponseCodeState::hasCurrentResponseCode());
        $this->assertTrue(Abp01SetHttpResponseCodeState::currentResponseCodeIsHttpNotFound());
    }

    private function _assertNoResponseCodeSet() {
        $this->assertFalse(Abp01SetHttpResponseCodeState::hasCurrentResponseCode());
    }

    public function existingNonEmptyDataFilesProvider() {
        return array(
            array('test-inv1-jibberish.gpx'),
            array('test2-strava-utf8-bom.gpx'),
            array('test4-empty-utf8-bom.gpx'),
            array('test1-garmin-desktop-app-utf8-bom.gpx'),
            array('test3-bikemap-utf8-bom.gpx')
        );
    }

    public function existingEmptyDataFilesProvider() {
        return array(
            array( 'test-empty-file.dat' ),
            array( 'test-empty-file.gpx' )
        );
    }

    public function nonExistingDataFilesProvider() {
        $faker = $this->_getFaker();
        $count = $faker->numberBetween(1, 10);
        $data = array();

        for ($i = 0; $i < $count; $i ++) {
            $data[] = array( $faker->uuid . '.' . $faker->fileExtension );
        }

        return $data;
    }

    protected static function _getRootTestsDir() {
        return __DIR__;
    }
}