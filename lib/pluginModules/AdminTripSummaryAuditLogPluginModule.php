<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class Abp01_PluginModules_AdminTripSummaryAuditLogPluginModule extends Abp01_PluginModules_PluginModule {
	const AUDIT_LOG_METABOX_REGISTRATION_HOOK_PRIORITY = 30;

	const AUDIT_LOG_METABOX_POSITION = 'side';

	const AUDIT_LOG_METABOX_PRIORITY = 'default';
	
	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_AuditLog_Provider
	 */
	private $_provider;
    
	public function __construct(Abp01_AuditLog_Provider $provider, Abp01_View $view, Abp01_Env $env, Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		$this->_provider = $provider;
		$this->_view = $view;
	}
	
	public function load() { 
		$this->_registerEditorControls();
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
				array($this, 'addAdminAuditLogSummary'), 
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

	public function addAdminAuditLogSummary($post, $args) {
		if ($this->_cantEditPostTripSummary($post)) {
			$this->_addAdminAuditLogSummary($post, $args);
		}
	}

	private function _addAdminAuditLogSummary($post, $args) {
		$postId = intval($post->ID);
		$data = new stdClass();
		$data->auditLogData = $postId  > 0 
			? $this->_getAuditLogData($postId)
			: $this->_getEmptyAuditLogData();

		echo $this->_view->renderAdminTripSummaryAuditLogContent($data);
	}

	private function _getAuditLogData($postId) {
		$auditLog = $this->_provider->getAuditLogForPostId($postId);
		return $auditLog->asPlainObject();
	}

	private function _getEmptyAuditLogData() {
		$emptyAuditLog = new Abp01_AuditLog_Data(array(), array());
		return $emptyAuditLog->asPlainObject();
	}
}