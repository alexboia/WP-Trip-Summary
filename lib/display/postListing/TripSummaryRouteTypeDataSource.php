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

class Abp01_Display_PostListing_TripSummaryRouteTypeDataSource implements Abp01_Display_PostListing_ColumnDataSource {
	private $_routeManager;

	public function __construct(Abp01_Route_Manager $routeManager) {
		$this->_routeManager = $routeManager;
	}

    public function getValue($postId) { 
		$routeType = $this->_getTripSummaryRouteTypeInfoForPostId($postId);
		return $routeType;
	}

	private function _getTripSummaryRouteTypeInfoForPostId($postId) {
		$allRouteTypeInfo = $this->_getTripSummaryRouteTypeInfoForCurrentWpQuery();
		$postRouteTypeInfo = isset($allRouteTypeInfo[$postId]) 
			? $allRouteTypeInfo[$postId]
			: '';
	
		return $postRouteTypeInfo;
	}

	private function _getTripSummaryRouteTypeInfoForCurrentWpQuery() {
		$query = $this->_getCurrentWpQuery();
		$allRouteTypeInfo = $query != null
			? $this->_getPostsTripSummaryRouteTypeInfo($query->posts)
			: array();

		return $allRouteTypeInfo;
	}

	private function _getPostsTripSummaryRouteTypeInfo($posts) {
		$postIds = array();
		$allRouteTypeInfo = array();
	
		//extract post IDs
		$postIds = abp01_extract_post_ids($posts);
		if (!empty($postIds)) {
			//Attempt to extract any cached data
			$cacheKey = $this->_getPostsTripSummaryRouteTypeInfoCacheKey($postIds);
			$allRouteTypeInfo = $this->_retrieveCachedTripSummaryRouteTypeInfo($cacheKey);
	
			//If there is no status information cached, fetch it
			if (!is_array($allRouteTypeInfo)) {
				$allRouteTypeInfo = $this->_routeManager
					->getTripSummaryRouteTypeInfo($postIds);
				$this->_cacheTripSummaryRouteTypeInfo($cacheKey, 
					$allRouteTypeInfo);
			}
		}
	
		return $allRouteTypeInfo;
	}

	private function _getPostsTripSummaryRouteTypeInfoCacheKey($postIds) {
		return sprintf('_abp01_posts_listing_route_type_info_%s', sha1(join('_', $postIds)));
	}

	private function _retrieveCachedTripSummaryRouteTypeInfo($cacheKey) {
		return get_transient($cacheKey);
	}

	private function _cacheTripSummaryRouteTypeInfo($cacheKey, $statusInfo) {
		set_transient($cacheKey, $statusInfo, MINUTE_IN_SECONDS / 2);
	}

	private function _getCurrentWpQuery(): WP_Query|null {
		return isset($GLOBALS['wp_query'])
			? $GLOBALS['wp_query'] 
			: null;
	}
}