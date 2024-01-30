<?php
class Abp01_Route_Log_Manager_Default implements Abp01_Route_Log_Manager {
	/**
	 * @var Abp01_Route_Log_Manager_Default
	 */
	private static $_instance = null;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	private $_adminLogCache = array();

	private function __construct() {
		$this->_env = Abp01_Env::getInstance();
	}

	/**
	 * @return Abp01_Route_Log_Manager_Default 
	 */
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getAdminLog($postId) {
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

	private function _toRouteLog($postId, array $rawLogEntriesData) {
		$logEntries = array();
		foreach ($rawLogEntriesData as $rle) {
			$logEntries[] = Abp01_Route_Log_Entry::fromDbArray($rle);
		}

		return new Abp01_Route_Log($postId, $logEntries);
	}

	public function getPublicLog($postId) {
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

	public function saveLogEntry(Abp01_Route_Log_Entry $logEntry) {
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

    public function deleteLog($postId) { 
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

	public function deleteLogEntry($postId, $logEntryId) {
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

	public function clearAllLogEntries() {
		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->rawQuery('TRUNCATE TABLE `' . $table . '`', null);
		$this->_adminLogCache = array();
	}

	public function getLogEntryById($postId, $logEntryId) {
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

	public function getLastUsedVehicle($postId) {
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
			return $result['log_vehicle'];
		} else {
			return '';
		}
	}
}