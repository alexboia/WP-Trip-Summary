<?php
class UrlValidatorTests extends WP_UnitTestCase {
	public function testTryValidateEmptyUrl_allowEmpty() {
		$validator = new Abp01_Validate_Url(true);
		$this->assertTrue($validator->validate(''));
		$this->assertTrue($validator->validate(null));
	}
	
	public function testTryValidateEmptyUrl_disallowEmpty() {
		$validator = new Abp01_Validate_Url(false);
		$this->assertFalse($validator->validate(''));
		$this->assertFalse($validator->validate(null));
	}
	
	public function testCanValidateUrl_validUrls_defaultProtocols() {
		$validator = new Abp01_Validate_Url();
		
		$this->assertTrue($validator->validate('http://example.com'));
		$this->assertTrue($validator->validate('https://example.com/test.html'));
		$this->assertTrue($validator->validate('mailto:test@example.com'));
		$this->assertTrue($validator->validate('ftp://example.com/path/to/test.html'));
		$this->assertTrue($validator->validate('ftps://example.com/path/to/other/test.html'));
	}
	
	public function testCanValidateUrls_validUrlsWithUnsupportedProtocol_defaultProtocols() {
		$validator = new Abp01_Validate_Url();
		
		$this->assertFalse($validator->validate('svn://example.com/trunk'));
		$this->assertFalse($validator->validate('file:///usr/bin/templ'));
	}
	
	public function testCanValidateUrls_validUrls_anyProtocols() {
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
	
	public function testCanValidateUrls_validUrls_specifiedProtocols() {
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

	public function testCanValidateUrls_invalidUrls_defaultProtocols() {
		$validator = new Abp01_Validate_Url();
		$this->_assertInvalidUrls($validator);
	}
	
	public function testCanValidateUrls_invalidUrls_anyProtocols() {
		$validator = new Abp01_Validate_Url(true, array());
		$this->_assertInvalidUrls($validator);
	}
	
	private function _assertInvalidUrls(Abp01_Validate_Url $validator) {
		$this->assertFalse($validator->validate('bogus-string'));
		$this->assertFalse($validator->validate('www.example.com'));
		$this->assertFalse($validator->validate('http://{0}.example.com'));
	}
}
