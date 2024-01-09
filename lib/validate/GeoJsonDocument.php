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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Validate_GeoJsonDocument implements Abp01_Validate_File {
	const DEFAULT_TEST_BUFFER_LENGTH = 150;

	private $_testBufferLength;

	private $_multiByteSearchEnabled = false;

	private $_searchDefiningAttributes = array(
		'properties',
		'type',
		'geometry',
		'features',
		'coordinates',
		'geometries'
	);

	public function __construct($testBufferLength = self::DEFAULT_TEST_BUFFER_LENGTH) {
		if ($testBufferLength <= 0) {
			throw new InvalidArgumentException('Buffer length must be greater than 0');
		}

		$this->_testBufferLength = $testBufferLength;
		$this->_multiByteSearchEnabled = function_exists('mb_stripos') 
			&& function_exists('mb_strripos');
	}

	public function validate($filePath) {
		if (empty($filePath)) {
			throw new InvalidArgumentException('File path may not be empty');
		}

		if (!is_readable($filePath)) {
			return false;
		}

		$testBuffers = $this->_collectTestBuffers($filePath);
		if (empty($testBuffers)) {
			return false;
		}
	
		$isValid = $this->_beginBufferStartsWithJsonStart($testBuffers['beginBuffer'])
			&& $this->_endBufferEndsWithJsonEnd($testBuffers['endBuffer'])
			&& $this->_combinedBuffersContainAnyDefiningAttribute($testBuffers['beginBuffer'], $testBuffers['endBuffer']);

		return $isValid;
	}

	private function _collectTestBuffers($filePath) {
		$beginBuffer = null;
		$endBuffer = null;

		$testFilePointer = @fopen($filePath, 'rb');
		if (!$testFilePointer) {
			return null;
		}

		$beginBuffer = @fread($testFilePointer, $this->_testBufferLength);

		if ($beginBuffer) {
			$fileSize = filesize($filePath);
			if ($fileSize > $this->_testBufferLength) {
				if (@fseek($testFilePointer, -$this->_testBufferLength, SEEK_END) === 0) {
					$endBuffer = @fread($testFilePointer, $this->_testBufferLength);
				}
			} else {
				$endBuffer = $beginBuffer;
			}
		}

		@fclose($testFilePointer);
		return !empty($beginBuffer) && !empty($endBuffer)
			? compact('beginBuffer', 'endBuffer')
			: null;
	}

	private function _beginBufferStartsWithJsonStart($beginBuffer) {
		$beginBuffer = trim($beginBuffer);
		if ($this->_multiByteSearchEnabled) {
			$jsonStartPos = mb_stripos($beginBuffer, '{', 0, 'UTF-8');
		} else {
			$jsonStartPos = stripos($beginBuffer, '{', 0);
		}

		return $jsonStartPos !== false 
			&& $jsonStartPos >= 0 
			&& $jsonStartPos <= 5;
	}

	private function _endBufferEndsWithJsonEnd($endBuffer) {
		$endBufferLength = 0;
		$endBuffer = trim($endBuffer);

		if ($this->_multiByteSearchEnabled) {
			$jsonEndPos = mb_strripos($endBuffer, '}', 0, 'UTF-8');
			$endBufferLength = mb_strlen($endBuffer, 'UTF-8');
		} else {
			$jsonEndPos = strripos($endBuffer, '}', 0);
			$endBufferLength = strlen($endBuffer);
		}

		return $jsonEndPos !== false 
			&& $jsonEndPos == ($endBufferLength - 1);
	}

	private function _combinedBuffersContainAnyDefiningAttribute($beginBuffer, $endBuffer) {
		$hasAnyDefiningAttribute = false;
		$combinedBuffers = $beginBuffer . $endBuffer;

		foreach ($this->_searchDefiningAttributes as $attr) {
			$searchAttrString = '"' . $attr . '"';
			
			if ($this->_multiByteSearchEnabled) {
				$attrIndex = mb_stripos($combinedBuffers, $searchAttrString, 0, 'UTF-8');
			} else {
				$attrIndex = stripos($combinedBuffers, $searchAttrString, 0);
			}

			if ($attrIndex !== false) {
				$hasAnyDefiningAttribute = true;
				break;
			}
		}

		return $hasAnyDefiningAttribute;
	}
}