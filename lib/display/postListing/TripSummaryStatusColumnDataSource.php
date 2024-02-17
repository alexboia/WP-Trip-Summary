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
	exit ;
}

class Abp01_Display_PostListing_TripSummaryStatusColumnDataSource implements Abp01_Display_PostListing_ColumnDataSource {
	/**
	 * @var array
	 */
	private static $_tripSummaryStatusInfoForCurrentWpQuery = null;
	
	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	private $_dataKey;
	
	public function __construct(Abp01_Route_Manager $routeManager, $dataKey) {
		$this->_routeManager = $routeManager;
		$this->_dataKey = $dataKey;
	}

	public function getValue($postId) {
		$postStatusInfo = $this->_getTripSummaryStatusInfoForPostId($postId);
		return isset($postStatusInfo[$this->_dataKey]) 
			? $postStatusInfo[$this->_dataKey] 
			: false;
	}

	private function _getTripSummaryStatusInfoForPostId($postId) {
		$allStatusInfo = $this->_getTripSummaryStatusInfoForCurrentWpQuery();
		$postStatusInfo = isset($allStatusInfo[$postId]) 
			? $allStatusInfo[$postId]
			: array();
	
		return $postStatusInfo;
	}

	private function _getTripSummaryStatusInfoForCurrentWpQuery() {
		if (!$this->_isTripSummaryStatusInfoCachedForCurrentWpQuery()) {
			$query = $this->_getCurrentWpQuery();
			$allStatusInfo = $query != null
				? $this->_getPostsTripSummaryStatusInfo($query->posts)
				: array();

			$this->_cacheTripSummaryStatusInfoForCurrentWpQuery($allStatusInfo);
		}

		return $this->_retrieveCachedTripSummaryStatusInfoForCurrentWpQuery();
	}

	private function _isTripSummaryStatusInfoCachedForCurrentWpQuery() {
		return self::$_tripSummaryStatusInfoForCurrentWpQuery !== null;
	}

	private function _cacheTripSummaryStatusInfoForCurrentWpQuery($allStatusInfo) {
		self::$_tripSummaryStatusInfoForCurrentWpQuery = $allStatusInfo;
	}

	private function _retrieveCachedTripSummaryStatusInfoForCurrentWpQuery() {
		return self::$_tripSummaryStatusInfoForCurrentWpQuery;
	}

	private function _getCurrentWpQuery() {
		return isset($GLOBALS['wp_query'])
			? $GLOBALS['wp_query'] 
			: null;
	}

	private function _getPostsTripSummaryStatusInfo($posts) {
		$postIds = array();
		$allStatusInfo = array();
	
		//extract post IDs
		$postIds = abp01_extract_post_ids($posts);
		if (!empty($postIds)) {
			//Attempt to extract any cached data
			$cacheKey = $this->_getPostsTripSummaryStatusInfoCacheKey($postIds);
			$allStatusInfo = $this->_retrieveCachedTripSummaryStatusInfo($cacheKey);
	
			//If there is no status information cached, fetch it
			if (!is_array($allStatusInfo)) {
				$allStatusInfo = $this->_routeManager
					->getTripSummaryStatusInfo($postIds);
				$this->_cacheTripSummaryStatusInfo($cacheKey, 
					$allStatusInfo);
			}
		}
	
		return $allStatusInfo;
	}

	private function _getPostsTripSummaryStatusInfoCacheKey($postIds) {
		return sprintf('_abp01_posts_listing_info_%s', sha1(join('_', $postIds)));
	}

	private function _retrieveCachedTripSummaryStatusInfo($cacheKey) {
		return get_transient($cacheKey);
	}

	private function _cacheTripSummaryStatusInfo($cacheKey, $statusInfo) {
		set_transient($cacheKey, $statusInfo, MINUTE_IN_SECONDS / 2);
	}
}