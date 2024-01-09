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

class Abp01_TripSummaryShortcodeBlockType {
	public function isAvailable() {
		return $this->_canRegisterWpBlockTypes();
	}

	private function _canRegisterWpBlockTypes() {
		return function_exists('register_block_type');
	}

	public function registerWithEditorScripts() {
		$this->registerEditorScripts();
		$this->register();
	}

	public function registerEditorScripts() {
		if ($this->isAvailable()) {
			Abp01_Includes::includeScriptBlockEditorViewerShortCodeBlock();
		}
	}

	public function register() {
		if ($this->isAvailable()) {
			register_block_type('abp01/block-editor-shortcode', array(
				'editor_script' => 'abp01-viewer-short-code-block',
				//we need server side rendering to account for 
				//	potential changes of the configured shortcode tag name
				'render_callback' => array($this, 'render')
			));
		}
	}

	public function render($attributes, $content) {
		static $rendered = false;
		if ($rendered === false || !doing_filter('the_content')) {
			$rendered = true;
			return '<div class="abp01-viewer-shortcode-block">' . $this->_renderTripSummaryShortCode() . '</div>';
		} else {
			return '';
		}
	}

	private function _renderTripSummaryShortCode() {
		return '[' . ABP01_VIEWER_SHORTCODE . ']';
	}
} 