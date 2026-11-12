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
class UpdateTo033InstallerStepTests extends WP_UnitTestCase {
	use LookupDataTestHelpers;

	private array $_originalLookupData;

	private mixed $_originalVersion;

	protected function setUp(): void {
		// The installer uses its own connection and the lookup tables are MyISAM.
		// Restore explicit snapshots instead of starting a WordPress transaction.
		$this->_originalVersion = get_option('abp01.option.version');
		$this->_originalLookupData = $this->_readAllLookupData();
		$this->_clearLookupDataTablesOnly();
		$install = new Abp01_Installer_Service_InstallLookupData($this->_getEnv());
		$this->assertTrue($install->execute());
		global $wpdb;
		$wpdb->delete($this->_getEnv()->getLookupLangTableName(), array('lookup_lang' => 'de_DE'));
	}

	protected function tearDown(): void {
		$this->_clearLookupDataTablesOnly();
		$this->_restoreAllLookupData($this->_originalLookupData);
		update_option('abp01.option.version', $this->_originalVersion);
	}

	public function test_upgradeFrom032_addsMissingGermanTranslations(): void {
		$this->_runUpgrade();
		(new AssertExpectedLookupDataInstalled())->check();
	}

	public function test_upgrade_preservesExistingTranslationsAndLookupRows(): void {
		global $wpdb;
		$table = $this->_getEnv()->getLookupLangTableName();
		$easyId = $this->_findLookupId('difficultyLevel', 'Easy');
		$wpdb->insert($table, array('ID' => $easyId, 'lookup_lang' => 'de_DE', 'lookup_label' => 'Meine leichte Tour'));
		$wpdb->update($table, array('lookup_label' => 'Traducere personalizata'), array('ID' => $easyId, 'lookup_lang' => 'ro_RO'));
		$before = $this->_readAllLookupData();

		$this->_runUpgrade();

		$after = $this->_readAllLookupData();
		$this->assertEquals($before['lookup'], $after['lookup']);
		foreach ($before['lookupLang'] as $row) {
			$this->assertContains($row, $after['lookupLang']);
		}
		$this->assertCount(81, $after['lookupLang']);
	}

	public function test_upgrade_doesNotTranslateCustomCategoryWithSameLabel(): void {
		$lookup = new Abp01_Lookup();
		$custom = $lookup->createLookupItem(Abp01_Lookup::PATH_SURFACE_TYPE, 'Easy');
		$this->assertNotNull($custom);

		$this->_runUpgrade();

		$german = new Abp01_Lookup('de_DE');
		$this->assertFalse($german->hasLookupItemTranslation($custom->id));
		$this->assertCount(28, $this->_readAllLookupData()['lookup']);
	}

	public function test_upgrade_doesNotRestoreRenamedOrDeletedDefaults(): void {
		global $wpdb;
		$env = $this->_getEnv();
		$easyId = $this->_findLookupId('difficultyLevel', 'Easy');
		$mediumId = $this->_findLookupId('difficultyLevel', 'Medium');
		$wpdb->update($env->getLookupTableName(), array('lookup_label' => 'My renamed difficulty'), array('ID' => $easyId));
		$wpdb->delete($env->getLookupLangTableName(), array('ID' => $mediumId));
		$wpdb->delete($env->getLookupTableName(), array('ID' => $mediumId));
		$before = $this->_readAllLookupData()['lookup'];

		$this->_runUpgrade();

		$this->assertEquals($before, $this->_readAllLookupData()['lookup']);
		$german = new Abp01_Lookup('de_DE');
		$this->assertFalse($german->hasLookupItemTranslation($easyId));
		$this->assertFalse($german->hasLookupItemTranslation($mediumId));
	}

	public function test_upgrade_isIdempotentWhenRetried(): void {
		$this->_runUpgrade();
		$before = $this->_readAllLookupData();

		$this->_runUpgrade();

		$this->assertEquals($before, $this->_readAllLookupData());
	}

	public function test_currentVersion_doesNotReapplyMissingTranslations(): void {
		update_option('abp01.option.version', '0.3.3');
		$before = $this->_readAllLookupData();
		$update = new Abp01_Installer_Step_Update($this->_getEnv());

		$this->assertTrue($update->execute());

		$this->assertEquals($before, $this->_readAllLookupData());
	}

	public function test_upgrade_withNoLookupRows_doesNotRecreateDefaults(): void {
		$this->_clearLookupDataTablesOnly();

		$this->_runUpgrade();

		$this->assertEmpty($this->_readAllLookupData()['lookup']);
		$this->assertEmpty($this->_readAllLookupData()['lookupLang']);
	}

	private function _runUpgrade(): void {
		update_option('abp01.option.version', '0.3.2');
		$update = new Abp01_Installer_Step_Update($this->_getEnv());
		$this->assertTrue($update->execute());
		$this->assertSame('0.3.3', get_option('abp01.option.version'));
		$this->assertNull($update->getLastError());
	}

	private function _findLookupId(string $category, string $label): int {
		global $wpdb;
		$table = $this->_getEnv()->getLookupTableName();
		return (int) $wpdb->get_var($wpdb->prepare(
			'SELECT ID FROM ' . $table . ' WHERE lookup_category = %s AND lookup_label = %s',
			$category,
			$label));
	}
}

