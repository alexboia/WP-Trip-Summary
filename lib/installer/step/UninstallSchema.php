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

if (!defined('ABP01_LOADED')) {
	exit;
}

class Abp01_Installer_Step_UninstallSchema implements Abp01_Installer_Step {
	private Env $_env;

	private Exception|\WP_Error|null $_lastError;

	public function __construct(Env $env) {
		$this->_env = $env;
	}

    public function execute(): bool { 
		$this->_lastError = null;
		return $this->_uninstallSchema();
	}

	private function _uninstallSchema(): bool {
		$result = true;
		$tables = $this->_getTablesToUninstall();

		foreach ($tables as $tableName) {
			$result = $result && $this->_dropTable($tableName);
		}

		return $result;
	}

    public function getLastError(): Exception|WP_Error|null { 
		return $this->_lastError;
	}

	private function _getTablesToUninstall(): array {
		$ownTables = array(
			$this->_getRouteDetailsLookupTableName(),
			$this->_getRouteDetailsTableName(),
			$this->_getRouteTrackTableName(),
			$this->_getLookupLangTableName(),
			$this->_getLookupTableName(),
			$this->_getRouteLogTableName()
		);

		/**
		 * Filters custom database table names dropped during uninstallation.
		 * Initial value is an empty array. Return an associative array whose values are
		 * table names.
		 *
		 * Custom tables are dropped in the order provided.
		 * Empty or non-array results are ignored, and built-in tables 
		 * are always appended to the final list, 
		 * so they are always dropped from the databasae.
		 *
		 * @since 0.3.3
		 * @category Installer
		 *
		 * @param array<string, mixed> $customTables Custom table names.
		 * @param string[] $ownTables Built-in table names in drop order.
		 */
		$customTables = apply_filters('abp01_uninstall_table_names',
			array(),
			$ownTables);

		if (!is_array($customTables)) {
			$customTables = array();
		}

		$customTables = array_filter($customTables, fn(mixed $tableName): bool 
			=> !empty($tableName) && is_string($tableName));

		$finalTables = array_merge($customTables, $ownTables);		
		return $finalTables;
	}

	private function _dropTable(string $tableName): bool {
		$result = false;

		try {
			$service = new Abp01_Installer_Service_DropDbTable($this->_env);
			$service->execute($tableName);
			$result = true;
		} catch (Exception $exc) {
			$this->_lastError = $exc;
		}

		return $result;
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
