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

/**
 * @package WP-Trip-Summary
 */
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

	public function __construct(Abp01_Route_Manager $routeManager, 
			Abp01_Lookup $lookup, 
			Abp01_Viewer_DataSource_Cache $cache) {
		$this->_routeManager = $routeManager;
		$this->_lookup = $lookup;
		$this->_cache = $cache;
	}

	public function getTripSummaryViewerData($postId) {
		$viewerData = $this->_cache->readCachedTripSummaryViewerData($postId);
		if (empty($viewerData)) {
			$viewerData = $this->_getTripSummaryViewerData($postId);
			$this->_cache->cachePostTripSummaryViewerData($postId, $viewerData);
		}

		return $viewerData;
	}

	private function _getTripSummaryViewerData($postId) {
		$viewerData = new stdClass();
		$viewerData->postId = $postId;
		$viewerData->info = $this->_getRouteInfoData($postId);
		$viewerData->track = $this->_getRouteTrackData($postId);
		return $viewerData;
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

			$valueTranslator = $this->_getRouteInfoValueTranslator();
			foreach ($routeInfo->getData() as $field => $value) {
				$routeInfoData->$field = $valueTranslator
					->translateFieldValue($routeInfo, 
						$field, 
						$value);
			}
		}

		return $routeInfoData;
	}

	private function _getRouteInfoValueTranslator() {
		return new Abp01_Route_Info_ValueTranslator($this->_lookup);
	}

	private function _getRouteTrackData($postId) {
		$routeTrackData = new stdClass();
		$track = $this->_routeManager->getRouteTrack($postId);

		$routeTrackData->exists = !empty($track);
		$routeTrackData->summary = !empty($track) 
			? $track->toPlainObject() 
			: null;

		return $routeTrackData;
	}

	public function getTripSummaryStatusInfo($postId) {
		return $this->_routeManager->getTripSummaryStatusInfo($postId);
	}
}