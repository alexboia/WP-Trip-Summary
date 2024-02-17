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

class Abp01_Display_PostListing_TripSummaryRouteTypeColumn extends Abp01_Display_PostListing_Column {
	public function __construct($key, $label, Abp01_Display_PostListing_ColumnDataSource $dataSource) {
		parent::__construct($key, $label, $dataSource);
	}

	public function renderValue($postId) {
		$routeType = parent::renderValue($postId);

		$label = $this->_getRouteTypeLabel($postId, 
			$routeType);

		return $this->_formatRouteTypeLabel($postId, 
			$routeType, 
			$label);
	}

	private function _formatRouteTypeLabel($postId, $routeType, $routeTypeLabel) {
		$cssClass = sprintf('abp01-route-type-cell abp01-route-type-cell-%s', !empty($routeType) 
			? esc_attr($routeType)
			: 'none');
		$formatted = '<span class="' . $cssClass . '">' . $routeTypeLabel . '</span>';

		return apply_filters('abp01_formatted_route_tyle_listing_label', 
			$formatted, 
			$postId, 
			$routeType, 
			$routeTypeLabel);
	}

	private function _getRouteTypeLabel($postId, $routeType) {
		$routeTypeLabel = '';
		if (!empty($routeType)) {
			switch ($routeType) {
				case Abp01_Route_Info::BIKE:
					$routeTypeLabel = __('Biking', 'abp01-trip-summary');
					break;
				case Abp01_Route_Info::HIKING:
					$routeTypeLabel = __('Hiking', 'abp01-trip-summary');
					break;
				case Abp01_Route_Info::TRAIN_RIDE:
					$routeTypeLabel = __('Train Ride', 'abp01-trip-summary');
					break;
			}
		} else {
			$routeTypeLabel = '-';
		}		

		return apply_filters('abp01_unformatted_route_type_label', 
			$routeTypeLabel, 
			$postId,
			$routeType);
	}

	public function renderLabel() {
		return parent::renderLabel();
	}

	public function getKey() {
		return parent::getKey();
	}
}