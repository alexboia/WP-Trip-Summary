<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class TestFrontendTheme implements Abp01_FrontendTheme {
    const GENERATED_TEASER_CODE = '_TEST_FRONTEND_THEME_TEASER_CODE_';

    const GENERATED_VIEWER_CODE = '_TEST_FRONTEND_THEME_VIEWER_CODE_';

    private static $_frontendViewerStylesIncluded = false;

    private static $_frontendViewerHelpersIncluded = false;

    private static $_teaserRendered = false;

    private static $_teaserRenderedWithData = null;

    private static $_viewerRendered = false;

    private static $_viewerRenderedWithData = null;

    public static function areFrontendViewerStylesIncluded() {
        return self::$_frontendViewerStylesIncluded;
    }

    public static function areFrontendViewerHelpersIncluded() {
        return self::$_frontendViewerHelpersIncluded;
    }

    public static function isTeaserRendered() {
        return self::$_teaserRendered;
    }

    public static function isTeaserRenderedWithData(stdClass $data) {
        return self::$_teaserRendered && self::$_teaserRenderedWithData == $data;
    }

    public static function isViewerRendered() {
        return self::$_viewerRendered;
    }

    public static function isViewerRenderedWithData(stdClass $data) {
        return self::$_viewerRendered && self::$_viewerRenderedWithData == $data;
    }

    public static function reset() {
        self::$_frontendViewerStylesIncluded = false;
        self::$_frontendViewerHelpersIncluded = false;
        self::$_teaserRendered = false;
        self::$_teaserRenderedWithData = null;
        self::$_viewerRendered = false;
        self::$_viewerRenderedWithData = null;
    }

    public function includeFrontendViewerStyles() {
        self::$_frontendViewerStylesIncluded = true;
    }

    public function registerFrontendViewerHelpers() {
        self::$_frontendViewerHelpersIncluded = true;
    }

    public function renderTeaser(stdClass $data) {
        self::$_teaserRendered = true;
        self::$_teaserRenderedWithData = $data;
        return self::GENERATED_TEASER_CODE;
    }

    public function renderViewer(stdClass $data) {
        self::$_viewerRendered = true;
        self::$_viewerRenderedWithData = $data;
        return self::GENERATED_VIEWER_CODE;
    }

    public function getVersion() {
        return '1.2.3';
    }
}