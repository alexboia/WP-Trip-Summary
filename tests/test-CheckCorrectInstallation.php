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

class CheckCorrectInstallation extends WP_UnitTestCase {
	use GenericTestHelpers;
	use LookupDataTestHelpers;
	use DbTestHelpers;

	public function test_dbTablesArePresent() {
		$env = $this->_getEnv();
		$checkTables = array(
			$env->getLookupTableName(),
			$env->getLookupLangTableName(),
			$env->getRouteDetailsTableName(),
			$env->getRouteTrackTableName(),
			$env->getRouteDetailsLookupTableName()
		);

		foreach ($checkTables as $checkTable) {
			$exists = $this->_tableExists($this->_getDb(), $checkTable);
			$this->assertTrue($exists, 'Table "' . $checkTable . '" does not exist!');
		}
	}

	public function test_storageDirectoriesArePresent() {
		$env = $this->_getEnv();

		$checkDirs = array(
			$env->getRootStorageDir(),
			$env->getCacheStorageDir(),
			$env->getTracksStorageDir()
		);

		foreach ($checkDirs as $dir) {
			$this->assertDirectoryIsReadable($dir);

			$guardIndexPhpFile = $dir . DIRECTORY_SEPARATOR . 'index.php';
			$this->assertFileIsReadable($guardIndexPhpFile);

			if ($dir != $env->getRootStorageDir()) {
				$guardHtaccessfile = $dir . DIRECTORY_SEPARATOR . '.htaccess';
				$this->assertFileIsReadable($guardHtaccessfile);
			}
		}
	}

	public function test_correctVersionNumber() {
		$expectedVersion = $this->_getEnv()->getVersion();
		$actualVersion = get_option(Abp01_Installer::OPT_VERSION);

		$this->assertEquals(ABP01_VERSION, 
			$expectedVersion);

		$this->assertEquals($expectedVersion, 
			$actualVersion);
	}

	public function test_initialLookupDataItemsArePresent() {
		$assert = new AssertExpectedLookupDataInstalled();
		$assert->check();
	}
 }