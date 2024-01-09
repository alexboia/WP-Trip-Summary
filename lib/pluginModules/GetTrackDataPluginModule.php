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
class Abp01_PluginModules_GetTrackDataPluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_Settings
	 */
	private $_settings;

	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	/**
	 * @var Abp01_Route_Track_Processor
	 */
	private $_routeTrackProcessor;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_getTrackDataAjaxAction;

	public function __construct(Abp01_Route_Manager $routeManager, 
		Abp01_Route_Track_Processor $routeTrackProcessor,
		Abp01_NonceProvider_ReadTrackData $readTrackDataNonceProvider,
		Abp01_Settings $settings, 
		Abp01_Env $env, 
		Abp01_Auth $auth) {

		parent::__construct($env, $auth);

		$this->_settings = $settings;
		$this->_routeManager = $routeManager;
		$this->_routeTrackProcessor = $routeTrackProcessor;

		$this->_initAjaxActions($readTrackDataNonceProvider);
	}

	private function _initAjaxActions(Abp01_NonceProvider_ReadTrackData $readTrackDataNonceProvider) {
		$this->_getTrackDataAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_GET_TRACK, array($this, 'getTrackData'))
				->useCurrentResourceProvider(new Abp01_AdminAjaxAction_CurrentResourceProvider_CurrentPostId())
				->useNonceProvider($readTrackDataNonceProvider)
				->setRequiresAuthentication(false)
				->onlyForHttpGet();
	}

	public function load() {
		$this->_registerAjaxActions();
	}

	private function _registerAjaxActions() {
		$this->_getTrackDataAjaxAction
			->register();
	}

	public function getTrackData() {
		$postId = $this->_getCurrentPostId();
		if (empty($postId)) {
			die;
		}
	  
		$response = abp01_get_ajax_response();
		$targetUnitSystem = $this->_settings->getUnitSystem();

		$track = $this->_routeManager->getRouteTrack($postId);
		if (!empty($track)) {
			$trackDocument = $this->_routeTrackProcessor->getOrCreateDisplayableTrackDocument($track);
			if (empty($trackDocument)) {
				$response->message = esc_html__('Track file not found or is not readable', 'abp01-trip-summary');
			} else {
				$response->success = true;
			}
		}

		if ($response->success) {
			$response->info = new stdClass();
			$response->profile = new stdClass();
			$response->track = $trackDocument->toPlainObject();

			//Only go through the trouble of converting 
			//	these values for display if the user 
			//	has opted to show min/max altitude information
			if ($this->_settings->getShowMinMaxAltitude()) {
				$response->info = $track
					->constructDisplayableInfo($targetUnitSystem)
					->toPlainObject();
			}

			if ($this->_settings->getShowAltitudeProfile()) {
				$profile = $this->_routeTrackProcessor->getOrCreateDisplayableAltitudeProfile($track, $targetUnitSystem, 8);
				if (!empty($profile)) {
					$response->profile = $profile->toPlainObject();
				}
			}
		}

		return $response;
	}
}