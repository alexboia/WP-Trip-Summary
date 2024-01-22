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
	const TRIP_SUMMARY_LOG_EDITOR_NONCE_URL_PARAM_NAME = 'abp01_nonce_log_entry';

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

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_saveRouteLogEntryAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_deleteRouteLogEntryAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_deleteAllRouteLogEntriesAjaxAction;

	public function __construct(Abp01_Route_Log_Manager $routeLogManager,
			Abp01_View $view, 
			Abp01_Env $env, 
			Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		
		$this->_routeLogManager = $routeLogManager;
		$this->_view = $view;

		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createEditCurrentPostTripSummaryAuthCallback();
		$currentResourceProvider = new Abp01_AdminAjaxAction_CurrentResourceProvider_CurrentPostId();

		$this->_saveRouteLogEntryAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_SAVE_ROUTE_LOG_ENTRY_FOR_POST, array($this, 'saveRouteLogEntry'))
					->useDefaultNonceProvider(self::TRIP_SUMMARY_LOG_EDITOR_NONCE_URL_PARAM_NAME)
					->useCurrentResourceProvider($currentResourceProvider)
					->authorizeByCallback($authCallback)
					->onlyForHttpPost();

		$this->_deleteRouteLogEntryAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_DELETE_ROUTE_LOG_ENTRY_FOR_POST, array($this, 'deleteRouteLogEntry'))
					->useDefaultNonceProvider(self::TRIP_SUMMARY_LOG_EDITOR_NONCE_URL_PARAM_NAME)
					->useCurrentResourceProvider($currentResourceProvider)
					->authorizeByCallback($authCallback)
					->onlyForHttpPost();

		$this->_deleteAllRouteLogEntriesAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_DELETE_ALL_ROUTE_LOG_ENTRIES_FOR_POST, array($this, 'deleteAllRouteLogEntries'))
					->useDefaultNonceProvider(self::TRIP_SUMMARY_LOG_EDITOR_NONCE_URL_PARAM_NAME)
					->useCurrentResourceProvider($currentResourceProvider)
					->authorizeByCallback($authCallback)
					->onlyForHttpPost();
	}

	public function load() {
		if ($this->_tripSummaryLogEnabled()) {
			$this->_registerAjaxActions();
			$this->_registerAdminWebPageAssets();
			$this->_registerEditorControls();
			$this->_setupViewer();
		}
	}

	private function _registerAjaxActions() {
		$this->_saveRouteLogEntryAjaxAction
			->register();
		$this->_deleteRouteLogEntryAjaxAction
			->register();
		$this->_deleteAllRouteLogEntriesAjaxAction
			->register();
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
		return Abp01_FeatureStatus::tripSummaryLogEnabled();
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
		$data->defaultRider = $this->_getDefaultLogEntryRider($postId);
		$data->defaultDate = $this->_getDefaultDate($postId);
		$data->defaultVehicle = $this->_getDefaultVehicle($postId);

		$data->saveRouteLogEntryNonce = $this->_saveRouteLogEntryAjaxAction->generateNonce($postId);
		$data->ajaxSaveRouteLogEntryAction = ABP01_ACTION_SAVE_ROUTE_LOG_ENTRY_FOR_POST;

		$data->deleteRouteLogEntryNonce = $this->_deleteRouteLogEntryAjaxAction->generateNonce($postId);
		$data->ajaxDeleteRouteLogEntryAction = ABP01_ACTION_DELETE_ROUTE_LOG_ENTRY_FOR_POST;

		$data->deleteAllRouteLogEntriesNonce = $this->_deleteAllRouteLogEntriesAjaxAction->generateNonce($postId);
		$data->ajaxDeleteAllRouteLogEntriesAction = ABP01_ACTION_DELETE_ALL_ROUTE_LOG_ENTRIES_FOR_POST;

		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->imgBaseUrl = $this->_getPluginMediaImgBaseUrl();

		echo $this->_view->renderAdminTripSummaryLogEditor($data);
	}

	private function _getDefaultLogEntryRider($postId) {
		$user = wp_get_current_user();
		$displayName = $user->display_name;
		return apply_filters('abp01_trip_summary_route_log_default_entry_rider', 
			$displayName, 
			$postId);
	}

	private function _getDefaultDate($postId) {
		$defaultDate = date('Y-m-d');
		return apply_filters('abp01_trip_summary_route_log_default_entry_date', 
			$defaultDate, 
			$postId);
	}

	private function _getDefaultVehicle($postId) {
		$defaultVehicle = $this->_routeLogManager
			->getLastUsedVehicle($postId);

		return apply_filters('abp01_trip_summary_route_log_default_entry_vehicle', 
			$defaultVehicle, 
			$postId);
	}

	public function saveRouteLogEntry() {
		$postId = $this->_getCurrentPostId();
		if (empty($postId)) {
			die;
		}

		$logEntryId = Abp01_InputFiltering::getFilteredGETValue('abp01_route_log_entry_id', 'intval');
		if ($logEntryId < 0) {
			$logEntryId = 0;
		}

		$logEntry = null;
		if ($logEntryId > 0) {
			$logEntry = $this->_routeLogManager->getLogEntryById($postId, $logEntryId);
		}

		if ($logEntry === null) {
			$logEntry = new Abp01_Route_Log_Entry();
			$logEntry->id = $logEntryId = 0;
			$logEntry->postId = $postId;
			$logEntry->createdBy = get_current_user_id();
		}

		$logEntry->rider = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_rider', 'trim');
		$logEntry->date = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_date', 'trim');
		$logEntry->timeInHours = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_time', 'intval');

		$logEntry->vehicle = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_vehicle', 'trim');
		$logEntry->gear = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_gear', 'trim');
		$logEntry->notes = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_notes', 'trim');
		$logEntry->isPublic = Abp01_InputFiltering::getFilteredPOSTValue('abp01_log_ispublic', 'strtolower') 
			=== 'yes';

		$logEntry->lastUpdatedBy = get_current_user_id();
		if ($logEntry->timeInHours < 0) {
			$logEntry->timeInHours = 0;
		}

		$validationChain = new Abp01_Validation_Chain();
		$validationChain->addInputValidationRule($logEntry->rider, 
			$this->_getLogEntryRiderValidationRule());
		$validationChain->addInputValidationRule($logEntry->date, 
			$this->_getLogEntryDateValidationRule());
		$validationChain->addInputValidationRule($logEntry->vehicle, 
			$this->_getLogEntryVehicleValidationRule());

		do_action('abp01_trip_summary_log_before_save_entry', 
			$postId, 
			$logEntry);

		$response = abp01_get_ajax_response(array(
			'logEntry' => null
		));

		if (!$validationChain->isInputValid()) {
			$response->message = $validationChain->getLastValidationMessage();
			return $response;
		}

		if ($this->_routeLogManager->saveLogEntry($logEntry)) {
			$logEntry = $this->_routeLogManager->getLogEntryById($postId, $logEntry->id);
			$response->logEntry = $logEntry->toPlainObject();
			$response->formattedLogEntry = $this->_getFormattedLogEntryData($logEntry);
			$response->success = true;
		} else {
			$response->message = __('The log entry could not be saved.', 'abp01-trip-summary');
		}

		do_action('abp01_trip_summary_log_after_save_entry', 
			$postId, 
			$logEntry, 
			$response->success);

		return $response;
	}

	private function _getFormattedLogEntryData(Abp01_Route_Log_Entry $logEntry) {
		$formatted = new stdClass();
		$formatted->date = abp01_format_db_date($logEntry->date);
		$formatted->timeInHours = abp01_format_time_in_hours($logEntry->timeInHours);
		return $formatted;
	}

	private function _getLogEntryRiderValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_NotEmpty(false),
			esc_html__('The log entry rider is mandatory', 'abp01-trip-summary')
		);
	}

	private function _getLogEntryDateValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_Regex('/^([\\d]{4})-([\\d]{2})-([\\d]{2})$/', false),
			esc_html__('A valid log entry date is mandatory', 'abp01-trip-summary')
		);
	}

	private function _getLogEntryVehicleValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_NotEmpty(false),
			esc_html__('The log entry vehicle is mandatory', 'abp01-trip-summary')
		);
	}

	public function deleteRouteLogEntry() {
		$postId = $this->_getCurrentPostId();
		if (empty($postId)) {
			die;
		}

		$logEntryId = Abp01_InputFiltering::getFilteredGETValue('abp01_route_log_entry_id', 'intval');
		if ($logEntryId <= 0) {
			die;
		}

		$response = abp01_get_ajax_response();
		if ($this->_routeLogManager->deleteLogEntry($postId, $logEntryId)) {
			$response->success = true;
		} else {
			$response->message = __('The log entry could not be deleted.', 'abp01-trip-summary');
		}

		return $response;
	}

	public function deleteAllRouteLogEntries() {
		$postId = $this->_getCurrentPostId();
		if (empty($postId)) {
			die;
		}

		$response = abp01_get_ajax_response();
		if ($this->_routeLogManager->deleteLog($postId)) {
			$response->success = true;
		} else {
			$response->message = __('The log entries could not be deleted.', 'abp01-trip-summary');
		}

		return $response;
	}
}