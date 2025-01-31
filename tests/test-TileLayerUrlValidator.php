<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class TileLayerUrlValidatorTests extends WP_UnitTestCase {
	public function test_tryValidateEmptyUrl() {
		$validator = new Abp01_Validate_TileLayerUrl();
		foreach ($this->_getEmptyTileLayerUrls() as $emptyTileLayerUrl) {
			$this->assertFalse($validator->validate($emptyTileLayerUrl));	
		}
	}

	private function _getEmptyTileLayerUrls() {
		return array(
			'',
			null,
			false
		);
	}

	public function test_canValidateUrl_validTileLayerUrl() {
		$validator = new Abp01_Validate_TileLayerUrl();
		foreach ($this->_getValidTileLayerUrls() as $validTileLayerUrl)  {
			$this->assertTrue($validator->validate($validTileLayerUrl));	
		}
	}

	private function _getValidTileLayerUrls() {
		return array(
			'http://example.com/{z}/{x}/{y}',
			'http://{s}.example.com/{z}/{x}/{y}',
			'https://tiles.example.com/server/{z}/{x}/{y}',
			'https://{s}.tiles.example.com/server/{z}/{x}/{y}',
			'ftp://example.com/{z}/{x}/{y}',
			'ftp://{s}.example.com/{z}/{x}/{y}',
			'ftps://tiles.example.com/server/{z}/{x}/{y}',
			'ftps://{s}.tiles.example.com/server/{z}/{x}/{y}',
			'http://{s}.tiles.example.com/{id}/server/{z}/{x}/{y}',
			'https://{s}.tiles.example.com/{id}/server/{z}/{x}/{y}'
		);
	}

	public function test_canValidateUrl_invalidTileLayerUrl() {
		$validator = new Abp01_Validate_TileLayerUrl();
		foreach ($this->_getInvalidTileLayerUrls() as $invalidTileLayerUrl) {
			$this->assertFalse($validator->validate($invalidTileLayerUrl));	
		}
	}

	private function _getInvalidTileLayerUrls() {
		return array(
			'http://example.com/tiles/server',
			'http://example.com/tiles/server/{x}/{y}',
			'svn://{s}.tiles.example.com/server/{z}/{x}/{y}',
			'www.example.com/server/{z}/{x}/{y}',
			'bogus-url-server-{z}-{x}-{y}'			
		);
	}
}