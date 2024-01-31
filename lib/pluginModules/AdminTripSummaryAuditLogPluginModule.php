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
class Abp01_PluginModules_AdminTripSummaryAuditLogPluginModule extends Abp01_PluginModules_PluginModule {
	const AUDIT_LOG_METABOX_REGISTRATION_HOOK_PRIORITY = 10;

	const AUDIT_LOG_METABOX_POSITION = 'side';

	const AUDIT_LOG_METABOX_PRIORITY = 'default';

	const AUDIT_LOG_POST_ROW_ACTIONS_HOOK_PRIORITY = 10;

	const AUDIT_LOG_NONCE_URL_PARAM_NAME = 'abp01_nonce';
	
	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_AuditLog_Provider
	 */
	private $_provider;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_getAuditLogContentByPostIdAjaxAction;
    
	public function __construct(Abp01_AuditLog_Provider $provider, Abp01_View $view, Abp01_Env $env, Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		$this->_provider = $provider;
		$this->_view = $view;
		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createEditCurrentPostTripSummaryAuthCallback();
		$currentResourceProvider = new Abp01_AdminAjaxAction_CurrentResourceProvider_None();

		$this->_getAuditLogContentByPostIdAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_GET_AUDIT_LOG_FOR_POST, array($this, 'getAuditLogContents'))
				->useDefaultNonceProvider(self::AUDIT_LOG_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpGet();
	}
	
	public function load() { 
		$this->_registerWebPageAssets();
		$this->_registerAjaxActions();
		$this->_registerEditorControls();
		$this->_registerPostRowActions();
		$this->_registerTripSummaryListingAuditLogInlineScripts();
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueCoreAuditLogStyles()) {
			Abp01_Includes::includeStyleAdminAuditLog();
		}

		if ($this->_shouldEnqueueListingAuditLogStyles()) {
			Abp01_Includes::includeStyleAdminListingAuditLog();
		}
	}

	private function _shouldEnqueueCoreAuditLogStyles() {
		return $this->_shouldEnqueueEditorWebPageAssets() 
			|| $this->_shouldEnqueueListingWebPageAssets();
	}

	private function _shouldEnqueueEditorWebPageAssets() {
		return $this->_env->isEditingWpPost(Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes()) 
			&& $this->_canEditCurrentPostTripSummary();
	}

	private function _shouldEnqueueListingWebPageAssets() {
		return $this->_env->isListingWpPosts(Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes()) 
			&& $this->_canEditCurrentPostTripSummary();
	}

	private function _shouldEnqueueListingAuditLogStyles() {
		return $this->_shouldEnqueueListingWebPageAssets();
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueListingAuditLogScripts()) {
			Abp01_Includes::includeScriptAdminListingAuditLog(Abp01_TranslatedScriptMessages::getAdminListingAuditLogScriptTranslations());
		}
	}

	private function _shouldEnqueueListingAuditLogScripts() {
		return $this->_shouldEnqueueListingWebPageAssets();
	}

	private function _registerAjaxActions() {
		$this->_getAuditLogContentByPostIdAjaxAction
			->register();
	}

	private function _registerEditorControls() {
		add_action('add_meta_boxes', array($this, 'registerAdminEditorAuditLogSummaryMetabox'), 
			self::AUDIT_LOG_METABOX_REGISTRATION_HOOK_PRIORITY, 
			2);
	}

	public function registerAdminEditorAuditLogSummaryMetabox($postType, $post) {
		if ($this->_shouldRegisterAdminEditorLauncherMetaboxes($postType, $post)) {
			add_meta_box('abp01-trip-summary-audit-log', 
				__('Trip summary audit log', 'abp01-trip-summary'),
				array($this, 'addAdminAuditLog'), 
				$postType, 
				self::AUDIT_LOG_METABOX_POSITION, 
				self::AUDIT_LOG_METABOX_PRIORITY, 
				array(
					'postType' => $postType,
					'post' => $post
				)
			);
		}
	}

	private function _shouldRegisterAdminEditorLauncherMetaboxes($postType, $post) {
		return Abp01_AvailabilityHelper::isEditorAvailableForPostType($postType) 
			&& $this->_cantEditPostTripSummary($post);
	}

	public function addAdminAuditLog($post, $args) {
		if ($this->_cantEditPostTripSummary($post)) {
			$this->_displayAdminAuditLogForPost($post, $args);
		}
	}

	private function _displayAdminAuditLogForPost($post, $args) {
		$postId = intval($post->ID);
		echo $this->_renderAdminAuditLogForPostId($postId);
	}

	private function _renderAdminAuditLogForPostId($postId) {
		$data = new stdClass();
		$data->postId = $postId;
		$data->auditLogData = $postId  > 0 
			? $this->_getAuditLogData($postId)
			: $this->_getEmptyAuditLogData();

		return $this->_view->renderAdminTripSummaryAuditLogContent($data);
	}

	private function _getAuditLogData($postId) {
		$auditLog = $this->_provider->getAuditLogForPostId($postId);
		return $auditLog->asPlainObject();
	}

	private function _getEmptyAuditLogData() {
		$emptyAuditLog = Abp01_AuditLog_Data::empty();
		return $emptyAuditLog->asPlainObject();
	}

	private function _registerPostRowActions() {
		add_action('post_row_actions', 
			array($this, 'addPostRowActions'), 
			self::AUDIT_LOG_POST_ROW_ACTIONS_HOOK_PRIORITY, 
			2);
	}

	public function addPostRowActions(array $actions, $post) {
		$postId = intval($post->ID);
		$postType = $post->post_type;
		
		if ($this->_shouldRegisterAdminEditorLauncherMetaboxes($postType, $post)) {
			$actions['abp01_show_trip_summary_audit_log'] = $this->_renderViewTripSummaryAuditLogLink($postId);
		}

		return $actions;
	}

	private function _renderViewTripSummaryAuditLogLink($postId) {
		return '<a class="abp01-admin-listing-audit-log-link" href="javascript:void(0);" data-post="' . $postId . '">' . __('Trip summary audit log', 'abp01-trip-summary') . '</a>';
	}

	private function _registerTripSummaryListingAuditLogInlineScripts() {
		add_action('in_admin_footer', array($this, 'renderTripSummaryListingAuditLogInlineScripts'));
	}

	public function renderTripSummaryListingAuditLogInlineScripts() {
		if ($this->_shouldEnqueueListingAuditLogScripts()) {
			$data = new stdClass();
			$data->ajaxBaseUrl = $this->_env->getAjaxBaseUrl();
			$data->ajaxAction = ABP01_ACTION_GET_AUDIT_LOG_FOR_POST;
			$data->nonce = $this->_getAuditLogContentByPostIdAjaxAction->generateNonce();
			echo $this->_view->renderAdminTripSummaryListingInlineScripts($data);
		}
	}

	public function getAuditLogContents() {
		$postId = $this->_getCurrentPostId();
		if (empty($postId)) {
			die;
		}

		echo $this->_renderAdminAuditLogForPostId($postId);
		die;
	}
}