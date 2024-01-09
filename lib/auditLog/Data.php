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

class Abp01_AuditLog_Data {
	private $_infoCreatedAt;

	private $_infoLastModifiedAt;

	private $_infoLastModifiedByUserId;

	private $_infoLastModifiedByUserName;

	private $_trackCreatedAt;

	private $_trackLastModifiedAt;

	private $_trackLastModifiedByUserId;

	private $_trackLastModifiedByUserName;

	public function __construct(array $infoAuditLogData, array $trackAuditLogData) {
		$this->_initInfoAuditLog($infoAuditLogData);
		$this->_initTrackAuditLog($trackAuditLogData);
	}

	public static function empty() {
		return new self(array(), array());
	}

	private function _initInfoAuditLog(array $infoAuditLogData) {
		$this->_infoCreatedAt = isset($infoAuditLogData['route_data_created_at'])
			? $infoAuditLogData['route_data_created_at']
			: null;
		$this->_infoLastModifiedAt = isset($infoAuditLogData['route_data_last_modified_at'])
			? $infoAuditLogData['route_data_last_modified_at']
			: null;

		$this->_infoLastModifiedByUserId = isset($infoAuditLogData['route_data_last_modified_by'])
			? $infoAuditLogData['route_data_last_modified_by']
			: null;
		$this->_infoLastModifiedByUserName = isset($infoAuditLogData['route_data_last_modified_by_name'])
			? $infoAuditLogData['route_data_last_modified_by_name']
			: null;
	}

	private function _initTrackAuditLog(array $trackAuditLogData) {
		$this->_trackCreatedAt = isset($trackAuditLogData['route_track_created_at'])
			? $trackAuditLogData['route_track_created_at']
			: null;
		$this->_trackLastModifiedAt = isset($trackAuditLogData['route_track_modified_at'])
			? $trackAuditLogData['route_track_modified_at']
			: null;

		$this->_trackLastModifiedByUserId = isset($trackAuditLogData['route_track_modified_by'])
			? $trackAuditLogData['route_track_modified_by']
			: null;
		$this->_trackLastModifiedByUserName = isset($trackAuditLogData['route_track_modified_by_name'])
			? $trackAuditLogData['route_track_modified_by_name']
			: null;
	}

	public function asPlainObject() {
		$data = new stdClass();

		$data->infoCreatedAt = $this->getInfoCreatedAt();
		$data->infoLastModifiedAt = $this->getInfoLastModifiedAt();
		$data->infoLastModifiedByUserId = $this->getInfoLastModifiedByUserId();
		$data->infoLastModifiedByUserName = $this->getInfoLastModifiedByUserName();

		$data->trackCreatedAt = $this->getTrackCreatedAt();
		$data->trackLastModifiedAt = $this->getTrackLastModifiedAt();
		$data->trackLastModifiedByUserId = $this->getTrackLastModifiedByUserId();
		$data->trackLastModifiedByUserName = $this->getTrackLastModifiedByUserName();

		return $data;
	}

	public function getInfoCreatedAt() {
		return $this->_infoCreatedAt;
	}

	public function getInfoLastModifiedAt() {
		return $this->_infoLastModifiedAt;
	}

	public function getInfoLastModifiedByUserId() {
		return $this->_infoLastModifiedByUserId;
	}

	public function getInfoLastModifiedByUserName() {
		return $this->_infoLastModifiedByUserName;
	}

	public function getTrackCreatedAt() {
		return $this->_trackCreatedAt;
	}

	public function getTrackLastModifiedAt() {
		return $this->_trackLastModifiedAt;
	}

	public function getTrackLastModifiedByUserId() {
		return $this->_trackLastModifiedByUserId;
	}

	public function getTrackLastModifiedByUserName() {
		return $this->_trackLastModifiedByUserName;
	}
}