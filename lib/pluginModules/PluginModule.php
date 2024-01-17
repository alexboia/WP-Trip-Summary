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
abstract class Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_Auth
	 */
	protected $_auth;

	/**
	 * @var Abp01_Env
	 */
	protected $_env;

	public function __construct(Abp01_Env $env, Abp01_Auth $auth) {
		$this->_env = $env;
		$this->_auth = $auth;
	}

	protected function _cantEditPostTripSummary($post) {
		if (empty($post)) {
			throw new InvalidArgumentException('Post information may not be empty!');
		}

		if ($post && is_object($post)) {
			$postId = intval($post->ID);
		} else if ($post && is_numeric($post)) {
			$postId = $post;
		} else {
			$postId = 0;
		}

		return !empty($postId) && $this->_auth->canEditPostTripSummary($postId);
	}

	protected function _createEditCurrentPostTripSummaryAuthCallback() {
		return function() {
			return $this->_canEditCurrentPostTripSummary();
		};
	}

	protected function _canEditCurrentPostTripSummary() {
		$postId = $this->_getCurrentPostId();
		return !empty($postId) && $this->_auth->canEditPostTripSummary($postId);
	}

	protected function _createManagePluginSettingsAuthCallback() {
		return function() {
			return $this->_currentUserCanManagePluginSettings();
		};
	}

	protected function _getCurrentPostId() {
		return $this->_env->getCurrentPostId('abp01_postId');
	}

	protected function _currentUserCanManagePluginSettings() {
		return $this->_auth->canManagePluginSettings();
	}

	protected function _getAjaxBaseUrl() {
		return $this->_env->getAjaxBaseUrl();
	}

	protected function _getPluginMediaImgBaseUrl() {
		return $this->_env->getPluginAssetUrl('media/img');
	}

	protected function _getLookupForCurrentLang() {
		return abp01_get_plugin()->getLookupForCurrentLang();
	}

	protected function _formatDbDate($dbDate) {
		return mysql2date(get_option('date_format'), $dbDate, true);
	}

	public function getMenuItems() {
		return array();
	}

	abstract public function load();
}