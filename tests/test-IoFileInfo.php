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

class IoFileInfoTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	protected function setUp(): void {
		parent::setUp();
		$this->_removeAllLogFiles();
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		$this->_removeAllLogFiles();
	}

	private function _removeAllLogFiles() {
		$dir = WP_CONTENT_DIR;
		$this->_removeAllFiles($dir, 'f_rand_*.txt');
		$this->_removeAllFiles($dir, 'f_rand_*.log');
	}

	public function test_canGetId() {
		$fileData = $this->_generateRandomTestFile();
		$filePath = $fileData['path'];
		
		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$fileId = $fileInfo->id();

		$this->assertNotNull($fileId);
		$this->assertTrue($fileInfo->matchesId($fileId));

		$this->assertEquals(sha1($filePath), $fileId);

		$fileIdAgain = $fileInfo->id();
		$this->assertEquals($fileId, $fileIdAgain);
	}

	public function test_canCheckIfMatchesId_whenMatches() {
		$fileData = $this->_generateRandomTestFile();
		$filePath = $fileData['path'];

		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$fileId = $fileInfo->id();

		$this->assertTrue($fileInfo->matchesId($fileId));
	}

	public function test_canCheckIfMatchesId_whenDoesntMatch() {
		$fileData = $this->_generateRandomTestFile();
		$filePath = $fileData['path'];

		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$bogusFileId = $this->_randomSha1();

		$this->assertFalse($fileInfo->matchesId($bogusFileId));
	}

	public function test_canGetContents_whenFileExists_notEmpty() {
		$fileData = $this->_generateRandomTestFile();
		$filePath = $fileData['path'];
		$fileContents = $fileData['contents'];

		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$readContents = $fileInfo->contents();

		$this->assertNotNull($readContents);
		$this->assertEquals($fileContents, $readContents);
	}

	public function test_canGetContents_whenFileDoesntExist() {
		$bogusFilePath = $this->_generateRandomTestFilePath();

		$fileInfo = new Abp01_Io_FileInfo($bogusFilePath);
		$readContents = $fileInfo->contents();

		$this->assertNull($readContents);
	}

	public function test_canReadTail_whenFileExists_tailLessThanFileLineCount() {
		$runTimes = 5;
		for ($i = 0; $i < $runTimes; $i ++) {
			$this->_runReadTailWhenRequestedLessThanFileLineCountTests();
		}
	}

	private function _runReadTailWhenRequestedLessThanFileLineCountTests() {
		$faker = $this->_getFaker();
		$lineCount = $faker->numberBetween(100, 1000);
		$tailLineCount = $faker->numberBetween(1, intval($lineCount / 2));

		$fileData = $this->_generateRandomTestFile($lineCount);
		$filePath = $fileData['path'];
		$fileLines = $fileData['lines'];
		
		$expectedTail = array_slice($fileLines, count($fileLines) - $tailLineCount);

		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$readTail = $fileInfo->tail($tailLineCount);
		$readTailLines = explode("\n", $readTail);

		$this->assertGreaterThanOrEqual($tailLineCount, 
			count($readTailLines));

		for ($i = 0; $i < $tailLineCount - 1; $i ++) { 
			$this->assertEquals($expectedTail[$i], $readTailLines[$i]);
		}

		$expectedLastTailLine = $expectedTail[$tailLineCount - 1];
		$lastReadTailLine = $readTailLines[$tailLineCount - 1];

		$this->assertStringStartsWith($expectedLastTailLine, 
			$lastReadTailLine);
	}

	public function test_canReadTail_whenFileExists_tailMoreThanFileLineCount() {
		$runTimes = 5;
		for ($i = 0; $i < $runTimes; $i ++) {
			$this->_runReadTailWhenRequestedMoreThanFileLineCountTests();
		}
	}

	private function _runReadTailWhenRequestedMoreThanFileLineCountTests() {
		$faker = $this->_getFaker();
		$lineCount = $faker->numberBetween(100, 1000);
		
		$tailLineCount = $lineCount + $faker->numberBetween(10, 20);
		$expectedTailLineCount = $lineCount;

		$fileData = $this->_generateRandomTestFile($lineCount);
		$filePath = $fileData['path'];
		$fileLines = $fileData['lines'];
		
		$expectedTail = array_slice($fileLines, count($fileLines) - $expectedTailLineCount);

		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$readTail = $fileInfo->tail($tailLineCount);
		$readTailLines = explode("\n", $readTail);

		$this->assertEquals($expectedTailLineCount, 
			count($readTailLines));

		for ($i = 0; $i < $expectedTailLineCount; $i ++) { 
			$this->assertEquals($expectedTail[$i], $readTailLines[$i]);
		}
	}

	public function test_canReadTail_whenFileDoesntExist() {
		$bogusFilePath = $this->_generateRandomTestFilePath();

		$fileInfo = new Abp01_Io_FileInfo($bogusFilePath);
		$readTail = $fileInfo->tail();

		$this->assertNull($readTail);
	}

	public function test_canCheckIfExists_whenFileExists() {
		$fileData = $this->_generateRandomTestFile();
		$filePath = $fileData['path'];

		$fileInfo = new Abp01_Io_FileInfo($filePath);
		$this->assertTrue($fileInfo->exists());
	}

	public function test_canCheckIfExists_whenFileDoesnExist() {
		$bogusFilePath = $this->_generateRandomTestFilePath();

		$fileInfo = new Abp01_Io_FileInfo($bogusFilePath);
		$this->assertFalse($fileInfo->exists());
	}
}