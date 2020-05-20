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

class Abp01_FrontendTheme_Decorator extends Abp01_FrontendTheme_Default {
    public function __construct(Abp01_Env $env) {
        parent::__construct($env);
    }

    public function includeFrontendViewerStyles() {
        if (!Abp01_Includes::includeStyleFrontendMainFromCurrentThemeIfPresent()) {
            parent::includeFrontendViewerStyles();
        }
    }

    public function registerFrontendViewerHelpers() {
        $themeHelpers = $this->_getFrontendTemplateLocation('helpers/controls.frontend.php');
        
        if (is_readable($themeHelpers)) {
            require_once $themeHelpers;
        }
        
        //include the default helpers - 
        //  the actual functions will only be defined 
        //  if no overrides are found
        parent::registerFrontendViewerHelpers();
    }

    public function renderTeaser(stdClass $data) {
        $themeTeaser = $this->_getFrontendTemplateLocation('wpts-frontend-teaser.php');

        if (is_readable($themeTeaser)) {
            ob_start();
            require $themeTeaser;
            return ob_get_clean();
        } else {
            return parent::renderTeaser($data);
        }
    }

    public function renderViewer(stdClass $data) {
        $themeViewer = $this->_getFrontendTemplateLocation('wpts-frontend.php');

        if (is_readable($themeViewer)) {
            ob_start();
            require $themeViewer;
            return ob_get_clean();
        } else {
            return parent::renderViewer($data);
        }
    }

    public function getVersion() {
        return parent::getVersion();
    }

    private function _getFrontendTemplateLocation($file) {
        $locations = $this->_getFrontendTemplateLocations();
        return wp_normalize_path($locations->theme . '/' . $file);
    }

    private function _getFrontendTemplateLocations() {
        return $this->_env->getFrontendTemplateLocations();
    }
}