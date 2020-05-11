<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

class Abp01_Route_Manager {
	/**
	 * The route manager singleton instance
	 * 
	 * @var Abp01_Route_Manager
	 * */
	private static $_instance = null;

	/**
	 * The last error that occured during a route manager operation
	 * 
	 * @var Exception|WP_Error
	 */
	private $_lastError = null;

	/**
	 * @var Abp01_Env The environment accessor instance
	 * */
	private $_env = null;

	/**
	 * The projection being used to process coordinates
	 * 
	 * @var Abp01_Route_SphericalMercator
	 * */
	private  $_proj = null;

	/**
	 * Retrieves the route manager singleton instance
	 * 
	 * @return Abp01_Route_Manager
	 * */
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

	public function saveRouteTrack(Abp01_Route_Track $track, $currentUserId) {
		$postId = intval($track->getPostId());
		if ($postId <= 0) {
			throw new InvalidArgumentException('Invalid post ID: "' . $postId . '"');
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
			'route_bbox' => $db->func("ST_Envelope(LINESTRING(ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857), ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)))", $lineBetween),
			'route_min_coord' => $db->func("ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)", $minCoord),
			'route_max_coord' => $db->func("ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)", $maxCoord),
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

	/**
	 * @return Abp01_Route_Track
	 */
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
			$file = $row['route_track_file'];

			$minCoord = $proj->inverse(floatval($row['route_min_lng']), 
				floatval($row['route_min_lat']));
			$maxCoord = $proj->inverse(floatval($row['route_max_lng']), 
				floatval($row['route_max_lat']));

			$bounds = new Abp01_Route_Track_Bbox($minCoord['lat'],
				$minCoord['lng'],
				$maxCoord['lat'],
				$maxCoord['lng']);

			$track = new Abp01_Route_Track($postId, 
				$file, 
				$bounds,
				floatval($row['route_min_alt']),
				floatval($row['route_max_alt']));

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

	public function getTripSummaryStatusInfo($postIds) {
		if (!is_array($postIds)) {
			$postIds = array($postIds);
		}

		$db = $this->_env->getDb();

		$postsTable = $this->_env->getWpPostsTableName();
		$trackTable = $this->_env->getRouteTrackTableName();
		$infoTable = $this->_env->getRouteDetailsTableName();

		$db->join($infoTable . ' rd', 'rd.post_ID = p.ID', 
			'LEFT');

		$db->join($trackTable . ' rt', 'rt.post_ID = p.ID', 
			'LEFT');

		$db->where('p.ID', $postIds, 'IN');
		$data = $db->get($postsTable  . ' p', null, array(
			'p.ID', 
			'IF(rd.post_ID IS NOT NULL, true, false) has_route_details', 
			'IF(rt.post_ID IS NOT NULL, true, false) has_route_track'
		));

		$statusInfo = array();
		if (!empty($data)) {
			foreach ($data as $row) {
				$statusInfo[intval($row['ID'])] = array(
					'has_route_details' 
						=> intval($row['has_route_details']) === 1,
					'has_route_track'
						=> intval($row['has_route_track']) === 1
				);
			}
		}

		return $statusInfo;
	}

	public function deleteTrackFiles($postId) {
		//delete track file
		$trackFile = $this->getTrackFilePath($postId);
		if (!empty($trackFile) && file_exists($trackFile)) {
			@unlink($trackFile);
		}

		//delete cached track file
		$cacheFile = $this->getTrackDocumentCacheFilePath($postId);
		if (!empty($cacheFile) && file_exists($cacheFile)) {
			@unlink($cacheFile);
		}
	}

	public function getOrCreateDisplayableAltitudeProfile(Abp01_Route_Track $track, $targetSystem, $stepPoints = 10) {
		if ($stepPoints <= 0) {
			throw new InvalidArgumentException('Number of points to step over must be greater than 0');
		}

		$profile = $this->_getCachedTrackProfileDocument($track->getPostId());
		$targetSystem = !($targetSystem instanceof Abp01_UnitSystem) 
			? $this->_createUnitSystemInstanceOrThrow($targetSystem) 
			: $targetSystem;

		if (!($profile instanceof Abp01_Route_Track_AltitudeProfile) 
			|| !$profile->matchesContext($targetSystem, $stepPoints)) {
			$trackDocument = $this->getOrCreateDisplayableTrackDocument($track);

			$profile = $trackDocument->computeAltitudeProfile($targetSystem, 
				$stepPoints);
			
			$this->_cacheTrackProfileDocument($track->getPostId(), 
				$profile);
		}

		return $profile;
	}

	public function getOrCreateDisplayableTrackDocument(Abp01_Route_Track $track) {
		$trackDocument = $this->_getCachedTrackDocument($track->getPostId());

		if (!($trackDocument instanceof Abp01_Route_Track_Document)) {
			$file = $this->getTrackFilePath($track->getPostId());
			if (is_readable($file)) {
				$parser = new Abp01_Route_Track_GpxDocumentParser();
				$trackDocument = $parser->parse(file_get_contents($file));
				if (!empty($trackDocument)) {
					$trackDocument = $trackDocument->simplify(0.01);
					$this->_cacheTrackDocument($track->getPostId(), $trackDocument);
				}
			}
		}

		return $trackDocument;
	}

	/**
     * @return Abp01_Route_Track_Info
     */
    public function getDisplayableTrackInfo(Abp01_Route_Track $track, $targetSystem) {
        $minAlt = new Abp01_UnitSystem_Value_Height($track->minAlt);
        $maxAlt = new Abp01_UnitSystem_Value_Height($track->maxAlt);

        $minAlt = $minAlt->convertTo($targetSystem);
        $maxAlt = $maxAlt->convertTo($targetSystem);

        return new Abp01_Route_Track_Info($minAlt, $maxAlt);
    }

	/**
	 * Caches the serialized version of the given track document for the given post ID
	 * 
	 * @param int $postId The post ID
	 * @param Abp01_Route_Track_Document $trackDocument The track document
	 * @return void
	 */
	private function _cacheTrackDocument($postId, Abp01_Route_Track_Document $trackDocument) {
		//Ensure the storage directory structure exists
		abp01_ensure_storage_directory();

		//Compute the path at which to store the cached file 
		//	and store the serialized track data
		$path = $this->getTrackDocumentCacheFilePath($postId);
		if (!empty($path)) {
			file_put_contents($path, $trackDocument->serializeDocument(), LOCK_EX);
		}

		//Ensure in-memory copy is removed
		wp_cache_delete($postId, 'abp01_cached_track_documents');
	}

	private function _cacheTrackProfileDocument($postId, Abp01_Route_Track_AltitudeProfile $trackProfileDocument) {
		//Ensure the storage directory structure exists
		abp01_ensure_storage_directory();

		//Compute the path at which to store the cached file 
		//	and store the serialized track data
		$path = $this->getTrackProfileDocumentCacheFilePath($postId);
		if (!empty($path)) {
			file_put_contents($path, $trackProfileDocument->serializeDocument(), LOCK_EX);
		}
	}

	/**
	 * Retrieves and deserializes the cached version of the 
	 * 	track document corresponding to the given post ID
	 * 
	 * @param int $postId The post ID
	 * @return Abp01_Route_Track_Document The deserialized document
	 */
	private function _getCachedTrackDocument($postId) {
		$path = $this->getTrackDocumentCacheFilePath($postId);
		if (empty($path) || !is_readable($path)) {
			return null;
		}

		//First, try to fetch in-memory cached item
		$trackDocument = wp_cache_get($postId, 'abp01_cached_track_documents');
		if (empty($trackDocument)) {
			//If in-memory cached item does not exist, 
			//	deserialize it from disk cache
			//	and store it in-memory
			$contents = file_get_contents($path);
			$trackDocument = Abp01_Route_Track_Document::fromSerializedDocument($contents);
			wp_cache_set($postId, $trackDocument, 'abp01_cached_track_documents');
		}

		return $trackDocument;
	}

	/**
	 * @return Abp01_Route_Track_AltitudeProfile
	 */
	private function _getCachedTrackProfileDocument($postId) {
		$path = $this->getTrackProfileDocumentCacheFilePath($postId);
		if (empty($path) || !is_readable($path)) {
			return null;
		}

		$contents = file_get_contents($path);
		return Abp01_Route_Track_AltitudeProfile::fromSerializedDocument($contents);
	}

	private function _createUnitSystemInstanceOrThrow($unitSystem) {
        if (!Abp01_UnitSystem::isSupported($unitSystem)) {
            throw new InvalidArgumentException('Unsupported unit system: "' . $unitSystem . '"');
        }

        return Abp01_UnitSystem::create($unitSystem);
    }

	/**
	 * Computes the track document cache file path for the given post ID
	 * 
	 * @param int $postId The post identifier
	 * @return string
	 */
	public function getTrackDocumentCacheFilePath($postId) {
		$fileName = sprintf('track-%d.cache', $postId);
		$cacheStorageDir = $this->_env->getCacheStorageDir();

		return is_dir($cacheStorageDir) 
			? wp_normalize_path($cacheStorageDir . '/' . $fileName) 
			: null;
	}

	public function getTrackProfileDocumentCacheFilePath($postId) {
		$fileName = sprintf('track-profile-%d.cache', $postId);
		$cacheStorageDir = $this->_env->getCacheStorageDir();

		return is_dir($cacheStorageDir) 
			? wp_normalize_path($cacheStorageDir . '/' . $fileName) 
			: null;
	}

	/**
	 * Compute the absolute file upload destination file path for the given post ID
	 * 
	 * @param int $postId The post ID
	 * @return string The computed path
	 */
	public function getTrackFilePath($postId) {
		$fileName = sprintf('track-%d.gpx', $postId);
		$tracksStorageDir = $this->_env->getTracksStorageDir();

		return is_dir($tracksStorageDir) 
			? wp_normalize_path($tracksStorageDir . '/' . $fileName) 
			: null;
	}

	public function getLastError() {
		return $this->_lastError;
	}

	public function getProj() {
		return $this->_proj;
	}
}