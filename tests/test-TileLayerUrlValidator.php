<?php
class TileLayerUrlValidatorTests extends WP_UnitTestCase {
	public function testTryValidateEmptyUrl() {
		$validator = new Abp01_Validate_TileLayerUrl();
		$this->assertFalse($validator->validate(''));
		$this->assertFalse($validator->validate(null));
		$this->assertFalse($validator->validate(false));
	}

	public function testCanValidateUrl_validTileLayerUrl() {
		$validator = new Abp01_Validate_TileLayerUrl();
		$this->assertTrue($validator->validate('http://example.com/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('http://{s}.example.com/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('https://tiles.example.com/server/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('https://{s}.tiles.example.com/server/{z}/{x}/{y}'));
		
		$this->assertTrue($validator->validate('ftp://example.com/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('ftp://{s}.example.com/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('ftps://tiles.example.com/server/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('ftps://{s}.tiles.example.com/server/{z}/{x}/{y}'));

		$this->assertTrue($validator->validate('http://{s}.tiles.example.com/{id}/server/{z}/{x}/{y}'));
		$this->assertTrue($validator->validate('https://{s}.tiles.example.com/{id}/server/{z}/{x}/{y}'));
	}

	public function testCanValidateUrl_invalidTileLayerUrl() {
		$validator = new Abp01_Validate_TileLayerUrl();
		$this->assertFalse($validator->validate('http://example.com/tiles/server'));
		$this->assertFalse($validator->validate('http://example.com/tiles/server/{x}/{y}'));
		$this->assertFalse($validator->validate('svn://{s}.tiles.example.com/server/{z}/{x}/{y}'));
		$this->assertFalse($validator->validate('www.example.com/server/{z}/{x}/{y}'));
		$this->assertFalse($validator->validate('bogus-url-server-{z}-{x}-{y}'));
	}
}