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
class Abp01_PluginModules_LogsManagementPluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_View
	 */
	private $_view;

	public function __construct(Abp01_View $view,
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		$this->_view = $view;
	}

    public function load() { 
		$this->_registerWebPageAssets();
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeStyleAdminSystemLogs();
		}
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminSystemLogs(array());
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		return $this->_isViewingSystemLogsPage();
	}

	private function _isViewingSystemLogsPage() {
		return $this->_env->isAdminPage('abp01-system-logs');
	}
	
	public function getMenuItems() {
		return array(
			array(
				'slug' => 'abp01-system-logs',
				'parent' => ABP01_MAIN_MENU_SLUG,
				'pageTitle' => esc_html__('System logs', 'abp01-trip-summary'),
				'menuTitle' => esc_html__('System logs', 'abp01-trip-summary'),
				'capability' => Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY,
				'callback' => array($this, 'displayLogsManagementPage')
			)
		);
	}

	public function displayLogsManagementPage() {
		if (!$this->_currentUserCanManagePluginSettings()) {
			die;
		}

		$data = new stdClass();

		echo $this->_view->renderAdminSystemLogsPage($data);
	}
}