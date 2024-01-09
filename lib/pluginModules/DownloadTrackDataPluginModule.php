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
class Abp01_PluginModules_DownloadTrackDataPluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_Settings
	 */
	private $_settings;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_downloadGpxTrackDataAction;

	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	/**
	 * @var Abp01_Route_Track_FileNameProvider
	 */
	private $_trackFileNameProvider;

	public function __construct(Abp01_Route_Manager $routeManager,
		Abp01_Route_Track_FileNameProvider $trackFileNameProvider,
		Abp01_NonceProvider_DownloadTrackData $downloadTrackDataNonceProvider, 
		Abp01_Settings $settings, 
		Abp01_Env $env, 
		Abp01_Auth $auth) {

		parent::__construct($env, $auth);

		$this->_settings = $settings;
		$this->_routeManager = $routeManager;
		$this->_trackFileNameProvider = $trackFileNameProvider;

		$this->_initAjaxActions($downloadTrackDataNonceProvider);
	}

	private function _initAjaxActions(Abp01_NonceProvider_DownloadTrackData $trackDownloadNonceProvider) {
		$this->_downloadGpxTrackDataAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_DOWNLOAD_TRACK, array($this, 'downloadGpxTrack'))
				->useCurrentResourceProvider(new Abp01_AdminAjaxAction_CurrentResourceProvider_CurrentPostId())
				->useNonceProvider($trackDownloadNonceProvider)
				->setRequiresAuthentication(false)
				->onlyForHttpGet();
	}
	
	public function load() {
		$this->_registerAjaxActions();
	}

	private function _registerAjaxActions() {
		$this->_downloadGpxTrackDataAction
			->register();
	}

	public function downloadGpxTrack() {
		$postId = $this->_getCurrentPostId();
		if (empty($postId)) {
			die;
		}
	
		if ($this->_trackDataAllowedBySettings()) {
			$this->_sendGpxTrackDataFileForPostId($postId);
		}

		die;
	}

	private function _trackDataAllowedBySettings() {
		return $this->_settings->getAllowTrackDownload();
	}

	private function _sendGpxTrackDataFileForPostId($postId) {
		$trackFileDownloader = $this->_createTrackFileDownloader();
		$trackFileDownloader->sendTrackFileForPostId($postId);
	}

	private function _createTrackFileDownloader() {
		return new Abp01_Transfer_TrackFileDownloader($this->_routeManager, 
			$this->_trackFileNameProvider);
	}
}