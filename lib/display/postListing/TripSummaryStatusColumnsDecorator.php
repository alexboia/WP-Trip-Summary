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

class Abp01_Display_PostListing_TripSummaryStatusColumnsDecorator extends Abp01_Display_PostListing_ColumnCustomization {
	public function __construct() {
		parent::__construct($this->_getColumns(), $this->_getPostTypes());
	}

	private function _getColumns(): array {
		$routeManager = $this->_getRouteManager();
		return array(
			new Abp01_Display_PostListing_TripSummaryStatusColumn(
				'abp01_trip_summary_info_status', 
				esc_html__('Trip summary info', 'abp01-trip-summary'), 
				new Abp01_Display_PostListing_TripSummaryStatusColumnDataSource(
					$routeManager, 
					'has_route_details'
				)
			),

			new Abp01_Display_PostListing_TripSummaryStatusColumn(
				'abp01_trip_summary_track_status', 
				esc_html__('Trip summary track', 'abp01-trip-summary'), 
				new Abp01_Display_PostListing_TripSummaryStatusColumnDataSource(
					$routeManager, 
					'has_route_track'
				)
			)
		);
	}

	private function _getRouteManager(): Abp01_Route_Manager {
		return abp01_get_route_manager();
	}

	private function _getPostTypes(): array {
		return Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes();
	}
}