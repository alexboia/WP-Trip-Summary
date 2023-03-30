<?php

use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class GpxDocumentValidatorTests extends WP_UnitTestCase {
	use ExpectException;
	use TestDataFileHelpers;

	public function test_canValidate_validFile() {
		$validator = new Abp01_Validate_GpxDocument();
		foreach ($this->_getValidFiles() as $fileName) {
			$filePath = $this->_determineDataFilePath($fileName);
			$this->assertTrue($validator->validate($filePath));
		}
	}

	public function test_canValidate_invalidFile() {
		$validator = new Abp01_Validate_GpxDocument();
		foreach ($this->_getInvalidFiles() as $fileName) {
			$filePath = $this->_determineDataFilePath($fileName);
			$this->assertFalse($validator->validate($filePath));
		}
	}

	/**
	 * @expectedException \InvalidArgumentException 
	 */
	public function test_tryValidate_emptyFilePath() {
		$this->expectException(InvalidArgumentException::class);
		$validator = new Abp01_Validate_GpxDocument();
		$validator->validate('');
	}

	/**
	 * @expectedException \InvalidArgumentException 
	 */
	public function test_tryValidate_nullFilePath() {
		$this->expectException(InvalidArgumentException::class);
		$validator = new Abp01_Validate_GpxDocument();
		$validator->validate(null);
	}

	protected static function _getRootTestsDir() { 
		return __DIR__;
	}

	private function _getValidFiles() {
		return array(
			'test4-empty-utf8-bom.gpx',
			'test4-empty-utf8-wo-bom.gpx',
			'test5-empty-wmeta-wtrkroot-utf8-bom.gpx',
			'test5-empty-wmeta-wtrkroot-utf8-wo-bom.gpx',
			'test1-garmin-desktop-app-utf8-bom.gpx',
			'test1-garmin-desktop-app-utf8-wo-bom.gpx',
			'test3-bikemap-utf8-bom.gpx',
			'test3-bikemap-utf8-wo-bom.gpx'
		);
	}

	private function _getInvalidFiles() {
		return array(
			'test-empty-file.gpx',
			'test-inv1-jibberish.gpx',
			'test-inv3-jibberish-xmldeclonly.gpx'
		);
	}
}