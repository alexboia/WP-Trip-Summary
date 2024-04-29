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

class LogFileInfoTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	const TEST_FILE_COUNT = 10;

	protected function setUp(): void {
		parent::setUp();
		$this->_removeAllLogFiles();
	}

	private function _removeAllLogFiles() {
		$env = $this->_getEnv();
		$logsDir = $env->getLogStorageDir();
		$this->_removeAllFiles($logsDir, '*.txt');
		$this->_removeAllFiles($logsDir, '*.log');
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		$this->_removeAllLogFiles();
	}

	public function test_canCheckIfDebugLogFiles_whenIsDebugLogFile() {
		$config = abp01_get_logger_config();
		$debugLogFiles = $this->_generateDatedFilesWithContents('wpts_debug', 
			$config->getLoggerDirectory(), 
			self::TEST_FILE_COUNT);

		foreach ($debugLogFiles as $filePath) {
			$fileInfo = new Abp01_Logger_FileInfo($filePath);
			$this->assertTrue($fileInfo->isDebugLogFile());
		}
	}

	public function test_canCheckIfDebugLogFiles_whenIsNotDebugLogFile() {
		$config = abp01_get_logger_config();
		$bogusFiles = $this->_generateDatedFilesWithContents('bogus_prefix_file', 
			$config->getLoggerDirectory(), 
			self::TEST_FILE_COUNT);

		foreach ($bogusFiles as $filePath) {
			$fileInfo = new Abp01_Logger_FileInfo($filePath);
			$this->assertFalse($fileInfo->isDebugLogFile());
		}
	}

	public function test_canCheckIfErrorLogFiles_whenIsErrorLogFile() {
		$config = abp01_get_logger_config();
		$errorLogFiles = $this->_generateDatedFilesWithContents('wpts_error', 
			$config->getLoggerDirectory(), 
			self::TEST_FILE_COUNT);

		foreach ($errorLogFiles as $filePath) {
			$fileInfo = new Abp01_Logger_FileInfo($filePath);
			$this->assertTrue($fileInfo->isErrorLogFile());
		}
	}

	public function test_canCheckIfErrorLogFiles_whenIsNotErrorLogFile() {
		$config = abp01_get_logger_config();
		$bogusFiles = $this->_generateDatedFilesWithContents('bogus_prefix_file', 
			$config->getLoggerDirectory(), 
			self::TEST_FILE_COUNT);

		foreach ($bogusFiles as $filePath) {
			$fileInfo = new Abp01_Logger_FileInfo($filePath);
			$this->assertFalse($fileInfo->isErrorLogFile());
		}
	}

	public function test_canConvertToPlainObject_debugLogFiles() {
		$this->_runToPlainTestConversionTests('wpts_debug', 
			true, 
			false);
	}

	private function _runToPlainTestConversionTests($filePrefix) {
		$config = abp01_get_logger_config();
		$logFiles = $this->_generateDatedFilesWithContents($filePrefix, 
			$config->getLoggerDirectory(), 
			self::TEST_FILE_COUNT);

		foreach ($logFiles as $filePath) {
			$fileInfo = new Abp01_Logger_FileInfo($filePath);
			$asPlainObject = $fileInfo->asPlainObject();

			$this->assertNotNull($asPlainObject);
			$this->assertEquals($asPlainObject->id, 
				$fileInfo->id());
			$this->assertEquals($asPlainObject->fileName, 
				$fileInfo->getFileName());
			$this->assertEquals($asPlainObject->fileSize, 
				$fileInfo->getFileSize());
			$this->assertEquals($asPlainObject->formattedSize, 
				$fileInfo->getFormattedFileSize());
			$this->assertEquals($asPlainObject->lastModified, 
				$fileInfo->getLastModified());
			$this->assertEquals($asPlainObject->fomattedLastModified, 
				date('Y-m-d H:i:s', $asPlainObject->lastModified));
			$this->assertEquals($asPlainObject->isErrorLogFile, 
				$fileInfo->isErrorLogFile());
			$this->assertEquals($asPlainObject->isDebugLogFile, 
				$fileInfo->isDebugLogFile());
		}
	}

	public function test_canConvertToPlainObject_errorLogFiles() {
		$this->_runToPlainTestConversionTests('wpts_error', 
			false, 
			true);
	}
}