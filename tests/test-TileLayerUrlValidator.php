<?php
/**
 * Copyright (c) 2014-2016, Alexandru Boia
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *  - Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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