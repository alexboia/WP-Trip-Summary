<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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


class DropDbTableInstallerServiceTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use DbTestHelpers;

	protected function setUp(): void { 
		$this->_dropTestDbTable();
	}

	private function _createTestTable() {
		$createSql = "CREATE TABLE `abp01_test_table` (
			`term_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(200) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
			`slug` VARCHAR(200) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
			`term_group` BIGINT(10) NOT NULL DEFAULT '0',
			PRIMARY KEY (`term_id`) USING BTREE,
			INDEX `name` (`name`(191)) USING BTREE,
			INDEX `slug` (`slug`(191)) USING BTREE
		)
		COLLATE='utf8mb4_unicode_ci'
		ENGINE=MyISAM
		AUTO_INCREMENT=290;";

		$this->_getDb()->rawQuery($createSql);
	}

	protected function tearDown(): void { 
		$this->_dropTestDbTable();
	}

	private function _dropTestDbTable() {
		$this->_dropTables($this->_getDb(), 
			'abp01_test_table');
	}

	public function test_canDrop() {
		$service = new Abp01_Installer_Service_DropDbTable($this->_getEnv());
		$service->execute('abp01_test_table');

		$this->assertFalse($this->_tableExists($this->_getDb(), 
			'abp01_test_table'));
	}
}