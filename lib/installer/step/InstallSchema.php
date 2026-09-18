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

use WpTripSummary\Env;

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Installer_Step_InstallSchema implements Abp01_Installer_Step {
	private Env $_env;

	private Exception|\WP_Error|null $_lastError;

	private array $_onlyTables = array();

	private Abp01_Installer_Table_Definitions $_tableDefs;

	public function __construct(Env $env) {
		$this->_env = $env;
		$this->_tableDefs = new Abp01_Installer_Table_Definitions($env);
	}

	public function execute(): bool { 
		$this->_lastError = null;
		return $this->_installSchema();
	}

	private function _installSchema(): bool {
		$result = true;
		$tables = $this->_getTablesToInstall();

		foreach ($tables as $tableName => $tableDef) {
			$result = $result && $this->_createTable($tableName, $tableDef);
		}

		return $result;
	}

	public function getLastError(): Exception|WP_Error|null { 
		return $this->_lastError;	
	}

	public function onlyTables(array $tableNames): Abp01_Installer_Step_InstallSchema {
		$this->_onlyTables = $tableNames;
		return $this;
	}

	private function _getTablesToInstall(): array {
		$ownTables = array(
			$this->_getLookupTableName() 
				=> $this->_getLookupTableDefinition(),
			$this->_getLookupLangTableName() 
				=> $this->_getLookupLangTableDefinition(),
			$this->_getRouteDetailsTableName()
				 => $this->_getRouteDetailsTableDefinition(),
			$this->_getRouteTrackTableName() 
				=> $this->_getRouteTrackTableDefinition(),
			$this->_getRouteDetailsLookupTableName() 
				=> $this->_getRouteDetailsLookupTableDefinition(),
			$this->_getRouteLogTableName()
				=> $this->_getRouteLogTableDefinition()
		);

		/**
		 * Filters custom database table definitions installed alongside the built-in tables.
		 * Initial value is an empty array. Each entry must use the table name as its key
		 * and a CREATE TABLE statement as its value.
		 *
		 * Non-array results are discarded. Built-in definitions are merged afterwards,
		 * so a custom definition cannot override a built-in table with the same name.
		 *
		 * @since 0.3.0
		 * @category Installer
		 *
		 * @param array<string, string> $customTables Custom table definitions keyed by table name.
		 * @param array<string, string> $ownTables Built-in table definitions keyed by table name.
		 */
		$customTables = apply_filters('abp01_install_tables_definitions',
			array(),
			$ownTables);

		if (!is_array($customTables)) {
			$customTables = array();
		}

		$finalTables = array_merge($customTables, 
			$ownTables);

		if (!empty($this->_onlyTables)) {
			$finalTables = array_filter(
				$finalTables, 
				function($tableName) {
					return in_array($tableName, $this->_onlyTables);
				}, 
				ARRAY_FILTER_USE_KEY
			);
		}

		return $finalTables;
	}

	private function _createTable(string $tableName, string $tableDef): bool {
		$result = false;
		try {
			$service = new Abp01_Installer_Service_CreateDbTable($this->_env);
			$result = $service->execute($tableName, $tableDef);
		} catch (Exception $exc) {
			$this->_lastError = $exc;
		}
		return $result;
	}

	private function _getRouteTrackTableDefinition(): string {
		return $this->_tableDefs->getRouteTrackTableDefinition();
	}

	private function _getRouteDetailsTableDefinition(): string {
		return $this->_tableDefs->getRouteDetailsTableDefinition();
	}

	private function _getLookupLangTableDefinition(): string {
		return $this->_tableDefs->getLookupLangTableDefinition();
	}

	private function _getLookupTableDefinition(): string {
		return $this->_tableDefs->getLookupTableDefinition();
	}

	private function _getRouteDetailsLookupTableDefinition(): string {
		return $this->_tableDefs->getRouteDetailsLookupTableDefinition();
	}

	private function _getRouteLogTableDefinition(): string {
		return $this->_tableDefs->getRouteLogTableDefinition();
	}

	private function _getRouteTrackTableName(): string {
		return $this->_env->getRouteTrackTableName();
	}

	private function _getRouteDetailsTableName(): string {
		return $this->_env->getRouteDetailsTableName();
	}

	private function _getLookupLangTableName(): string {
		return $this->_env->getLookupLangTableName();
	}

	private function _getLookupTableName(): string {
		return $this->_env->getLookupTableName();
	}

	private function _getRouteDetailsLookupTableName(): string {
		return $this->_env->getRouteDetailsLookupTableName();
	}

	private function _getRouteLogTableName(): string {
		return $this->_env->getRouteLogTableName();
	}
}
