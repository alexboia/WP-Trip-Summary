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

use PHPUnit\Framework\TestCase;
use WpTripSummary\Io\NginxAccessDirectives;

class NginxAccessDirectivesTests extends TestCase {
	public function test_canGenerateDenyAccessRules_withDefaults(): void {
		$rules = NginxAccessDirectives::generateDenyAccessRules('/wp-content/uploads/wp-trip-summary');

		$this->_assertRule($rules, 
			'=', 
			'/wp-content/uploads/wp-trip-summary', 
			403);
		$this->_assertRule($rules, 
			'^~', 
			'/wp-content/uploads/wp-trip-summary/', 
			403);
		$this->assertSame(2, 
			preg_match_all('/^location /m', 
				$rules));
	}

	public function test_canGenerateDenyAccessRules_withoutChildren(): void {
		$rules = NginxAccessDirectives::generateDenyAccessRules('/private/', 
			404, 
			false);

		$this->_assertRule($rules, 
			'=', '/private', 
			404);
		$this->assertSame(1, 
			preg_match_all('/^location /m', 
				$rules));
		$this->assertStringNotContainsString('^~', 
			$rules);
	}

	/**
	 * @dataProvider provideUrlPaths
	 */
	public function test_canGenerateDenyAccessRules_normalizesAndEscapesPaths(string $urlPath, string $expectedPath): void {
		$rules = NginxAccessDirectives::generateDenyAccessRules($urlPath);

		$this->_assertRule($rules, 
			'=', 
			$expectedPath, 
			403);
		$this->_assertRule($rules, 
			'^~', 
			$expectedPath . '/', 
			403);
		$this->assertSame(2, 
			preg_match_all('/^location /m', 
				$rules));
	}

	public static function provideUrlPaths(): array {
		return array(
			'plain path' => array('/private', '/private'),
			'trailing slash' => array('/private/', '/private'),
			'multiple trailing slashes' => array('/private///', '/private'),
			'surrounding spaces' => array('  /private/  ', '/private'),
			'WordPress in subdirectory' => array('/blog/wp-content/uploads/wp-trip-summary', '/blog/wp-content/uploads/wp-trip-summary'),
			'multisite uploads' => array('/wp-content/uploads/sites/2/wp-trip-summary/', '/wp-content/uploads/sites/2/wp-trip-summary'),
			'literal spaces' => array('/private files', '/private files'),
			'encoded spaces' => array('/private%20files', '/private files'),
			'encoded trailing space' => array('/private%20', '/private '),
			'encoded slash' => array('/private%2Ffiles%2F', '/private/files'),
			'literal plus' => array('/private+files', '/private+files'),
			'encoded plus' => array('/private%2Bfiles', '/private+files'),
			'literal apostrophe' => array("/John's files", "/John\\'s files"),
			'encoded apostrophe' => array('/John%27s%20files', "/John\\'s files"),
			'literal backslash' => array('/private\\files', '/private\\\\files'),
			'encoded backslash' => array('/private%5Cfiles', '/private\\\\files'),
			'consecutive backslashes' => array('/private\\\\files', '/private\\\\\\\\files'),
			'backslash before apostrophe' => array("/private\\'files", "/private\\\\\\'files"),
			'backslash before letter n' => array('/private\\new', '/private\\\\new'),
			'trailing backslash' => array('/private\\', '/private\\\\'),
			'double quotes' => array('/private"files"', '/private"files"'),
			'configuration punctuation' => array('/private;{#}', '/private;{#}'),
			'encoded configuration punctuation' => array('/private%3B%7B%23%7D', '/private;{#}'),
			'decode percent encoding once' => array('/private%2520files', '/private%20files'),
			'encoded percent sign' => array('/private%25files', '/private%files'),
			'UTF-8 path' => array('/trasee/munți', '/trasee/munți'),
			'encoded UTF-8 path' => array('/trasee/mun%C8%9Bi', '/trasee/munți'),
			'hidden directory' => array('/private/.cache', '/private/.cache')
		);
	}

	/**
	 * @dataProvider provideRootPaths
	 */
	public function test_canGenerateDenyAccessRules_forRoot(string $urlPath, bool $includeChildren): void {
		$rules = NginxAccessDirectives::generateDenyAccessRules($urlPath, 
			403, 
			$includeChildren);

		$this->_assertRule($rules, 
			'=', 
			'/', 
			403);
		$this->assertSame($includeChildren ? 2 : 1, 
			preg_match_all('/^location /m', 
				$rules));
		
		if ($includeChildren) {
			$this->_assertRule($rules, '^~', '/', 403);
		}
		
		$this->assertStringNotContainsString("''", $rules);
		$this->assertStringNotContainsString("'//'", $rules);
	}

	public static function provideRootPaths(): array {
		return array(
			'root with children' => array('/', true),
			'root without children' => array('/', false),
			'repeated slash root with children' => array('///', true),
			'repeated slash root without children' => array('///', false),
			'encoded root with children' => array('%2F', true),
			'encoded root without children' => array('%2F', false)
		);
	}

	/**
	 * @dataProvider provideHttpCodes
	 */
	public function test_canGenerateDenyAccessRules_withHttpCode(int $httpCode, int $expectedCode): void {
		$rules = NginxAccessDirectives::generateDenyAccessRules('/private', 
			$httpCode);

		$this->_assertRule($rules, 
			'=', 
			'/private', 
			$expectedCode);
		$this->_assertRule($rules, 
			'^~', 
			'/private/', 
			$expectedCode);
	}

	public static function provideHttpCodes(): array {
		return array(
			'forbidden' => array(403, 403),
			'not found' => array(404, 404),
			'gone' => array(410, 410),
			'Nginx connection close' => array(444, 444),
			'server error' => array(503, 503),
			'Nginx maximum return code' => array(999, 999),
			'zero falls back to forbidden' => array(0, 403),
			'negative falls back to forbidden' => array(-1, 403),
			'minimum integer falls back to forbidden' => array(PHP_INT_MIN, 403),
			'out of range falls back to forbidden' => array(1000, 403),
			'maximum integer falls back to forbidden' => array(PHP_INT_MAX, 403)
		);
	}

	/**
	 * @dataProvider provideInvalidUrlPaths
	 */
	public function test_cannotGenerateDenyAccessRules_withInvalidPath(string $urlPath): void {
		$this->expectException(InvalidArgumentException::class);

		NginxAccessDirectives::generateDenyAccessRules($urlPath);
	}

	public static function provideInvalidUrlPaths(): array {
		return array(
			'empty path' => array(''),
			'blank path' => array('   '),
			'relative path' => array('private/files'),
			'full URL' => array('https://example.com/private'),
			'encoded leading space' => array('%20/private'),
			'literal null byte' => array("/private\0files"),
			'trailing null byte' => array("/private\0"),
			'encoded null byte' => array('/private%00files'),
			'literal newline' => array("/private\nfiles"),
			'encoded newline' => array('/private%0Afiles'),
			'encoded trailing newline' => array('/private%0A'),
			'literal carriage return' => array("/private\rfiles"),
			'encoded carriage return' => array('/private%0Dfiles'),
			'literal tab' => array("/private\tfiles"),
			'encoded tab' => array('/private%09files'),
			'encoded delete control character' => array('/private%7Ffiles'),
			'current directory segment' => array('/private/./files'),
			'parent directory segment' => array('/private/../files'),
			'encoded parent directory segment' => array('/private/%2E%2E/files'),
			'trailing parent directory segment' => array('/private/..'),
			'noncanonical internal slashes' => array('/private//files')
		);
	}

	private function _assertRule(string $rules, string $modifier, string $expectedPath, int $httpCode): void {
		// Check executable directives without depending on comments or indentation.
		$pattern = '~^location[ \t]+' . preg_quote($modifier, '~') .
			"[ \t]+'" . preg_quote($expectedPath, '~') .
			"'[ \t]*\\{\\r?\\n[ \t]*return $httpCode;\\r?\\n\\}~m";

		$this->assertMatchesRegularExpression($pattern, $rules);
	}
}
