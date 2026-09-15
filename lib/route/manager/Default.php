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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Route_Manager_Default implements Abp01_Route_Manager {
	private static Abp01_Route_Manager_Default|null $_instance = null;

	private Exception|WP_Error|null $_lastError = null;

	private Env|null $_env = null;

	private Abp01_Route_SphericalMercator|null $_proj = null;

	public static function getInstance(): Abp01_Route_Manager_Default {
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		$this->_env = Env::getInstance();
		$this->_proj = new Abp01_Route_SphericalMercator();
	}

	private function _deleteLookupDataAssociation(MysqliDb $db, int $postId): bool {
		$tableName = $this->_env->getRouteDetailsLookupTableName();
		$db->where('post_ID', $postId);
		$db->delete($tableName);

		$lastError = trim($db->getLastError());
		return empty($lastError);
	}

	private function _updateLookupDataAssociation(MysqliDb $db, int $postId, Abp01_Route_Info $info): bool {
		$tableName = $this->_env->getRouteDetailsLookupTableName();

		//clear all previous associations
		if (!$this->_deleteLookupDataAssociation($db, $postId)) {
			return false;
		}

		$data = $info->getData();
		foreach ($data as $field => $value) {
			//filter info fields that are not lookup data items
			if (!$info->isLookupKey($field)) {
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

	public function saveRouteInfo(int|string $postId, Abp01_Route_Info $info, int|string $currentUserId): bool {
		$postId = intval($postId);
		if ($postId <= 0 || $info == null) {
			throw new InvalidArgumentException();
		}

		$currentUserId = intval($currentUserId);
		if ($currentUserId < 0) {
			$currentUserId = 0;
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

	public function deleteRouteInfo(int|string $postId): bool {
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

	public function saveRouteTrack(Abp01_Route_Track $track, int|string $currentUserId): bool {
		$postId = intval($track->getPostId());
		if ($postId <= 0) {
			throw new InvalidArgumentException('Invalid post ID: "' . $postId . '"');
		}

		$currentUserId = intval($currentUserId);
		if ($currentUserId < 0) {
			$currentUserId = 0;
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
			'route_track_file' => $track->getFileName(),
			'route_track_file_mime_type' => $track->getFileMimeType(),
			'route_bbox' => $db->func("ST_Envelope(LINESTRING(ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857), ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)))", $lineBetween),
			'route_min_coord' => $db->func("ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)", $minCoord),
			'route_max_coord' => $db->func("ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)", $maxCoord),
			'route_min_alt' => $track->getMinimumAltitude(),
			'route_max_alt' => $track->getMaximumAltitude(),
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

	public function deleteRouteTrack(int|string $postId): bool {
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

	public function getRouteInfo(int|string $postId): ?Abp01_Route_Info {
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

		$type = isset($row['route_type']) 
			? $row['route_type'] 
			: null;

		$json = isset($row['route_data_serialized']) 
			? $row['route_data_serialized'] 
			: null;

		if (!$type || !$json) {
			return null;
		}

		return Abp01_Route_Info::fromJson($type, $json);
	}

	public function getRouteTrack(int|string $postId): ?Abp01_Route_Track {
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
			'route_track_file_mime_type',
			'ST_X(route_min_coord) AS route_min_lng',
			'ST_Y(route_min_coord) AS route_min_lat',
			'ST_X(route_max_coord) AS route_max_lng',
			'ST_Y(route_max_coord) AS route_max_lat'
		));
		if (!$row) {
			return null;
		}

		if (isset($row['route_track_file']) && $row['route_track_file']) {
			$proj = $this->_proj;
			$fileName = $row['route_track_file'];
			$fileMimeType = $row['route_track_file_mime_type'];

			$minCoord = $proj->inverse(floatval($row['route_min_lng']), 
				floatval($row['route_min_lat']));
			$maxCoord = $proj->inverse(floatval($row['route_max_lng']), 
				floatval($row['route_max_lat']));

			$bounds = new Abp01_Route_Track_Bbox($minCoord['lat'],
				$minCoord['lng'],
				$maxCoord['lat'],
				$maxCoord['lng']);

			$track = new Abp01_Route_Track($postId, 
				$fileName, 
				$fileMimeType,
				$bounds,
				floatval($row['route_min_alt']),
				floatval($row['route_max_alt']));

			return $track;
		} else {
			return null;
		}
	}

	public function hasRouteTrack(int|string $postId): bool {
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

	public function hasRouteInfo(int|string $postId): bool {
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

	public function getTripSummaryStatusInfo(array $postIds): array {
		if (!is_array($postIds)) {
			$postIds = array($postIds);
		}

		$allStatusInfo = array();
		$rawStatusInfoData = $this->_queryForTripSummaryStatusInfoData($postIds);

		if (!empty($rawStatusInfoData)) {
			foreach ($rawStatusInfoData as $row) {
				$postId = intval($row['ID']);
				$postStatusInfo = $this->_getTripSummaryStatusInfoFromDbRow($row);
				$allStatusInfo[$postId] = $postStatusInfo;
			}
		}

		return $allStatusInfo;
	}

	private function _queryForTripSummaryStatusInfoData(array $postIds): array {
		$db = $this->_env->getDb();

		$postsTable = $this->_env
			->getWpPostsTableName();
		$trackTable = $this->_env
			->getRouteTrackTableName();
		$infoTable = $this->_env
			->getRouteDetailsTableName();

		$db->join($infoTable . ' rd', 'rd.post_ID = p.ID', 
			'LEFT');

		$db->join($trackTable . ' rt', 'rt.post_ID = p.ID', 
			'LEFT');

		$db->where('p.ID', $postIds, 
			'IN');
		
		$rawStatusInfoData = $db->get($postsTable  . ' p', null, array(
			'p.ID', 
			'IF(rd.post_ID IS NOT NULL, true, false) has_route_details', 
			'IF(rt.post_ID IS NOT NULL, true, false) has_route_track'
		));

		return $rawStatusInfoData;
	}

	private function _getTripSummaryStatusInfoFromDbRow(array $row): array {
		return array(
			'has_route_details' 
				=> intval($row['has_route_details']) === 1,
			'has_route_track'
				=> intval($row['has_route_track']) === 1
		);
	}

	function getTripSummaryRouteTypeInfo(array $postIds): array {
		if (!is_array($postIds)) {
			$postIds = array($postIds);
		}

		$allRouteTypeInfoData = array();
		$rawRouteTypeInfoData = $this->_queryForRouteTypeInfoData($postIds);

		if (!empty($rawRouteTypeInfoData)) {
			foreach ($rawRouteTypeInfoData as $row) {
				$postId = intval($row['post_ID']);
				$allRouteTypeInfoData[$postId] = $row['route_type'];
			}
		}

		return $allRouteTypeInfoData;
	}

	private function _queryForRouteTypeInfoData(array $postIds): array {
		$db = $this->_env->getDb();
		$infoTable = $this->_env->getRouteDetailsTableName();

		$db->where('rd.post_ID', $postIds, 
			'IN');

		$rawRouteTypeInfoData = $db->get($infoTable  . ' rd', null, array(
			'rd.post_ID', 
			'rd.route_type'
		));

		return $rawRouteTypeInfoData;
	}

	public function getAllPostsWithRouteTracks(): array {
		$postIds = array();

		$db = $this->_env->getDb();
		$trackTable = $this->_env->getRouteTrackTableName();

		$rows = $db->get($trackTable, null, 'post_ID');
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$postId = intval($row['post_ID']);
				$postIds[] = $postId;
			}
		}

		return $postIds;
	}

	public function clearAll(): void {
		$db = $this->_env->getDb();

		$trackTable = $this->_env
			->getRouteTrackTableName();
		$infoTable = $this->_env
			->getRouteDetailsTableName();
		$infoLookupTable = $this->_env
			->getRouteDetailsLookupTableName();

		$db->rawQuery('TRUNCATE TABLE `' . $trackTable . '`', 
			null);
		$db->rawQuery('TRUNCATE TABLE `' . $infoTable . '`', 
			null);
		$db->rawQuery('TRUNCATE TABLE `' . $infoLookupTable . '`', 
			null);
	}

	public function getLastError(): Exception|WP_Error|null {
		return $this->_lastError;
	}
}