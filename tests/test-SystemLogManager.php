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

class SystemLogManagerTests extends WP_UnitTestCase {
	const TEST_LOG_FILE_COUNT = 10;
	
	use GenericTestHelpers;

	protected function setUp(): void {
		parent::setUp();
		$this->_removeAllLogConfigFilters();
		$this->_removeAllLogFiles();
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		$this->_removeAllLogConfigFilters();
		$this->_removeAllLogFiles();
	}

	private function _removeAllLogConfigFilters() {
		remove_all_filters('abp01_get_logger_config_rotate_logs');
		remove_all_filters('abp01_get_logger_config_max_log_files');
		remove_all_filters('abp01_get_logger_class');
	}

	private function _removeAllLogFiles() {
		$env = $this->_getEnv();
		$logsDir = $env->getLogStorageDir();
		$this->_removeAllFiles($logsDir, '*.txt');
		$this->_removeAllFiles($logsDir, '*.log');
	}

	public function test_canGetLoggerConfig_withoutCustomizations() {
		$env = $this->_getEnv();
		$config = abp01_get_logger_config();

		$this->assertNotNull($config);
		$this->_assetValidConfig($config, 
			$env->getLogStorageDir(), 
			true, 
			10, 
			$env->isDebugMode(), 
			true);
	}

	private function _assetValidConfig(Abp01_Logger_Config $config, 
			$expStorageDir, 
			$expLogsRotate, 
			$expMaxLogFiles, 
			$expDebugMode, 
			$expErrorLogginEnabled) {

		$this->assertEquals($expStorageDir, 
			$config->getLoggerDirectory());
		$this->assertEquals($expLogsRotate, 
			$config->shouldRotateLogs());
		$this->assertEquals($expMaxLogFiles, 
			$config->getMaxLogFiles());
		$this->assertEquals($expDebugMode, 
			$config->isDebugMode());
		$this->assertEquals($expErrorLogginEnabled, 
			$config->isErrorLoggingEnabled());

		$this->assertEquals('wpts_error.log', 
			basename($config->getErrorLogFile()));
		$this->assertEquals('wpts_debug.log', 
			basename($config->getDebugLogFile()));
	}

	/**
	 * @runInSeparateProcesss
	 */
	public function test_canGetLoggerConfig_withHookCustomizations() {
		$this->_addLogConfigCustomizationHooks(false, 15);

		$env = $this->_getEnv();
		$config = abp01_get_logger_config();

		$this->assertNotNull($config);
		$this->_assetValidConfig($config, 
			$env->getLogStorageDir(), 
			false, 
			15, 
			$env->isDebugMode(), 
			true);
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLoggerConfig_withConstantCustomizations() {
		define('ABP01_LOGS_ROTATE', false);
		define('ABP01_LOGS_MAX_LOG_FILES', 15);
		
		$env = $this->_getEnv();
		$config = abp01_get_logger_config();

		$this->assertNotNull($config);
		$this->_assetValidConfig($config, 
			$env->getLogStorageDir(), 
			ABP01_LOGS_ROTATE, 
			ABP01_LOGS_MAX_LOG_FILES, 
			$env->isDebugMode(), 
			true);
	}

	private function _addLogConfigCustomizationHooks($retRotateLogs, $retMaxLogFiles) {
		add_filter('abp01_get_logger_config_rotate_logs', 
			function($rotateLogs) use ($retRotateLogs) {
				return $retRotateLogs;
			});

		add_filter('abp01_get_logger_config_max_log_files', 
			function($maxLogFiles) use ($retMaxLogFiles) {
				return $retMaxLogFiles;
			});
	}

	public function test_canGetLogManager() {
		$logManager = abp01_get_log_manager();
		$this->assertNotNull($logManager);
		$this->assertInstanceOf(Abp01_Logger_Manager::class, $logManager);

		$logManagerAgain = abp01_get_log_manager();
		$this->assertSame($logManager, $logManagerAgain);
	}

	public function test_canGetLog_defaultLogClass() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$logger = $logManager->getLogger();

		$this->_assertLoggerClassValid($logger, 
			Abp01_Logger_MonologLogger::class);
	}

	private function _assertLoggerClassValid($logger, $expClassName) {
		$this->assertNotNull($logger);
		$this->assertInstanceOf(Abp01_Logger::class, $logger);
		$this->assertEquals($expClassName, get_class($logger));
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLog_customLogClassViaHook_whenClassValid() {
		$this->_registerCustomLogClass(Abp01StubLogger::class);

		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$logger = $logManager->getLogger();
		
		$this->_assertLoggerClassValid($logger, 
			Abp01StubLogger::class);
	}

	private function _registerCustomLogClass($retClassName) {
		add_filter('abp01_get_logger_class', function($className) use ($retClassName) {
			return $retClassName;
		});
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLog_customLogClassViaHook_whenClassInvalid() {
		$this->_registerCustomLogClass(Abp01InvalidStubLogger::class);

		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$logger = $logManager->getLogger();
		
		$this->_assertLoggerClassValid($logger, 
			Abp01_Logger_MonologLogger::class);
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLogFiles_whenNoneFound() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$files = $logManager->getLogFiles();
		$this->assertNotNull($files);
		$this->assertCount(0, $files);
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLogFiles_whenLogFilesFound() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$expectedFiles = $this->_generateErrorAndDebugLogFiles($logManager->getConfig(), 
			self::TEST_LOG_FILE_COUNT, 
			self::TEST_LOG_FILE_COUNT);
		
		$expectedTotal = count($expectedFiles['error']) 
			+ count($expectedFiles['debug']);

		$files = $logManager->getLogFiles();
		$this->assertNotNull($files);
		$this->assertCount($expectedTotal, $files);

		foreach ($expectedFiles['error'] as $expectedDebugLog) {
			$this->_assertLogFileInReturnedCollection($expectedDebugLog, $files);
		}

		foreach ($expectedFiles['debug'] as $expectedDebugLog) {
			$this->_assertLogFileInReturnedCollection($expectedDebugLog, $files);
		}
	}

	private function _assertLogFileInReturnedCollection($expectedLog, array $files) {
		$found = false;
		$searchPath = strtolower(realpath($expectedLog));
		
		foreach ($files as $file) {
			/** @var Abp01_Logger_FileInfo $file */
			$matchPath = strtolower(realpath($file->getFilePath()));
			if ($searchPath == $matchPath) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, sprintf(
			'File "%s" not found in actual log file collection.', 
			basename($expectedLog)
		));
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLogFileById_whenExists() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$this->_generateErrorAndDebugLogFiles($logManager->getConfig(), 
			self::TEST_LOG_FILE_COUNT, 
			self::TEST_LOG_FILE_COUNT);

		$files = $logManager->getLogFiles();
		foreach ($files as $file) {
			$id = $file->id();
			$readFile = $logManager->getLogFileById($id);
			$this->assertNotNull($readFile);
			$this->assertEquals($file->id(), $readFile->id());
			$this->assertEquals($file->contents(), $readFile->contents());
			$this->assertEquals($file->getFileSize(), $readFile->getFileSize());
		}
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canGetLogFileById_whenDoesntExit() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		for ($i = 0; $i < self::TEST_LOG_FILE_COUNT; $i ++) {
			$bogusFilePath = $this->_generateBogusFilePath($i);
			$bogusFileId = sha1($bogusFilePath);
			$readFile = $logManager->getLogFileById($bogusFileId); 
			$this->assertNull($readFile);
		}
	}

	private function _generateBogusFilePath($index) {
		return sprintf('/tmp/bogus/path/%d', $index);
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canDeleteLogFileById_whenExists() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		$this->_generateErrorAndDebugLogFiles($logManager->getConfig(), 10, 10);

		$files = $logManager->getLogFiles();
		foreach ($files as $file) {
			$fileId = $file->id();
			$result = $logManager->deleteLogFileById($fileId);
			$this->assertTrue($result);

			$readFile = $logManager->getLogFileById($fileId);
			$this->assertNull($readFile);
		}
	}

	/**
	 * @runInSeparateProcesss 
	 * @preserveGlobalState disabled
	 */
	public function test_canDeleteLogFileById_whenDoesntExist() {
		$logManager = $this->_createTestLogManagerWithDefaultConfig();
		for ($i = 0; $i < self::TEST_LOG_FILE_COUNT; $i ++) {
			$bogusFilePath = $this->_generateBogusFilePath($i);
			$bogusFileId = sha1($bogusFilePath);
			
			$result = $logManager->deleteLogFileById($bogusFileId);
			$this->assertFalse($result);
			
			$readFile = $logManager->getLogFileById($bogusFileId); 
			$this->assertNull($readFile);
		}
	}

	private function _createTestLogManagerWithDefaultConfig() {
		return new Abp01_Logger_Manager(abp01_get_logger_config());
	}

	private function _generateErrorAndDebugLogFiles(Abp01_Logger_Config $config, 
		$errLogCount, 
		$debugLogCount) {
		
		$debugLogFiles = array();
		$erroLogFiles = array();
		
		$erroLogFiles = $this->_generateDatedFilesWithContents('wpts_error', 
			$config->getLoggerDirectory(), 
			$errLogCount);
		$debugLogFiles = $this->_generateDatedFilesWithContents('wpts_debug', 
			$config->getLoggerDirectory(), 
			$debugLogCount);

		return array(
			'error' => $erroLogFiles,
			'debug' => $debugLogFiles
		);
	}

	private function _createTestLogManagerWithDebugModeEnabled() {
		$config = abp01_get_logger_config();
		return new Abp01_Logger_Manager(
			new Abp01_Logger_Config(
				$config->getLoggerDirectory(), 
				$config->shouldRotateLogs(), 
				$config->getMaxLogFiles(), 
				true
			)
		);
	}
}