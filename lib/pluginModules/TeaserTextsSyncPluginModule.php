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
class Abp01_PluginModules_TeaserTextsSyncPluginModule extends Abp01_PluginModules_PluginModule {
	const RESET_TEASER_TEXT_MARKER_TRANSIENT_KEY = 'abp01_reset_teaser_text_required';

	const RESET_TEASER_TEXT_MARKER_TRANSIENT_DURATION = MINUTE_IN_SECONDS;

	const RESET_TEASER_TEXT_MARKER_TRANSIENT_VALUE = 'true';

	const UPDATE_OPTION_HOOK_PRIORITY = 10;

	const ADMIN_FOOTER_LOADED_HOOK_PRIORITY = 1;

	/**
	 * @var Abp01_Settings
	 */
	private $_settings;

	public function __construct(Abp01_Settings $settings, Abp01_Env $env, Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		$this->_settings = $settings;
	}

	public function load() {
		add_action('update_option_WPLANG', 
			array($this, 'onLanguageUpdatedQueueTeaserSyncRequest'), 
			self::UPDATE_OPTION_HOOK_PRIORITY, 
			3);
		add_action('in_admin_footer', 
			array($this, 'onAdminFooterLoadedCheckForTeaserSyncRequest'), 
			self::ADMIN_FOOTER_LOADED_HOOK_PRIORITY);
	}

	public function onLanguageUpdatedQueueTeaserSyncRequest($oldValue, $value, $optName) {
		//When the WPLANG updated hook is triggered, 
		//	the new text domain is not yet loaded.
		//Thus, it's no point in resetting the teasers at this point, 
		//	since the values corresponding to the previous locale will be pulled.
		//The solution is to queue a flag that says it needs to be updated at a later point in time,
		//	which currently is when the footer is being generated, for lack of a better time and place.
		$shouldQueueTeaserSyncRequest = $optName == 'WPLANG' 
			&& $this->_isSavingWpOptions();

		if ($shouldQueueTeaserSyncRequest) {
			$this->_queueTeaserTextsSyncRequest();	
		}
	}

	private function _queueTeaserTextsSyncRequest() {
		set_transient(self::RESET_TEASER_TEXT_MARKER_TRANSIENT_KEY, 
			self::RESET_TEASER_TEXT_MARKER_TRANSIENT_VALUE, 
			self::RESET_TEASER_TEXT_MARKER_TRANSIENT_DURATION);
	}

	public function onAdminFooterLoadedCheckForTeaserSyncRequest() {
		$syncTeaserTextsRequested = $this->_dequeueTeaserTextsSyncRequest();
		if ($syncTeaserTextsRequested) {
			$this->_syncTeaserTexts();
		}
	}

	private function _dequeueTeaserTextsSyncRequest() {
		$maybeTeaserTextsDequeueRequest = get_transient(self::RESET_TEASER_TEXT_MARKER_TRANSIENT_KEY);
		delete_transient(self::RESET_TEASER_TEXT_MARKER_TRANSIENT_KEY);
		return $maybeTeaserTextsDequeueRequest === self::RESET_TEASER_TEXT_MARKER_TRANSIENT_VALUE;
	}

	private function _syncTeaserTexts() {
		$this->_settings->syncTopTeaserTextWithCurrentLocale();
		$this->_settings->syncBottomTeaserTextWithCurrentLocale();
		$this->_settings->saveSettings();
	}

	private function _isSavingWpOptions() {
		return $this->_env->isSavingWpOptions();
	}
}