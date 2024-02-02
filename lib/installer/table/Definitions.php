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

class Abp01_Installer_Table_Definitions {
	private $_env;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	public function getRouteTrackTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->getRouteTrackTableName() . "` (
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

	public function getRouteDetailsTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->getRouteDetailsTableName() . "` (
			`post_ID` BIGINT(10) UNSIGNED NOT NULL,
			`route_type` VARCHAR(150) NOT NULL,
			`route_data_serialized` LONGTEXT NOT NULL,
			`route_data_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`route_data_last_modified_at` TIMESTAMP NULL DEFAULT NULL,
			`route_data_last_modified_by` BIGINT(20) NULL DEFAULT NULL,
				PRIMARY KEY (`post_ID`)
		)";
	}

	public function getLookupLangTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->getLookupLangTableName() . "` (
			`ID` INT(10) UNSIGNED NOT NULL,
			`lookup_lang` VARCHAR(10) NOT NULL,
			`lookup_label` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`ID`, `lookup_lang`)
		)";
	}

	public function getLookupTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->getLookupTableName() . "` (
			`ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`lookup_category` VARCHAR(150) NOT NULL,
			`lookup_label` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`ID`)
		)";
	}

	public function getRouteDetailsLookupTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->getRouteDetailsLookupTableName() . "` (
			`post_ID` BIGINT(10) UNSIGNED NOT NULL,
			`lookup_ID` INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (`post_ID`, `lookup_ID`)
		)";
	}

	public function getRouteLogTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->getRouteLogTableName() . "` (
			`log_ID` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`log_post_ID` BIGINT(20) NOT NULL,
			`log_rider` VARCHAR(255) NOT NULL,
			`log_date` DATE NOT NULL,
			`log_vehicle` VARCHAR(255) NOT NULL,
			`log_gear` TEXT NULL DEFAULT NULL,
			`log_duration_hours` INT(11) NULL DEFAULT NULL,
			`log_notes` TEXT NULL DEFAULT NULL,
			`log_date_created` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
			`log_date_updated` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
			`log_is_public` BIT(1) NOT NULL DEFAULT b'0',
			`log_created_by` BIGINT(20) NOT NULL,
			`log_updated_by` BIGINT(20) NOT NULL,
			PRIMARY KEY (`log_ID`) USING BTREE,
			INDEX `IDX_route_log_post_ID` (`log_post_ID`) USING BTREE
		)";
	}

	public function getRouteTrackTableName() {
		return $this->_env->getRouteTrackTableName();
	}

	public function getRouteDetailsTableName() {
		return $this->_env->getRouteDetailsTableName();
	}

	public function getLookupLangTableName() {
		return $this->_env->getLookupLangTableName();
	}

	public function getLookupTableName() {
		return $this->_env->getLookupTableName();
	}

	public function getRouteDetailsLookupTableName() {
		return $this->_env->getRouteDetailsLookupTableName();
	}

	public function getRouteLogTableName() {
		return $this->_env->getRouteLogTableName();
	}
}