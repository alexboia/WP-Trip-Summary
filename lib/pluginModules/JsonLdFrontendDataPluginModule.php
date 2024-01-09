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
class Abp01_PluginModules_JsonLdFrontendDataPluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_Settings
	 */
	private $_settings;

	/**
	 * @var Abp01_Viewer_DataSource
	 */
	private $_viewerDataSource;

	/**
	 * @var Abp01_View
	 */
	private $_view;
	
	public function __construct(Abp01_Settings $settings, 
		Abp01_Viewer_DataSource $viewerDataSource, 
		Abp01_View $view, 
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_settings = $settings;
		$this->_viewerDataSource = $viewerDataSource;
		$this->_view = $view;
	}

	public function load() {
		if ($this->_jsonLdFrontendDataEnabled()) {
			add_action('wp_head', array($this, 'includeJsonLdFrontendData'));
		}
	}

	private function _jsonLdFrontendDataEnabled() {
		return $this->_settings->getEnableJsonLdFrontenData();
	}

	public function includeJsonLdFrontendData() {
		if ($this->_isPostDetailsPage()) {
			$postId = $this->_getCurrentPostId();
			if ($postId > 0) {
				$this->_includeJsonLdFrontendData($postId);
			}
		}
	}

	private function _isPostDetailsPage() {
		return is_single() || is_page();
	}

	private function _includeJsonLdFrontendData($postId) {
		$viewerData = $this->_getTripSummaryViewerData($postId);
		if ($this->_hasTrackData($viewerData)) {
			/** @var WP_Post $post */
			$post = get_post($postId);
			$bounds = $viewerData->track->summary->bounds;
			
			$data = new stdClass();
			$data->name = $post->post_title;
			$data->southWest = $bounds->southWest;
			$data->northEast = $bounds->northEast;

			echo $this->_renderJsonLdFrontendData($data);
		}
	}

	private function _getTripSummaryViewerData($postId) {
		return  $this->_viewerDataSource->getTripSummaryViewerData($postId);
	}

	private function _hasTrackData($viewerData) {
		return !empty($viewerData) 
			&& !empty($viewerData->track) 
			&& $viewerData->track->exists;
	}

	private function _renderJsonLdFrontendData(stdClass $data) {
		return $this->_view->renderJsonLdFrontendData($data);
	}
}