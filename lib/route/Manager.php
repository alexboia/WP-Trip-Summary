<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Route_Manager {
	private static $_instance = null;

	private $_lastError = null;

	private $_env = null;

	private  $_proj = null;

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		$this->_env = Abp01_Env::getInstance();
		$this->_proj = new Abp01_Route_SphericalMercator();
	}

	private function _deleteLookupDataAssociation($db, $postId) {
		$tableName = $this->_env->getRouteDetailsLookupTableName();
		$db->where('post_ID', $postId);
		$db->delete($tableName);

		$lastError = trim($db->getLastError());
		return empty($lastError);
	}

	private function _updateLookupDataAssociation($db, $postId, Abp01_Route_Info $info) {
		$tableName = $this->_env->getRouteDetailsLookupTableName();

		//clear all previous associations
		if (!$this->_deleteLookupDataAssociation($db, $postId)) {
			return false;
		}

		$data = $info->getData();
		foreach ($data as $field => $value) {
			//filter info fields that are not lookup data items
			if (!$info->getLookupKey($field)) {
				continue;
			}

			//convert non-arrays to arrays in order to have a single
			//saving sequence
			if (!is_array($value)) {
				$value = array($value);
			}

			//finally, save each item
			foreach ($value as $v) {
				$db->insert($tableName, array(
					'post_ID' => $postId,
					'lookup_ID' => $v
				));

				$lastError = trim($db->getLastError());
				if (!empty($lastError)) {
					return false;
				}
			}
		}

		return true;
	}

	public function saveRouteInfo($postId, $currentUserId, Abp01_Route_Info $info) {
		$postId = intval($postId);
		if ($postId <= 0 || $info == null) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteDetailsTableName();
		$success = false;

		$data = array(
			'post_ID' => $postId,
			'route_type' => $info->getType(),
			'route_data_serialized' => $info->toJson(),
			'route_data_last_modified_by' => $currentUserId,
			'route_data_last_modified_at' => $db->now()
		);

		$db->startTransaction();
		if (!$this->_updateLookupDataAssociation($db, $postId, $info)) {
			$db->rollback();
			return false;
		}

		$db->where('post_ID', $postId);
		if ($db->update($table, $data) !== false) {
			if ($db->count) {
				$success = true;
			} else if ($db->insert($table, $data) !== false) {
				$success = true;
			}
		}

		if ($success) {
			$db->commit();
		} else {
			$db->rollback();
		}

		return $success;
	}

	public function deleteRouteInfo($postId) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}
		if (!$this->hasRouteInfo($postId)) {
			return true;
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteDetailsTableName();

		$db->startTransaction();
		if (!$this->_deleteLookupDataAssociation($db, $postId)) {
			$db->rollback();
			return false;
		}

		$db->where('post_ID', $postId);
		if ($db->delete($table) === false) {
			$db->rollback();
			return false;
		} else {
			$db->commit();
			return true;
		}
	}

	public function saveRouteTrack($postId, $currentUserId, Abp01_Route_Track $track) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$proj = $this->_proj;
		$db = $this->_env->getDb();
		$table = $this->_env->getRouteTrackTableName();
		$bounds = $track->getBounds();

		$sw = $bounds->southWest;
		$ne = $bounds->northEast;

		$minCoord = array_values($proj->forward($sw->lat, $sw->lng));
		$maxCoord = array_values($proj->forward($ne->lat, $ne->lng));
		$lineBetween = array($minCoord[0], $minCoord[1],
			$maxCoord[0],
			$maxCoord[1]);

		$data = array(
			'post_ID' => $postId,
			'route_track_file' => $track->getFile(),
			'route_bbox' => $db->func("Envelope(LineString(GeometryFromText(AsText(Point(?, ?)), 3857), GeometryFromText(AsText(Point(?, ?)), 3857)))", $lineBetween),
			'route_min_coord' => $db->func("GeometryFromText(AsText(Point(?, ?)), 3857)", $minCoord),
			'route_max_coord' => $db->func("GeometryFromText(AsText(Point(?, ?)), 3857)", $maxCoord),
			'route_min_alt' => $track->minAlt,
			'route_max_alt' => $track->maxAlt,
			'route_track_modified_at' => $db->now(),
			'route_track_modified_by' => $currentUserId
		);

		$db->where('post_ID', $postId);
		if ($db->update($table, $data)) {
			if ($db->count) {
				return true;
			} else {
				if ($db->insert($table, $data) === false) {
					return false;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}

	public function deleteRouteTrack($postId) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}
		if (!$this->hasRouteTrack($postId)) {
			return true;
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteTrackTableName();

		$db->where('post_ID', $postId);
		if ($db->delete($table) === false) {
			return false;
		} else {
			return true;
		}
	}

	public function getRouteInfo($postId) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteDetailsTableName();

		$db->where('post_ID', $postId);
		$row = $db->getOne($table);
		if (!$row) {
			return null;
		}

		$type = isset($row['route_type']) ? $row['route_type'] : null;
		$json = isset($row['route_data_serialized']) ? $row['route_data_serialized']: null;

		if (!$type || !$json) {
			return null;
		}

		return Abp01_Route_Info::fromJson($type, $json);
	}

	public function getRouteTrack($postId) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteTrackTableName();

		$db->where('post_ID', $postId);
		$row = $db->getOne($table, array(
			'route_min_alt',
			'route_max_alt',
			'route_track_file',
			'X(route_min_coord) AS route_min_lng',
			'Y(route_min_coord) AS route_min_lat',
			'X(route_max_coord) AS route_max_lng',
			'Y(route_max_coord) AS route_max_lat'
		));
		if (!$row) {
			return null;
		}

		if (isset($row['route_track_file']) && $row['route_track_file']) {
			$proj = $this->_proj;
			$file = $row['route_track_file'];

			$minCoord = $proj->inverse(floatval($row['route_min_lat']),
				floatval($row['route_min_lng']));
			$maxCoord = $proj->inverse(floatval($row['route_max_lat']),
				floatval($row['route_max_lng']));

			$bounds = new Abp01_Route_Track_Bbox($minCoord['lat'],
				$minCoord['lng'],
				$maxCoord['lat'],
				$maxCoord['lng']);

			$track = new Abp01_Route_Track($file, $bounds,
				floatval($row['route_min_alt']),
				floatval($row['route_min_alt']));

			return $track;
		} else {
			return null;
		}
	}

	public function hasRouteTrack($postId) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteTrackTableName();

		$db->where('post_ID', $postId);
		$stats = $db->getOne($table, 'COUNT(*) AS cnt');
		if ($stats && is_array($stats)) {
			return $stats['cnt'] > 0;
		}

		return false;
	}

	public function hasRouteInfo($postId) {
		$postId = intval($postId);
		if ($postId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$table = $this->_env->getRouteDetailsTableName();

		$db->where('post_ID', $postId);
		$stats = $db->getOne($table, 'COUNT(*) AS cnt');
		if ($stats && is_array($stats)) {
			return $stats['cnt'] > 0;
		}

		return false;
	}

	public function getLastError() {
		return $this->_lastError;
	}
}