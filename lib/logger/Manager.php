<?php
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

if (!defined('ABP01_LOADED')) {
	exit;
}

class Abp01_Logger_Manager {
	/**
	 * @var Abp01_Logger_Config
	 */
	private $_config;

	/**
	 * @var Abp01_Logger
	 */
	private $_logger;

	public function __construct(Abp01_Logger_Config $config) {
		$this->_config = $config;
	}

	public function getLogger(): Abp01_Logger {
		if ($this->_logger === null) {
			$this->_ensureLoggerDirectory();
			$this->_createLogger();
		}

		return $this->_logger;
	}

	private function _createLogger(): void {
		$defaultLoggerClass = Abp01_Logger_MonologLogger::class;
		/**
		 * Filters the class used to create the plug-in logger.
		 *
		 * The initial value is Abp01_Logger_MonologLogger. A custom class must be a
		 * concrete implementation of Abp01_Logger and accept an Abp01_Logger_Config
		 * instance as its constructor argument. Unsupported or non-instantiable classes
		 * are ignored and the default logger is used instead.
		 *
		 * @since 0.3.1
		 * @category Log Management
		 * @unstable Susceptible to breaking changes due to PSR-4 migration
		 *
		 * @param string $loggerClass The logger implementation class name.
		 */
		$loggerClass = apply_filters('abp01_get_logger_class', $defaultLoggerClass);

		$implementsInterface = in_array(Abp01_Logger::class, 
			class_implements($loggerClass));

		if ($defaultLoggerClass !== $loggerClass && $implementsInterface) {
			$reflectionLoggerClass = new ReflectionClass($loggerClass);
			if (!$reflectionLoggerClass->isAbstract()) {
				try {
					$this->_logger = $reflectionLoggerClass->newInstanceArgs(array(
						$this->_config
					));
				} catch (Exception $exc) {
					error_log(
						'Invalid log error class: <' . $loggerClass .'>. ' . 
						'Could not create logger instance, will use default logger class. ' .
						'Error: ' . $exc->getMessage() 
							. PHP_EOL 
							. $exc->getTraceAsString()
					);
					$this->_logger = null;
				}
			} else {
				$this->_logger = null;
			}
		}

		if ($this->_logger === null) {
			$this->_logger = new Abp01_Logger_MonologLogger($this->_config);
		}
	}

	private function _ensureLoggerDirectory(): void {
		$dir = $this->_config->getLoggerDirectory();
		if (!is_dir($dir)) {
			@mkdir($dir);
		}
	}

	/**
	 * @return Abp01_Logger_FileInfo[]
	 */
	public function getLogFiles(): array {
		$dir = $this->_config->getLoggerDirectory();
		if (!is_dir($dir)) {
			return array();
		}

		$fileNames = scandir($dir);
		if ($fileNames === false) {
			return array();
		}

		$logFiles = array();

		foreach ($fileNames as $name) {
			if ($name == '.' || $name == '..') {
				continue;
			}

			$filePath = realpath($dir . '/' . $name);
			if ($this->_maybeLogFile($filePath)) {
				$logFile = new Abp01_Logger_FileInfo($filePath);
				if ($logFile->isDebugLogFile() 
					|| $logFile->isErrorLogFile()) {
					$logFiles[] = $logFile;
				}
			}
		}

		return $logFiles;
	}

	private function _maybeLogFile(?string $filePath): bool {
		if (empty($filePath)) {
			return false;
		}

		$filePathInfo = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		return !empty($filePathInfo) && $filePathInfo === 'log';
	}

	public function getLogFileById(?string $logFileId): Abp01_Logger_FileInfo|null {
		if (empty($logFileId)) {
			return null;
		}

		$logFiles = $this->getLogFiles();

		/**
		 * @var Abp01_Logger_FileInfo|null $foundFile
		 */
		$foundFile = null;
		foreach ($logFiles as $logFile) {
			if ($logFile->matchesId($logFileId)) {
				$foundFile = $logFile;
				break;
			}
		}

		return $foundFile;
	}

	public function deleteLogFileById(?string $logFileId): bool {
		if (empty($logFileId)) {
			return false;
		}

		$logFile = $this->getLogFileById($logFileId);
		if (empty($logFile)) {
			return false;
		}

		$filePath = $logFile->getFilePath();
		return @unlink($filePath);
	}

	public function isDebugLoggingEnabled(): bool {
		return $this->_config->isDebugLoggingEnabled();
	}

	public function isErrorLoggingEnabled(): bool {
		return $this->_config->isErrorLoggingEnabled();
	}

	public function getConfig(): Abp01_Logger_Config {
		return $this->_config;
	}
}