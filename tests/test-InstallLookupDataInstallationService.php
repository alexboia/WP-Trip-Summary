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

class InstallLookupDataInstallationServiceTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use DbTestHelpers;

	protected function setUp(): void {
		$this->_startTransactionalTest();
		$this->_clearLookupData();
	}

	private function _startTransactionalTest() {
		$this->_getDb()->startTransaction();
	}

	private function _clearLookupData() {
		$env = $this->_getEnv();
		$clearTables = array(
			$env->getLookupLangTableName(),
			$env->getLookupTableName()
		);

		foreach ($clearTables as $tableName) {
			$this->_truncateTables($this->_getDb(), $tableName);
		}
	}

	protected function tearDown(): void {
		$this->_endTransactionalTest();
	}

	private function _endTransactionalTest() {
		$this->_getDb()->rollback();
	}

	public function test_canInstallLookupData() {
		$env = $this->_getEnv();
		$installLookupData = new Abp01_Installer_Service_InstallLookupData($env);
		$installLookupDataTranlations = new Abp01_Installer_Service_InstallLookupDataTranslationsForLanguage($env);

		$result = $installLookupData->execute();
		$this->assertTrue($result);

		$resultRo = $installLookupDataTranlations->execute('ro_RO');
		$this->assertTrue($resultRo);

		$resultFr = $installLookupDataTranlations->execute('fr_FR');
		$this->assertTrue($resultFr);

		$assertLookupDataExists = new AssertExpectedLookupDataInstalled();
		$assertLookupDataExists->check();
	}
}