<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
    }

    private function _renderFrontendViewerJsVars(stdClass $data) {
        return $this->_renderCoreView('wpts-frontend-jsvars.php', $data);
    }

    private function _renderCoreView($file, stdClass $data) {
        ob_start();
	    require $this->_env->getViewFilePath($file);
	    return ob_get_clean();
    }

    public function initView() {
        $frontendThemeClass = apply_filters('abp01_get_frotend_theme_class', 'Abp01_FrontendTheme_Decorator');
        if (!empty($frontendThemeClass) 
            && in_array('Abp01_FrontendTheme', class_implements($frontendThemeClass, true))) {
            $this->_frontendTheme = new $frontendThemeClass($this->_env);
        } else {
            $this->_frontendTheme = new Abp01_FrontendTheme_Decorator($this->_env);
        }
    }

    public function includeFrontendViewerScripts($translations) {
        Abp01_Includes::includeScriptFrontendMain(true, $translations);
    }

    public function includeFrontendViewerStyles() {
        $this->_frontendTheme->includeFrontendViewerStyles();
    }

    public function renderAdminSettingsPage(stdClass $data) {
	    return $this->_renderCoreView('wpts-settings.php', $data);
    }

    public function renderAdminHelpPage(stdClass $data) {
        return $this->_renderCoreView('wpts-help.php', $data);
    }

    public function renderAdminLookupPage(stdClass $data) {
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
}