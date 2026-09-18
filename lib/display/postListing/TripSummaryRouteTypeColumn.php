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

	private function _formatRouteTypeLabel(?int $postId, ?string $routeType, ?string $routeTypeLabel) {
		$cssClass = sprintf('abp01-route-type-cell abp01-route-type-cell-%s', 
			!empty($routeType) 
				? esc_attr($routeType)
				: 'none');

		$formatted = '<span class="' . $cssClass . '">' . $routeTypeLabel . '</span>';

		/**
		 * Filters the formatted route type label displayed in the post listing.
		 * Initial value is a span containing the unformatted label, with the base
		 * CSS class abp01-route-type-cell and a route-specific or none modifier class.
		 *
		 * @since 0.3.2
		 * @category Trip Summary Management
		 *
		 * @param string $formatted The formatted route type label HTML.
		 * @param int|null $postId The post identifier.
		 * @param string|null $routeType The route type code, or null/empty when none is assigned.
		 * @param string|null $routeTypeLabel The unformatted route type label after filtering.
		 */
		$filteredFormattedLabel = apply_filters('abp01_formatted_route_type_listing_label', 
			$formatted, 
			$postId, 
			$routeType, 
			$routeTypeLabel);

		return $filteredFormattedLabel;
	}

	private function _getRouteTypeLabel(?int $postId, ?string $routeType): ?string {
		$routeTypeLabel = '';
		if (!empty($routeType)) {
			$routeTypeLabel = Abp01_Route_Type::getTypeLabel($routeType);
		} else {
			$routeTypeLabel = '-';
		}

		/**
		 * Filters the unformatted (i.e. no HTML) route type label displayed in the post listing.
		 * Initial value is the translated route type label, a dash when no route type
		 * is assigned, or null when the route type is unknown.
		 * 
		 * If the return value is not a string, the initial value will be used.
		 *
		 * @since 0.3.2
		 * @category Trip Summary Management
		 *
		 * @param string|null $routeTypeLabel The unformatted route type label.
		 * @param int|null $postId The post identifier.
		 * @param string $routeType The route type code, or an empty string when none is assigned.
		 */
		$filteredRouteTypeLabel = apply_filters('abp01_unformatted_route_type_label',
			$routeTypeLabel,
			$postId,
			$routeType);

		if (!is_string($filteredRouteTypeLabel)) {
			$filteredRouteTypeLabel = $routeTypeLabel;
		}

		return $filteredRouteTypeLabel;
	}

	public function renderLabel() {
		return parent::renderLabel();
	}

	public function getKey() {
		return parent::getKey();
	}
}
