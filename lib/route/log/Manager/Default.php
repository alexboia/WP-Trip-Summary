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

declare(strict_types = 1);

if (!defined('ABP01_LOADED')) {
	exit;
}

use WpTripSummary\Env;

class Abp01_Route_Log_Manager_Default implements Abp01_Route_Log_Manager {
	private static Abp01_Route_Log_Manager_Default|null $_instance = null;

	private Env $_env;

	private array $_adminLogCache = array();

	private function __construct() {
		$this->_env = Env::getInstance();
	}

	public static function getInstance(): Abp01_Route_Log_Manager_Default {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getAdminLog(int|string $postId): Abp01_Route_Log {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		if (!empty($this->_adminLogCache[$postId])) {
			return $this->_adminLogCache[$postId];
		}

		$db = $this->_env->getDb();
		$routeLogTable = $this->_env->getRouteLogTableName();
		$usersTable = $this->_env->getWpUsersTableName();

		$db->join($usersTable . ' c_wpu', 'c_wpu.ID = rl.log_created_by', 'LEFT');
		$db->join($usersTable . ' u_wpu', 'u_wpu.ID = rl.log_updated_by', 'LEFT');

		$db->where('rl.log_post_ID', $postId, '=');

		$rawLogEntriesData = $db->get($routeLogTable  . ' rl', null, array(
			'rl.*', 
			'CONCAT(c_wpu.user_login, "(", c_wpu.display_name, ")") AS `log_created_by_user_desc`', 
			'CONCAT(u_wpu.user_login, "(", u_wpu.display_name, ")") AS `log_updated_by_user_desc`'
		));

		if (!empty($rawLogEntriesData)) {
			$log = $this->_toRouteLog($postId, $rawLogEntriesData);
			$this->_adminLogCache[$postId] = $log;
			return $log;
		} else {
			return new Abp01_Route_Log($postId);
		}
	}

	private function _toRouteLog(int $postId, array $rawLogEntriesData): Abp01_Route_Log {
		$logEntries = array();
		foreach ($rawLogEntriesData as $rle) {
			$logEntries[] = Abp01_Route_Log_Entry::fromDbArray($rle);
		}

		return new Abp01_Route_Log($postId, $logEntries);
	}

	public function getPublicLog(int|string $postId): Abp01_Route_Log {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$routeLogTable = $this->_env->getRouteLogTableName();

		$db->where('rl.log_post_ID', $postId, '=');
		$db->where('rl.log_is_public', 1, '=');
		$db->orderBy('rl.log_date', 'DESC');
		$rawLogEntriesData = $db->get($routeLogTable  . ' rl', null, 'rl.*');

		if (!empty($rawLogEntriesData)) {
			return $this->_toRouteLog($postId, $rawLogEntriesData);
		} else {
			return new Abp01_Route_Log($postId);
		}
	}

	public function saveLogEntry(Abp01_Route_Log_Entry $logEntry): bool {
		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		if (!empty($this->_adminLogCache[$logEntry->postId])) {
			unset($this->_adminLogCache[$logEntry->postId]);
		}

		$data = $logEntry->toDbArray();
		if ($logEntry->id > 0) {
			$db->where('log_ID', $logEntry->id);
			return $db->update($table, $data);
		} else {
			if ($db->insert($table, $data) !== false) {
				$logEntry->id = $db->getInsertId();
				return true;
			} else {
				return false;
			}
		}
	}

    public function deleteLog(int|string $postId): bool { 
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		if (!empty($this->_adminLogCache[$postId])) {
			unset($this->_adminLogCache[$postId]);
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->where('log_post_ID', $postId);
		return ($db->delete($table) !== false);
	}

	public function deleteLogEntry(int|string $postId, int|string $logEntryId): bool {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$logEntryId = intval($logEntryId);
		if ($logEntryId <= 0) {
			throw new InvalidArgumentException();
		}

		if (!empty($this->_adminLogCache[$postId])) {
			unset($this->_adminLogCache[$postId]);
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->where('log_post_ID', $postId);
		$db->where('log_ID', $logEntryId);

		return ($db->delete($table) !== false);
	}

	public function clearAllLogEntries(): void {
		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->rawQuery('TRUNCATE TABLE `' . $table . '`', null);
		$this->_adminLogCache = array();
	}

	public function getLogEntryById(int|string $postId, int|string $logEntryId): ?Abp01_Route_Log_Entry {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$logEntryId = intval($logEntryId);
		if ($logEntryId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->where('log_post_ID', $postId);
		$db->where('log_ID', $logEntryId);

		$result = $db->getOne($table, '*');
		if (empty($result)) {
			return null;
		}

		return Abp01_Route_Log_Entry::fromDbArray($result);
	}

	public function getLastUsedVehicle(int|string $postId): string {
		$postId = intval($postId);
		if ($postId <= 0) {
			return '';
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->where('log_post_ID', $postId);
		$db->orderBy('log_ID', 'DESC');

		$result = $db->getOne($table, 'log_vehicle');
		if (!empty($result) && !empty($result['log_vehicle'])) {
			return (string)$result['log_vehicle'];
		} else {
			return '';
		}
	}
}