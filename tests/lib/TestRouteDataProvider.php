<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class TestRouteDataProvider {
	use DbTestHelpers;

	/**
	 * @var MysqliDb
	 */
	private $_db;

	public function __construct() {
		$this->_db = $this->_getEnv()->getDb();
	}

	private function _getEnv() {
		return abp01_get_env();
	}

	public function nowTimestamp() {
		return $this->_db->now();
	}

	public function saveRouteInfo(array $info) {
		$routeDetailsTable = $this->_getRouteDetailsTableName();
		
		if (empty($info['route_data_created_at'])) {
			$info['route_data_created_at'] = $this->_db->now();
		}

		$this->_db->insert($routeDetailsTable, array(
			'post_ID' => $info['post_id'],
			'route_type' => $info['route_type'],
			'route_data_serialized' => $info['route_data_serialized'],
			'route_data_created_at' => $info['route_data_created_at'],
			'route_data_last_modified_at' => $info['route_data_last_modified_at'],
			'route_data_last_modified_by' => $info['route_data_last_modified_by']
		));
	}

	private function _getRouteDetailsTableName() {
		return $this->_getEnv()
			->getRouteDetailsTableName();
	}

	public function saveRouteInfoLookupAssociations($postId, array $lookupData) {
		$lookupDetailsTableName = $this->_getRouteDetailsLookupTableName();

		foreach ($lookupData as $field => $value) {
			if (!is_array($value)) {
				$value = array($value);
			}

			if (!is_array($value)) {
				$value = array($value);
			}

			foreach ($value as $valueItem) {
				$this->_db->insert($lookupDetailsTableName, array(
					'post_ID' => $postId,
					'lookup_ID' => $valueItem
				));
			}
		}
	}

	private function _getRouteDetailsLookupTableName() {
		return $this->_getEnv()
			->getRouteDetailsLookupTableName();
	}

	public function clearRouteInfo() {
		$this->_truncateTables($this->_db, 
			$this->_getRouteDetailsTableName());
	}

	public function clearRouteInfoLookupAssociations() {
		$this->_truncateTables($this->_db, 
			$this->_getRouteDetailsLookupTableName());
	}

	public function saveRouteTrack(array $track) {
		$routeTrackTableName = $this->_getRouteTrackTableName();

		if (empty($track['route_track_created_at'])) {
			$track['route_track_created_at'] = $this->_db->now();
		}

		$this->_db->insert($routeTrackTableName, array(
			'post_ID' => $track['post_id'],
			'route_track_file' => $track['route_file_name'],
			'route_track_file_mime_type' => $track['route_file_mime_type'],

			'route_bbox' => $this->_db->func('ST_Envelope(LINESTRING(ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857), ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)))',
				array(
					$track['route_min_x'], 
					$track['route_min_y'], 
					$track['route_max_x'], 
					$track['route_max_y'])
				),

			'route_min_coord' => $this->_db->func('ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)', 
				array(
					$track['route_min_x'], 
					$track['route_min_y']
				)),

			'route_max_coord' => $this->_db->func('ST_GeomFromText(ST_AsText(POINT(?, ?)), 3857)',
				array(
					$track['route_max_x'], 
					$track['route_max_y']
				)),

			'route_min_alt' => $track['route_min_alt'],
			'route_max_alt' => $track['route_max_alt'],

			'route_track_created_at' => $track['route_track_created_at'],
			'route_track_modified_at' => $track['route_track_modified_at'],
			'route_track_modified_by' => $track['route_track_modified_by']
		));
	}

	private function _getRouteTrackTableName() {
		return $this->_getEnv()
			->getRouteTrackTableName();
	}

	public function clearRouteTrack() {
		$this->_truncateTables($this->_db, 
			$this->_getRouteTrackTableName());
	}

	public function clearPostsTable() {
		$this->_truncateTables($this->_db, 
			$this->_getWpPostsTableName());
	}

	private function _getWpPostsTableName() {
		return $this->_getEnv()
			->getWpPostsTableName();
	}

	public function clearAll() {
		$this->clearRouteInfo();
		$this->clearRouteInfoLookupAssociations();
		$this->clearRouteTrack();
		$this->clearPostsTable();
	}
}