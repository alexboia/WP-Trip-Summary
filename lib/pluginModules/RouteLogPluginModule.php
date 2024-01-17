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
class Abp01_PluginModules_RouteLogPluginModule extends Abp01_PluginModules_PluginModule {
	const LOG_METABOX_REGISTRATION_HOOK_PRIORITY = 10;

	const LOG_METABOX_POSITION = 'normal';

	const LOG_METABOX_PRIORITY = 'high';
	
	/**
	 * @var Abp01_Route_Log_Manager
	 */
	private $_routeLogManager;

	/**
	 * @var Abp01_View
	 */
	private $_view;

	public function __construct(Abp01_Route_Log_Manager $routeLogManager,
			Abp01_View $view, 
			Abp01_Env $env, 
			Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		$this->_routeLogManager = $routeLogManager;
		$this->_view = $view;
	}

	public function load() {
		if ($this->_tripSummaryLogEnabled()) {
			$this->_registerAdminWebPageAssets();
			$this->_registerEditorControls();
			$this->_setupViewer();
		}
	}

	private function _registerAdminWebPageAssets() {
		if (is_admin()) {
			add_action('admin_enqueue_scripts', 
				array($this, 'onAdminEnqueueStyles'));
			add_action('admin_enqueue_scripts', 
				array($this, 'onAdminEnqueueScripts'));
		}		
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets(true)) {
			Abp01_Includes::includeStyleAdminLogEntries();
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		$isEditingPost = $this->_env->isEditingWpPost(Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes());
		return $isEditingPost
			&& $this->_canEditCurrentPostTripSummary();
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets(true)) {
			Abp01_Includes::includeScriptAdminLogEntries($this->_getAdminTripSummaryAdminLogEntriesTranslations());
		}
	}

	private function _getAdminTripSummaryAdminLogEntriesTranslations() {
		return Abp01_TranslatedScriptMessages::getAdminTripSummaryAdminLogEntriesTranslations();
	}

	private function _tripSummaryLogEnabled() {
		$enabled = defined('ABP01_TRIP_SUMMARY_LOG_ENABLED')
			? constant('ABP01_TRIP_SUMMARY_LOG_ENABLED') === true
			: true;

		return apply_filters('abp01_trip_summary_log_enabled', $enabled) 
			=== true; 
	}

	private function _setupViewer() {
		add_filter('abp01_additional_frontend_viewer_tabs', 
			array($this, 'addRouteLogFrontendViewerTab'), 
			10, 
			2);

		add_action('abp01_additional_frontend_viewer_tab_content', 
			array($this, 'renderRouteLogFrontendViewerTabContent'),
			10, 
			3);
	}

	public function addRouteLogFrontendViewerTab(array $additionalTabs, $postId) {
		$additionalTabs['abp01-route-log'] = array(
			'icon' => 'dashicons-welcome-write-blog',
			'label' => __('Log','abp01-trip-summary')
		);

		return $additionalTabs;
	}

	public function renderRouteLogFrontendViewerTabContent($tabId, array $tabInfo, stdClass $viewerData) {
		if ($tabId != 'abp01-route-log') {
			return;
		}

		$data = new stdClass();
		echo $this->_view->renderRouteLogFrontendViewerTabContent($data);
	}

	private function _registerEditorControls() {
		add_action('add_meta_boxes', array($this, 'registerAdminEditorLogMetaboxes'), 
			self::LOG_METABOX_REGISTRATION_HOOK_PRIORITY, 
			2);
	}

	public function registerAdminEditorLogMetaboxes($postType, $post) { 
		if ($this->_shouldRegisterAdminEditorLogMetaboxes($postType, $post)) {
			add_meta_box('abp01-enhanced-editor-log-metabox', 
				__('Trip summary log', 'abp01-trip-summary'),
				array($this, 'addAdminLogEditor'), 
				$postType, 
				self::LOG_METABOX_POSITION, //TODO modifiable via hook, but no sidebar
				self::LOG_METABOX_PRIORITY, //TODO modifiable via hook
				array(
					'postType' => $postType,
					'post' => $post
				)
			);
		}
	}

	private function _shouldRegisterAdminEditorLogMetaboxes($postType, $post) {
		return Abp01_AvailabilityHelper::isEditorAvailableForPostType($postType) 
			&& $this->_cantEditPostTripSummary($post);
	}

	public function addAdminLogEditor($post, $args) {
		if ($this->_cantEditPostTripSummary($post)) {
			$this->_addAdminLogEditorForm($post, $args);
		}
	}

	private function _addAdminLogEditorForm($post, $args) {
		$postId = intval($post->ID);
		
		$data = new stdClass();
		$data->postId = $postId;
		
		$log = $this->_routeLogManager->getAdminLog($postId);
		$data->log = $log->toPlainObject();
		$data->hasLogEntries = $log->hasLogEntries();
		$data->logEntryCount = $log->getLogEntryCount();

		echo $this->_view->renderAdminTripSummaryLogEditor($data);
	}
}