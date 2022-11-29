<?php
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

class GeoJsonDocumentValidatorTests extends WP_UnitTestCase {
	use TestDataFileHelpers;

	public function test_canValidate_validFile() {
		$validator = new Abp01_Validate_GeoJsonDocument();
		foreach ($this->_getValidFiles() as $fileName) {
			$filePath = $this->_determineDataFilePath($fileName);
			$this->assertTrue($validator->validate($filePath));
		}
	}

	public function test_canValidate_invalidFile() {
		$validator = new Abp01_Validate_GeoJsonDocument();
		foreach ($this->_getInvalidFiles() as $fileName) {
			$filePath = $this->_determineDataFilePath($fileName);
			$this->assertFalse($validator->validate($filePath));
		}
	}

	/**
	 * @expectedException \InvalidArgumentException 
	 */
	public function test_tryValidate_emptyFilePath() {
		$validator = new Abp01_Validate_GeoJsonDocument();
		$validator->validate('');
	}

	/**
	 * @expectedException \InvalidArgumentException 
	 */
	public function test_tryValidate_nullFilePath() {
		$validator = new Abp01_Validate_GeoJsonDocument();
		$validator->validate(null);
	}

	protected static function _getRootTestsDir() { 
		return __DIR__;
	}

	private function _getValidFiles() {
		return array(
			'geojson/test1-bikemap-utf8-bom.geojson',
			'geojson/test3-empty-object-featurecollection-utf8-bom.geojson',
			'geojson/test4-empty-object-featurecollection-utf8-wo-bom.geojson',
			'geojson/test5-simple-shapes-featurecollection-nometa-utf8-bom.geojson',
			'geojson/test6-simple-shapes-featurecollection-nometa-utf8-wo-bom.geojson'
		);
	}

	private function _getInvalidFiles() {
		return array(
			'geojson/test-inv1-jibberish.geojson',
			'geojson/test-inv3-jibberish-noendbrace.geojson'
		);
	}
}