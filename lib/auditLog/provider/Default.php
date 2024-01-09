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

class Abp01_AuditLog_Provider_Default implements Abp01_AuditLog_Provider {

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	/**
	 * @param int $postId 
	 * @return Abp01_AuditLog_Data 
	 */
	public function getAuditLogForPostId($postId) { 
		if (empty($postId)) {
			throw new InvalidArgumentException('Post ID may not be empty.');
		}

		if ($postId < 0) {
			throw new InvalidArgumentException('Post ID may not be less than 0.');
		}

		$infoAuditLogData = $this->_retrieveInfoAuditLogData($postId);
		$trackAuditLogData = $this->_retrieveTrackAuditLogData($postId);

		return new Abp01_AuditLog_Data($infoAuditLogData, 
			$trackAuditLogData);
	}

	private function _retrieveInfoAuditLogData($postId) {
		$db = $this->_env->getDb();

		$infoTable = $this->_env
			->getRouteDetailsTableName();
		$usersTable = $this->_env
			->getWpUsersTableName();

		$db->join($usersTable . ' usr', 'usr.ID = rd.route_data_last_modified_by', 
			'LEFT');

		$db->where('rd.post_ID', 
			$postId);

		$auditLogData = $db->getOne($infoTable . ' rd', array(
			'rd.post_ID',
			'rd.route_data_created_at',
			'rd.route_data_last_modified_at',
			'rd.route_data_last_modified_by',
			'usr.user_login route_data_last_modified_by_name'
		));

		return $this->_coalesceAuditLogData($auditLogData);
	}

	private function _coalesceAuditLogData($auditLogData) {
		if ($auditLogData == null) {
			$auditLogData = array();
		}
		return $auditLogData;
	}

	private function _retrieveTrackAuditLogData($postId) {
		$db = $this->_env->getDb();

		$trackTable = $this->_env
			->getRouteTrackTableName();
		$usersTable = $this->_env
			->getWpUsersTableName();

		$db->join($usersTable . ' usr', 'usr.ID = rt.route_track_modified_by', 
			'LEFT');

		$db->where('rt.post_ID', 
			$postId);

		$auditLogData = $db->getOne($trackTable . ' rt', array(
			'rt.post_ID',
			'rt.route_track_created_at',
			'rt.route_track_modified_at',
			'rt.route_track_modified_by',
			'usr.user_login route_track_modified_by_name'
		));

		return $this->_coalesceAuditLogData($auditLogData);
	}
}