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
class Abp01_PluginModules_RestApiEnhancementsPluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_Rest_DataSource
	 */
	private $_restDataSource;
	
	public function __construct(Abp01_Rest_DataSource $restDataSource, 
			Abp01_Env $env, 
			Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		
		$this->_restDataSource = $restDataSource;
	}

	public function load() {
		add_action('rest_api_init', 
			array($this, 'initRestApi'));
	}

	public function initRestApi() {
		if ($this->_shouldAddTripSummaryToRestApi()) {
			$this->_registerTripSummaryRestApiFields();
		}		
	}

	private function _shouldAddTripSummaryToRestApi() {
		return apply_filters('abp01_add_trip_summary_to_rest_api', 
			true);
	}

	private function _registerTripSummaryRestApiFields() {
		$objectTypes = Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes();
		register_rest_field($objectTypes, 'wpts_trip_summary', array(
			'get_callback' => function($object, $fieldName, $request, $objectType) {
				return $this->_getTripSummaryFieldData($object, 
					$fieldName, 
					$request, 
					$objectType);
			},
			'update_callback' => null,
			'schema' => array(
				'description' => __('WP Trip Summary Info', 'abp01-trip-summary'),
				'type' => 'object'
			),
		));
	}

	private function _getTripSummaryFieldData($object, $fieldName, $request, $objectType) {
		$postId = $this->_getObjectId($object);
		if ($postId <= 0) {
			return null;
		}

		/** @var WP_REST_Request $request */
		$requestPostId = intval($request->get_param('id'));
		if (!$this->_shouldAddTripSummaryToRestApiListing() && $postId !== $requestPostId) {
			return null;
		}

		return $this->_getTripSummaryData($postId);
	}

	private function _getObjectId($object) {
		return  isset($object['id']) 
			? intval($object['id']) 
			: 0;
	}

	private function _shouldAddTripSummaryToRestApiListing() {
		return apply_filters('abp01_add_trip_summary_to_rest_api_listing', 
			false);
	}

	private function _getTripSummaryData($postId) {
		return $this->_restDataSource
			->getPostTripSummaryData($postId);
	}
}