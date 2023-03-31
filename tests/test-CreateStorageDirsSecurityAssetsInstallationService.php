<?php

use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;

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

class CreateStorageDirsSecurityAssetsInstallationServiceTests extends WP_UnitTestCase {
	use TestDataFileHelpers;
	use AssertStringContains;

	protected function setUp(): void {
		$this->_ensurePluginTestDirectoriesRemoved();
		$this->_ensurePluginTestDirectoriesCreated();
	}

	protected function tearDown(): void {
		$this->_ensurePluginTestDirectoriesRemoved();
	}

	public function test_canCreate() {
		list($rootStorageDir, $tracksStorageDir, $cacheStorageDir) = 
			$this->_getTestPluginStorageDirectories();

		$service = new Abp01_Installer_Service_CreateStorageDirsSecurityAssets($rootStorageDir, 
			$tracksStorageDir, 
			$cacheStorageDir);

		$service->execute();

		$this->_assertIndexPhpFile($rootStorageDir, '../../../index.php');
		$this->_assertIndexPhpFile($tracksStorageDir, '../../../../index.php');
		$this->_assertIndexPhpFile($cacheStorageDir, '../../../../index.php');

		$this->_assertHtaccesFile($tracksStorageDir);
		$this->_assertHtaccesFile($cacheStorageDir);
	}

	private function _assertIndexPhpFile($directory, $expectedRedirect) {
		$file = $directory . '/index.php';
		$this->assertFileExists($file);

		$contents = file_get_contents($file);
		$this->assertNotEmpty($contents);

		$expectedContents = '<?php header("Location: ' . $expectedRedirect . '"); exit;';
		$this->assertEquals($expectedContents, $contents);
	}

	private function _assertHtaccesFile($directory) {
		$file = $directory . '/.htaccess';
		$this->assertFileExists($file);

		$contents = file_get_contents($file);
		$this->assertNotEmpty($contents);

		$expectedExcludedExtensions = array(
			'.cache',
			'.gpx',
			'.geojson',
			'.kml'
		);

		foreach ($expectedExcludedExtensions as $ee) {
			$filesMatchRule = '<FilesMatch "\\' . $ee . '">';
			$this->assertStringContainsString($filesMatchRule, $contents);
		}

		$this->assertEquals(count($expectedExcludedExtensions), 
			substr_count($contents, '</FilesMatch>'));

		$this->assertEquals(count($expectedExcludedExtensions), 
			substr_count($contents, 'order allow,deny'));

		$this->assertEquals(count($expectedExcludedExtensions), 
			substr_count($contents, 'deny from all'));
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
}