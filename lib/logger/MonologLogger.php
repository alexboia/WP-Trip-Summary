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

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Logger_MonologLogger implements Abp01_Logger {
	private $_logger = null;

	private $_config;

	public function __construct(Abp01_Logger_Config $config) {
		$this->_config = $config;
	}
	
	private function _getOrCreateLogger(): Logger {
		if ($this->_logger === null) {
			$logger = new Logger($this->_config->getLoggerName());
			$lineFormatter = new LineFormatter(null, null, true, true, true);
			
			if ($this->_config->shouldRotateLogs()) {
				$this->_createRotatingLogHandlers($logger, $lineFormatter);
			} else {
				$this->_createSimpleLogHandlers($logger, $lineFormatter);
			}

			if ($this->_config->shouldCaptureWebContext()) {
				$logger->pushProcessor(new WebProcessor());
			}

			if ($this->_config->isDebugMode()) {
				$logger->pushHandler(new BrowserConsoleHandler());
			}

			do_action('abp01_customize_monolog_logger', $logger);		
			$this->_logger = $logger;
		}

		return $this->_logger;
	}

	private function _createRotatingLogHandlers(Logger $logger, FormatterInterface $formatter) {
		if ($this->_config->isDebugLoggingEnabled()) {
			$debugRotatingFileHandler = new RotatingFileHandler($this->_config->getDebugLogFile(), 
				$this->_config->getMaxLogFiles(), 
				Logger::DEBUG);

			$debugRotatingFileHandler->setFormatter($formatter);
			$logger->pushHandler($debugRotatingFileHandler);
		}

		if ($this->_config->isErrorLoggingEnabled()) {
			$errorRotatingFileHandler = new RotatingFileHandler($this->_config->getErrorLogFile(), 
				$this->_config->getMaxLogFiles(), 
				Logger::WARNING, 
				false);

			$errorRotatingFileHandler->setFormatter($formatter);
			$logger->pushHandler($errorRotatingFileHandler);
		}
	}

	private function _createSimpleLogHandlers(Logger $logger, FormatterInterface $formatter) {
		if ($this->_config->isDebugLoggingEnabled()) {
			$debugStreamFileHandler = new StreamHandler($this->_config->getDebugLogFile(),  
				Logger::DEBUG);

			$debugStreamFileHandler->setFormatter($formatter);
			$logger->pushHandler($debugStreamFileHandler);
		}

		if ($this->_config->isErrorLoggingEnabled()) {
			$errorStreamFileHandler = new StreamHandler($this->_config->getErrorLogFile(), 
				Logger::WARNING);

			$errorStreamFileHandler->setFormatter($formatter);
			$logger->pushHandler($errorStreamFileHandler);
		}
	}
	
	public function emergency(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->emergency($message, $context);
	}

	public function alert(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->alert($message, $context);
	}

	public function critical(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->critical($message, $context);
	}

	public function error(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->error($message, $context);
	}

	public function warning(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->warning($message, $context);
	}

	public function notice(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->notice($message, $context);
	}

	public function info(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->info($message, $context);
	}

	public function debug(string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->debug($message, $context);
	}

	public function log($level, string|Stringable $message, array $context = []): void { 
		$this->_getOrCreateLogger()->log($level, $message, $context);
	}	

	public function exception(string $message, Exception $exception, array $context = []): void {
		$this->_getOrCreateLogger()->error($message, array_merge($context, array(
			'exception' => array(
				'type' => get_class($exception),
				'code' => $exception->getCode(),
				'message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTraceAsString()
			)
		)));
	}
}