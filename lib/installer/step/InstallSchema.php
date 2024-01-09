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

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
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
				=> $this->_getRouteDetailsLookupTableDefinition()
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
		return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteTrackTableName() . "` (
			`post_ID` BIGINT(20) UNSIGNED NOT NULL,
			`route_track_file` LONGTEXT NOT NULL,
			`route_track_file_mime_type` VARCHAR(250) NOT NULL DEFAULT 'application/gpx' ,
			`route_min_coord` POINT NOT NULL,
			`route_max_coord` POINT NOT NULL,
			`route_bbox` POLYGON NOT NULL,
			`route_min_alt` FLOAT NULL DEFAULT '0',
			`route_max_alt` FLOAT NULL DEFAULT '0',
			`route_track_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`route_track_modified_at` TIMESTAMP NULL DEFAULT NULL,
			`route_track_modified_by` BIGINT(20) NULL DEFAULT NULL,
				PRIMARY KEY (`post_ID`),
				SPATIAL INDEX `idx_route_track_bbox` (`route_bbox`)
		)";
	}

	private function _getRouteDetailsTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteDetailsTableName() . "` (
			`post_ID` BIGINT(10) UNSIGNED NOT NULL,
			`route_type` VARCHAR(150) NOT NULL,
			`route_data_serialized` LONGTEXT NOT NULL,
			`route_data_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`route_data_last_modified_at` TIMESTAMP NULL DEFAULT NULL,
			`route_data_last_modified_by` BIGINT(20) NULL DEFAULT NULL,
				PRIMARY KEY (`post_ID`)
		)";
	}

	private function _getLookupLangTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->_getLookupLangTableName() . "` (
			`ID` INT(10) UNSIGNED NOT NULL,
			`lookup_lang` VARCHAR(10) NOT NULL,
			`lookup_label` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`ID`, `lookup_lang`)
		)";
	}

	private function _getLookupTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->_getLookupTableName() . "` (
			`ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`lookup_category` VARCHAR(150) NOT NULL,
			`lookup_label` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`ID`)
		)";
	}

	private function _getRouteDetailsLookupTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteDetailsLookupTableName() . "` (
			`post_ID` BIGINT(10) UNSIGNED NOT NULL,
			`lookup_ID` INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (`post_ID`, `lookup_ID`)
		)";
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
}