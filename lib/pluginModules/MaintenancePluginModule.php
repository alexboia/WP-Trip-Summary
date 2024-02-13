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
class Abp01_PluginModules_MaintenancePluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_MaintenanceTool_Registry
	 */
	private $_registry;

	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_executeToolAction;

	public function __construct(Abp01_MaintenanceTool_Registry $registry,
		Abp01_View $view,
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);
		$this->_registry = $registry;
		$this->_view = $view;
		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createManagePluginSettingsAuthCallback();

		$this->_executeToolAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_EXEC_MAINTENANCE_TOOL, array($this, 'executeTool'))
				->useDefaultNonceProvider('abp01_nonce_execute_tool')
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();
	}

	public function load() {
		$this->_registerAjaxActions();
		$this->_registerWebPageAssets();
	}

	private function _registerAjaxActions() {
		$this->_executeToolAction
			->register();
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeStyleAdminMaintenance();
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		return $this->_isViewingMaintenancePage();
	}

	private function _isViewingMaintenancePage() {
		return $this->_env->isAdminPage(ABP01_MAINTENANCE_SUBMENU_SLUG);
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminMaintenance($this->_getAdminMaintenanceScriptTranslations());
		}
	}

	private function _getAdminMaintenanceScriptTranslations() {
		return Abp01_TranslatedScriptMessages::getAdminMaintenanceScriptTranslations();
	}

	public function getMenuItems() {
		return array(
			array(
				'slug' => ABP01_MAINTENANCE_SUBMENU_SLUG,
				'parent' => ABP01_MAIN_MENU_SLUG,
				'pageTitle' => esc_html__('Maintenance', 'abp01-trip-summary'),
				'menuTitle' => esc_html__('Maintenance', 'abp01-trip-summary'),
				'capability' => Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY,
				'callback' => array($this, 'displayMaintenancePage')
			)
		);
	}

	public function displayMaintenancePage() {
		if (!$this->_currentUserCanManagePluginSettings()) {
			die;
		}

		$data = new stdClass();
		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->ajaxExecuteToolAction = ABP01_ACTION_EXEC_MAINTENANCE_TOOL;
		$data->nonce = $this->_executeToolAction
			->generateNonce();

		$data->toolsInfo = $this->_getMaintenanceToolsInfo();
		$data->preselectTool = null;

		echo $this->_view->renderAdminMaintenancePage($data);
	}

	private function _getMaintenanceToolsInfo() {
		return $this->_registry->getRegisteredToolsInfo();
	}

	public function executeTool() {
		$toolId = $this->_getToolIdFromHttpGet();
		if (!$this->_registry->isToolRegistered($toolId)) {
			die;
		}

		$response = abp01_get_ajax_response();
		$result = $this->_registry->executeTool($toolId);

		$response->success = $result->wasSuccessful();
		$response->content = $this->_renderToolResult($toolId, 
			$result);

		return $response;
	}

	private function _getToolIdFromHttpGet() {
		return Abp01_InputFiltering::getFilteredGETValue('abp01_tool_id');
	}

	private function _renderToolResult($toolId, Abp01_MaintenanceTool_Result $result) {
		$data = new stdClass();
		$data->result = $result->getData();
		$renderedResult = $this->_view->renderAdminMaintenanceToolResult($toolId, 
			$data);

		$filteredRenderedResult = apply_filters('abp01_render_maintenance_tool_result', 
			$renderedResult, 
			$toolId, 
			$result);

		return $filteredRenderedResult;
	}
}