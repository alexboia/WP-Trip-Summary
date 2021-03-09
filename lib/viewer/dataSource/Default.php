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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Viewer_DataSource_Default implements Abp01_Viewer_DataSource {
	/**
	 * @var Abp01_Lookup
	 */
	private $_lookup;
	
	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	/**
	 * @var Abp01_Viewer_DataSource_Cache
	 */
	private $_cache;

	public function __construct(Abp01_Route_Manager $routeManager, Abp01_Lookup $lookup, Abp01_Viewer_DataSource_Cache $cache) {
		$this->_routeManager = $routeManager;
		$this->_lookup = $lookup;
		$this->_cache = $cache;
	}

	public function getTripSummaryViewerData($postId) {
		$data = $this->_cache->readCachedTripSummaryViewerData($postId);
		if (empty($data)) {
			$data = $this->_getTripSummaryViewerData($postId);
			$this->_cache->cachePostTripSummaryViewerData($postId, $data);
		}

		return $data;
	}

	private function _getTripSummaryViewerData($postId) {
		$data = new stdClass();
		$data->info = $this->_getRouteInfoData($postId);
		$data->track = $this->_getRouteTrackData($postId);
		return $data;
	}

	private function _getRouteInfoData($postId) {
		$routeInfoData = new stdClass();
		$routeInfoData->exists = false;

		$routeInfo = $this->_routeManager->getRouteInfo($postId);
		if (!empty($routeInfo)) {
			$routeInfoData->exists = true;
			$routeInfoData->isBikingTour = $routeInfo->isBikingTour();
			$routeInfoData->isHikingTour = $routeInfo->isHikingTour();
			$routeInfoData->isTrainRideTour = $routeInfo->isTrainRideTour();

			foreach ($routeInfo->getData() as $field => $value) {
				$lookupKey = $routeInfo->getLookupKey($field);
				if ($lookupKey) {
					if (is_array($value)) {
						foreach ($value as $k => $v) {
							$value[$k] = $this->_lookup->lookup($lookupKey, $v);
						}
						$value = array_filter($value, 'abp01_is_not_empty');
					} else {
						$value = $this->_lookup->lookup($lookupKey, $value);
					}
				}
			
				$routeInfoData->$field = $value;
			}
		}

		return $routeInfoData;
	}

	private function _getRouteTrackData($postId) {
		$routeTrackData = new stdClass();
		$routeTrackData->exists = $this->_routeManager->hasRouteTrack($postId);
		return $routeTrackData;
	}
}