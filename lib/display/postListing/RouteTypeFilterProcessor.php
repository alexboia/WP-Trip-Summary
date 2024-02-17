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

class Abp01_Display_PostListing_RouteTypeFilterProcessor implements Abp01_Display_PostListing_FilterProcessor {
	public function processFilter(Abp01_Display_PostListing_Filter $filter): void {
		add_filter('posts_join', 
			function($join, WP_Query $query) use ($filter): string {
				return $this->_processQueryJoin($join, $query, $filter->getCurrentValue());
			}, 10, 2);

		add_filter('posts_clauses', 
			function(array $clauses, WP_Query $query) use ($filter): array {
				return $this->_processQueryClauses($clauses, $query, $filter->getCurrentValue());
			}, 10, 2);
	}

	private function _processQueryJoin($join, WP_Query $query, $currentValue): string {
		$postsTableName = $this->_getPostsTableName();
		$routeDetailsTableName = $this->_getRouteDetailsTableName();

		if (!empty($currentValue)) {
			$join .= ' LEFT JOIN `' . $routeDetailsTableName . '` `abp01rd` ON `' . $postsTableName . '`.`ID` = `abp01rd`.`post_ID`';
		}

		return $join;
	}

	private function _processQueryClauses(array $clauses, WP_Query $query, $currentValue): array {
		if (!empty($currentValue)) {
			$clauses['where'] = " AND `abp01rd`.`route_type` = '" . esc_sql($currentValue) . "'";
		}
		return $clauses;	
	}
	
	private function _getRouteDetailsTableName() {
		return abp01_get_env()->getRouteDetailsTableName();
	}

	private function _getPostsTableName() {
		return abp01_get_env()->getWpPostsTableName();
	}
}