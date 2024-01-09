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
class Abp01_PluginModules_FrontendViewerPluginModule extends Abp01_PluginModules_PluginModule {
	const FRONTEND_VIEWER_CONTENT_HOOK_PRIORITY = 0;
	
	/**
	 * @var Abp01_Viewer
	 */
	private $_viewer;

	/**
	 * @var Abp01_Settings
	 */
	private $_settings;

	/**
	 * @var Abp01_NonceProvider_ReadTrackData
	 */
	private $_readTrackDataNonceProvider;

	/**
	 * @var Abp01_NonceProvider_DownloadTrackData
	 */
	private $_downloadTrackDataNonceProvider;

	/**
	 * @var Abp01_Viewer_DataSource
	 */
	private $_viewerDataSource;

	/**
	 * @var Abp01_TripSummaryShortcodeBlockType
	 */
	private $_tripSummaryShortCodeBlockType;

	public function __construct(Abp01_Viewer_DataSource $viewerDataSource, 
		Abp01_Viewer $viewer, 
		Abp01_Settings $settings, 
		Abp01_NonceProvider_ReadTrackData $readTrackDataNonceProvider, 
		Abp01_NonceProvider_DownloadTrackData $downloadTrackDataNonceProvider, 
		Abp01_Env $env, 
		Abp01_Auth $auth) {

		parent::__construct($env, $auth);

		$this->_viewerDataSource = $viewerDataSource;
		$this->_viewer = $viewer;
		$this->_settings = $settings;
		$this->_readTrackDataNonceProvider = $readTrackDataNonceProvider;
		$this->_downloadTrackDataNonceProvider = $downloadTrackDataNonceProvider;
		$this->_tripSummaryShortCodeBlockType = new Abp01_TripSummaryShortcodeBlockType();
	}

	public function load() {
		$this->_registerCustomBlockTypes();
		$this->_registerWebPageAssets();
		$this->_initViewerContentHooks();
		$this->_registerViewerShortCode();
	}

	private function _registerCustomBlockTypes() {
		add_action('wp', array($this, 'onPluginInitSetupCustomBlockTypes'));
	}

	public function onPluginInitSetupCustomBlockTypes() {
		if ($this->_shouldRegisterCustomBlockTypes()) {
			$this->_registerTripSummaryShortCodeBlock();
		}
	}

	private function _shouldRegisterCustomBlockTypes() {
		return $this->_shouldAddViewer();
	}

	private function _registerTripSummaryShortCodeBlock() {
		$this->_tripSummaryShortCodeBlockType
			->register();
	}

	private function _registerWebPageAssets() {
		add_action('wp_enqueue_scripts', 
			array($this, 'onFrontendEnqueueStyles'));
		add_action('wp_enqueue_scripts', 
			array($this, 'onFrontendEnqueueScripts'));
	}

	public function onFrontendEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			$this->_viewer->includeFrontendViewerStyles();
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		static $addViewerScripts = null;
	
		if ($addViewerScripts === null) {
			$addViewerScripts = false;
			if ($this->_shouldAddViewer()) {
				$postId = $this->_getCurrentPostId();
				$addViewerScripts = $this->_postHasAnyTripSummaryData($postId);
			}
		}
	
		return $addViewerScripts;
	}

	private function _shouldAddViewer() {
		return is_single() || is_page();
	}

	private function _postHasAnyTripSummaryData($postId) {
		$hasData = false;
		$statusInfo = $this->_viewerDataSource->getTripSummaryStatusInfo($postId);
	
		if (!empty($statusInfo[$postId])) {
			$statusInfo = $statusInfo[$postId];
			$hasData = ($statusInfo['has_route_details'] || $statusInfo['has_route_track']);
		}

		return $hasData;
	}

	public function onFrontendEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			$this->_viewer->includeFrontendViewerScripts($this->_getFrontendViewerScriptTranslations());
		}
	}

	private function _getFrontendViewerScriptTranslations() {
		return Abp01_TranslatedScriptMessages::getFrontendViewerScriptTranslations();
	}

	function _initViewerContentHooks() {	
		$this->_removeWpAutoPFilterIfPresent();
		$this->_registerViewerContentHook();
	}

	private function _removeWpAutoPFilterIfPresent() {
		$priority = has_filter('the_content', 'wpautop');
		if ($priority !== false) {
			remove_filter('the_content', 'wpautop', $priority);
		}
	}

	private function _registerViewerContentHook() {
		add_filter('the_content', 
			array($this, 'addViewerToContent'), 
			self::FRONTEND_VIEWER_CONTENT_HOOK_PRIORITY);
	}

	private function _registerViewerShortCode() {
		add_shortcode(ABP01_VIEWER_SHORTCODE, array($this, 'renderViewerShortCode'));
	}

	public function addViewerToContent($postContent) {
		$postContent = wpautop($postContent);

		if ($this->_shouldAddViewer()) {
			$postId = $this->_getCurrentPostId();
			if (!empty($postId)) {
				$viewerData = $this->_getViewerData($postId);
				$postContent = $this->_viewer->renderAndAttachToContent($viewerData, $postContent);
			}			
		}

		return $postContent;
	}

	private function _getViewerData($postId) {
		$viewerData = $this->_viewerDataSource
			->getTripSummaryViewerData($postId);

		$viewerData->ajaxUrl = $this->_getAjaxBaseUrl();
		$viewerData->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;
		$viewerData->downloadTrackAction = ABP01_ACTION_DOWNLOAD_TRACK;
		$viewerData->imgBaseUrl = $this->_getPluginMediaImgBaseUrl();

		$viewerData->nonceGet = $this->_readTrackDataNonceProvider
			->generateNonce($postId);
		$viewerData->nonceDownload = $this->_downloadTrackDataNonceProvider
			->generateNonce($viewerData->postId);

		$viewerData->settings = $this->_settings
			->asPlainObject();

		$viewerData->additionalTabs = $this->_getAdditionalTabs($postId);

		return $viewerData;
	}

	private function _getAdditionalTabs($postId) {
		$additionalTabs = array();
		return apply_filters('abp01_additional_frontend_viewer_tabs', 
			$additionalTabs, 
			$postId);
	}

	public function renderViewerShortCode($attributes) {
		$content = '';
		$postId = $this->_getCurrentPostId();
	
		if (!empty($postId)) {
			$viewerData = $this->_getViewerData($postId);
			$contentParts = $this->_viewer->render($viewerData);
			$content = $contentParts['viewerHtml'];
		}
	
		return $content;
	}
}