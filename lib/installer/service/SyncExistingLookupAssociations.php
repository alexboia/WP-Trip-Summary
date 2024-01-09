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

class Abp01_Installer_Service_SyncExistingLookupAssociations {
	/**
	 * @var Abp01_Env
	 */
	private $_env;
	
	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	public function execute() {
		$db = $this->_env->getDb();
		if (!$db) {
			return false;
		}
		
		$tableName = $this->_getRouteDetailsLookupTableName();
		$detailsTableName = $this->_getRouteDetailsTableName();

		//remove all existing entries
		if ($db->rawQuery('TRUNCATE TABLE `' . $tableName . '`', null, false) === false) {
			return false;
		}

		//extract the current values
		$data = $db->rawQuery('SELECT post_ID, route_data_serialized FROM `' . $detailsTableName . '`');
		if (!is_array($data)) {
			return false;
		}

		foreach ($data as $row) {
			$postId = intval($row['post_ID']);
			if (empty($row['route_data_serialized'])) {
				continue;
			}

			$routeDetails = json_decode($row['route_data_serialized'], false);
			if (!empty($routeDetails->bikeRecommendedSeasons)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->bikeRecommendedSeasons);
			}

			if (!empty($routeDetails->bikePathSurfaceType)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->bikePathSurfaceType);
			}

			if (!empty($routeDetails->bikeBikeType)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->bikeBikeType);
			}

			if (!empty($routeDetails->bikeDifficultyLevel)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->bikeDifficultyLevel);
			}

			if (!empty($routeDetails->hikingDifficultyLevel)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->hikingDifficultyLevel);
			}

			if (!empty($routeDetails->hikingRecommendedSeasons)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->hikingRecommendedSeasons);
			}

			if (!empty($routeDetails->hikingSurfaceType)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->hikingSurfaceType);
			}

			if (!empty($routeDetails->trainRideOperator)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->trainRideOperator);
			}

			if (!empty($routeDetails->trainRideLineStatus)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->trainRideLineStatus);
			}

			if (!empty($routeDetails->trainRideElectrificationStatus)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->trainRideElectrificationStatus);
			}

			if (!empty($routeDetails->trainRideLineType)) {
				$this->_addLookupAssociation($db, 
					$tableName, 
					$postId, 
					$routeDetails->trainRideLineType);
			}
		}

		return true;
	}

	private function _addLookupAssociation($db, $tableName, $postId, $lookupId) {
		if (is_array($lookupId)) {
			foreach ($lookupId as $id) {
				$this->_addLookupAssociation($db, $tableName, $postId, $id);
			}
		} else {
			$db->insert($tableName, array(
				'lookup_ID' => $lookupId,
				'post_ID' => $postId
			));
		}
	}

	private function _getRouteDetailsTableName() {
		return $this->_env->getRouteDetailsTableName();
	}

	private function _getRouteDetailsLookupTableName() {
		return $this->_env->getRouteDetailsLookupTableName();
	}
}