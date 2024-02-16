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

class Abp01_View {
	/**
	 * @var Abp01_FrontendTheme
	 */
	private $_frontendTheme;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct() {
		$this->_env = Abp01_Env::getInstance();
	}

	private function _registerAdminHelpers() {
		require_once $this->_env->getViewHelpersFilePath('controls.php');
	}

	private function _registerFrontendHelpers() {
		$this->_frontendTheme->registerFrontendViewerHelpers();
		require_once $this->_env->getViewHelpersFilePath('controls.frontend.php');
	}

	private function _renderFrontendViewerJsVars(stdClass $data) {
		return $this->_renderCoreView('wpts-frontend-jsvars.php', $data);
	}

	private function _renderCoreView($file, stdClass $data) {
		ob_start();
		require $this->_env->getViewFilePath($file);
		return ob_get_clean();
	}

	public function isUsingTheme($wptsThemeClass) {
		return !empty($this->_frontendTheme) 
			&& get_class($this->_frontendTheme) == $wptsThemeClass;
	}

	public function initView() {
		$frontendThemeClass = $this->_determineThemeClass();
		$this->_frontendTheme = new $frontendThemeClass($this->_env);
	}

	private function _determineThemeClass() {
		$frontendThemeClass = apply_filters('abp01_get_frotend_theme_class', 'Abp01_FrontendTheme_Decorator');
		if (!$this->_isThemeClassValid($frontendThemeClass)) {
			$frontendThemeClass = 'Abp01_FrontendTheme_Decorator';
		}
		return $frontendThemeClass;
	}

	private function _isThemeClassValid($frontendThemeClass) {
		return !empty($frontendThemeClass) 
			&& class_exists($frontendThemeClass)
			&& in_array('Abp01_FrontendTheme', class_implements($frontendThemeClass, true));
	}

	public function includeFrontendViewerScripts($translations) {
		Abp01_Includes::includeScriptFrontendMain(true, $translations);
	}

	public function includeFrontendViewerStyles() {
		$this->_frontendTheme->includeFrontendViewerStyles();
	}

	public function renderAdminSettingsPage(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-settings.php', $data);
	}

	public function renderAdminHelpPage(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-help.php', $data);
	}

	public function renderAdminAboutPage(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-about.php', $data);
	}

	public function renderAdminMaintenancePage(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-maintenance.php', $data);
	}

	public function renderAdminSystemLogsPage(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-admin-system-logs.php', $data);
	}

	public function renderAdminMaintenanceToolResult($toolId, stdClass $data) {
		$viewFileName = sprintf('maintenance/wpts-%s-result.php', $toolId);
		return $this->_viewFileExists($viewFileName)
			? $this->_renderCoreView($viewFileName, $data)
			: '';
	}
	
	private function _viewFileExists($viewFileName) {
		return is_readable($this->_env->getViewFilePath($viewFileName));
	}

	public function renderAdminLookupPage(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-lookup-data-management.php', $data);
	}

	public function renderAdminTripSummaryEditor(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-editor.php', $data);
	}

	public function renderAdminTripSummaryEditorLauncherMetabox(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-editor-launcher-metabox.php', $data);
	}

	public function renderAdminTripSummaryAuditLogContent(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-audit-log.php', $data);
	}

	public function renderAdminTripSummaryListingInlineScripts(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-listing-inline-scripts.php', $data);
	}

	public function renderFrontendTeaser(stdClass $data) {
		$this->_registerFrontendHelpers();
		return $this->_frontendTheme->renderTeaser($data);
	}

	public function renderFrontendViewer(stdClass $data) {
		$this->_registerFrontendHelpers();
		return $this->_renderFrontendViewerJsVars($data) 
			. PHP_EOL 
			. $this->_frontendTheme->renderViewer($data);
	}

	public function renderJsonLdFrontendData(stdClass $data) {
		$this->_registerFrontendHelpers();
		return $this->_renderCoreView('wpts-jsonld-frontend-data.php', $data);
	}

	public function renderCoreView($viewName, stdClass $data) {
		$this->_registerFrontendHelpers();
		return $this->_renderCoreView($viewName, $data);
	}

	public function renderRouteLogFrontendViewerTabContent(stdClass $data) {
		$this->_registerFrontendHelpers();
		return $this->_renderCoreView('wpts-frontend-route-log.php', $data);
	}

	public function renderAdminTripSummaryLogEditor(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-log-editor.php', $data);
	}

	public function renderAdminTripSummaryLogEditorMetaboxStatusItem(stdClass $data) {
		$this->_registerAdminHelpers();
		return $this->_renderCoreView('wpts-log-editor-launcher-status-item.php', $data);
	}
}