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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Installer_Step_InstallSchema implements Abp01_Installer_Step {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var Exception|\WP_Error|null
	 */
	private $_lastError;

	private $_onlyTables = array();

	/**
	 * @var Abp01_Installer_Table_Definitions
	 */
	private $_tableDefs;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
		$this->_tableDefs = new Abp01_Installer_Table_Definitions($env);
	}

	public function execute() { 
		$this->_lastError = null;
		return $this->_installSchema();
	}

	private function _installSchema() {
		$result = true;
		$tables = $this->_getTablesToInstall();

		foreach ($tables as $tableName => $tableDef) {
			$result = $result && $this->_createTable($tableName, $tableDef);
		}

		return $result;
	}

	public function getLastError() { 
		return $this->_lastError;	
	}

	public function onlyTables(array $tableNames) {
		$this->_onlyTables = $tableNames;
		return $this;
	}

	private function _getTablesToInstall() {
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

	private function _createTable($tableName, $tableDef) {
		$result = false;
		try {
			$service = new Abp01_Installer_Service_CreateDbTable($this->_env);
			$result = $service->execute($tableName, $tableDef);
		} catch (Exception $exc) {
			$this->_lastError = $exc;
		}
		return $result;
	}

	private function _getRouteTrackTableDefinition() {
		return $this->_tableDefs->getRouteTrackTableDefinition();
	}

	private function _getRouteDetailsTableDefinition() {
		return $this->_tableDefs->getRouteDetailsTableDefinition();
	}

	private function _getLookupLangTableDefinition() {
		return $this->_tableDefs->getLookupLangTableDefinition();
	}

	private function _getLookupTableDefinition() {
		return $this->_tableDefs->getLookupTableDefinition();
	}

	private function _getRouteDetailsLookupTableDefinition() {
		return $this->_tableDefs->getRouteDetailsLookupTableDefinition();
	}

	private function _getRouteLogTableDefinition() {
		return $this->_tableDefs->getRouteLogTableDefinition();
	}

	private function _getRouteTrackTableName() {
		return $this->_env->getRouteTrackTableName();
	}

	private function _getRouteDetailsTableName() {
		return $this->_env->getRouteDetailsTableName();
	}

	private function _getLookupLangTableName() {
		return $this->_env->getLookupLangTableName();
	}

	private function _getLookupTableName() {
		return $this->_env->getLookupTableName();
	}

	private function _getRouteDetailsLookupTableName() {
		return $this->_env->getRouteDetailsLookupTableName();
	}

	private function _getRouteLogTableName() {
		return $this->_env->getRouteLogTableName();
	}
}