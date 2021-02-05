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

class UrlValidatorTests extends WP_UnitTestCase {
	public function test_tryValidateEmptyUrl_allowEmpty() {
		$validator = new Abp01_Validate_Url(true);
		$this->assertTrue($validator->validate(''));
		$this->assertTrue($validator->validate(null));
	}
	
	public function test_tryValidateEmptyUrl_disallowEmpty() {
		$validator = new Abp01_Validate_Url(false);
		$this->assertFalse($validator->validate(''));
		$this->assertFalse($validator->validate(null));
	}
	
	public function test_canValidateUrl_validUrls_defaultProtocols() {
		$validator = new Abp01_Validate_Url();
		
		$this->assertTrue($validator->validate('http://example.com'));
		$this->assertTrue($validator->validate('https://example.com/test.html'));
		$this->assertTrue($validator->validate('mailto:test@example.com'));
		$this->assertTrue($validator->validate('ftp://example.com/path/to/test.html'));
		$this->assertTrue($validator->validate('ftps://example.com/path/to/other/test.html'));
	}
	
	public function test_canValidateUrls_validUrlsWithUnsupportedProtocol_defaultProtocols() {
		$validator = new Abp01_Validate_Url();
		
		$this->assertFalse($validator->validate('svn://example.com/trunk'));
		$this->assertFalse($validator->validate('file:///usr/bin/templ'));
	}
	
	public function test_canValidateUrls_validUrls_anyProtocols() {
		$validator = new Abp01_Validate_Url(true, array());
		
		$this->assertTrue($validator->validate('http://example.com'));
		$this->assertTrue($validator->validate('https://example.com/test.html'));
		$this->assertTrue($validator->validate('mailto:test@example.com'));
		$this->assertTrue($validator->validate('ftp://example.com/path/to/test.html'));
		$this->assertTrue($validator->validate('ftps://example.com/path/to/other/test.html'));

		$this->assertTrue($validator->validate('svn://example.com/trunk'));
		$this->assertTrue($validator->validate('file:///usr/bin/templ'));
		$this->assertTrue($validator->validate('stuf://www.example.com'));
	}
	
	public function test_canValidateUrls_validUrls_specifiedProtocols() {
		$validator = new Abp01_Validate_Url(true, array('http://', 'https://', 'svn://'));
		
		$this->assertTrue($validator->validate('http://example.com'));
		$this->assertTrue($validator->validate('https://example.com/test.html'));
		$this->assertTrue($validator->validate('svn://example.com/trunk'));
		
		$this->assertFalse($validator->validate('file:///usr/bin/templ'));
		$this->assertFalse($validator->validate('stuf://www.example.com'));
		$this->assertFalse($validator->validate('mailto:test@example.com'));
		$this->assertFalse($validator->validate('ftp://example.com/path/to/test.html'));
		$this->assertFalse($validator->validate('ftps://example.com/path/to/other/test.html'));
	}

	public function test_canValidateUrls_invalidUrls_defaultProtocols() {
		$validator = new Abp01_Validate_Url();
		$this->_assertInvalidUrls($validator);
	}
	
	public function test_canValidateUrls_invalidUrls_anyProtocols() {
		$validator = new Abp01_Validate_Url(true, array());
		$this->_assertInvalidUrls($validator);
	}
	
	private function _assertInvalidUrls(Abp01_Validate_Url $validator) {
		$this->assertFalse($validator->validate('bogus-string'));
		$this->assertFalse($validator->validate('www.example.com'));
		$this->assertFalse($validator->validate('http://{0}.example.com'));
	}
}
