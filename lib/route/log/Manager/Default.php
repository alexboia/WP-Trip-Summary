<?php
class Abp01_Route_Log_Manager_Default implements Abp01_Route_Log_Manager {
	private static $_instance = null;

	private $_env;

	private function __construct() {
		$this->_env = Abp01_Env::getInstance();
	}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function getLog($postId) { 
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->where('post_ID', $postId);
		$row = $db->getOne($table);
		if (!$row) {
			return null;
		}

		$logEntriesJson = isset($row['log_entries_serialized']) 
			? $row['log_entries_serialized'] 
			: null;

		if (empty($logEntriesJson)) {
			return null;
		}

		return Abp01_Route_Log::fromJson($postId, 
			$logEntriesJson);
	}

    public function saveLog(Abp01_Route_Log $log, $currentUserId) { 
		$postId = intval($log->getPostId());
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$data = array(
			'post_ID' => $log->getPostId(),
			'log_entries_serialized' => $log->getLogEntriesAsJson(),
			'log_modified_at' => $db->now(),
			'log_modified_by' => $currentUserId
		);

		$db->where('post_ID', $postId);
		if ($db->update($table, $data)) {
			if ($db->count) {
				return true;
			} else {
				return ($db->insert($table, $data) !== false);
			}
		} else {
			return false;
		}
	}

    public function deleteLog($postId) { 
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->where('post_ID', $postId);
		return ($db->delete($table) !== false);
	}

	public function clearAllLogs() {
		$db = $this->_env->getDb();
		$table = $this->_env->getRouteLogTableName();

		$db->rawQuery('TRUNCATE TABLE `' . $table . '`', 
			null);
	}
}