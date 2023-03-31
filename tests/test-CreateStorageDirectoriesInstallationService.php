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

class CreateStorageDirectoriesInstallationServiceTests extends WP_UnitTestCase {
	use TestDataFileHelpers;

	protected function setUp(): void {
		$this->_ensureTestDirectoriesRemoved();
	}

	private function _ensureTestDirectoriesRemoved() {
		list($rootStorageDir, $tracksStorageDir, $cacheStorageDir) = 
			$this->_getTestStorageDirectories();

		if (is_dir($cacheStorageDir)) {
			@rmdir($cacheStorageDir);
		}

		if (is_dir($tracksStorageDir)) {
			@rmdir($tracksStorageDir);
		}

		if (is_dir($rootStorageDir)) {
			@rmdir($rootStorageDir);
		}
	}

	private function _getTestStorageDirectories() {
		$rootTestDataDir = $this->_determineTestDataDir();
		$rootStorageDir = $rootTestDataDir . '/storage';
		$tracksStorageDir = $rootStorageDir . '/tracks';
		$cacheStorageDir = $rootStorageDir . '/cache';

		return array($rootStorageDir, 
			$tracksStorageDir, 
			$cacheStorageDir);
	}

	protected function tearDown(): void {
		$this->_ensureTestDirectoriesRemoved();
	}

	public function test_canCreateDirectories() {
		list($rootStorageDir, $tracksStorageDir, $cacheStorageDir) = 
			$this->_getTestStorageDirectories();

		$service = new Abp01_Installer_Service_CreateStorageDirectories($rootStorageDir, 
			$tracksStorageDir, 
			$cacheStorageDir);

		$service->execute();

		$this->assertDirectoryExists($rootStorageDir);
		$this->assertDirectoryExists($tracksStorageDir);
		$this->assertDirectoryExists($cacheStorageDir);
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
}