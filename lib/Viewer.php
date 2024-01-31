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

class Abp01_Viewer {
	const TAB_INFO = 'abp01-tab-info';

	const TAB_MAP = 'abp01-tab-map';

	const ITEM_LAYOUT_HORIZONTAL = 'abp01-item-layout-horizontal';

	const ITEM_LAYOUT_VERTICAL = 'abp01-item-layout-vertical';

	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var array
	 */
	private $_contentCache = array();

	public function __construct(Abp01_View $view) {
		$this->_view = $view;
	}

	public static function getAvailableTabs() {
		$availableTabs = array(
			self::TAB_INFO => __('Prosaic details', 'abp01-trip-summary'), 
			self::TAB_MAP => __('Map', 'abp01-trip-summary')
		);

		$additionalTabs = apply_filters('abp01_additional_frontend_viewer_tabs', 
			array(), 
			null);

		if (!empty($additionalTabs)) {
			foreach ($additionalTabs as $tabCode => $tabInfo) {
				$label = !empty($tabInfo['label']) 
					? $tabInfo['label'] 
					: null;

				if (empty($label) || empty($tabCode)) {
					continue;
				}

				if (!isset($availableTabs[$tabCode])) {
					$availableTabs[$tabCode] = $label;
				}
			}
		}

		return $availableTabs;
	}

	public static function isTabSupported($tab) {
		return in_array($tab, array_keys(self::getAvailableTabs()));
	}

	public static function getAvailableItemLayouts() {
		return array(
			self::ITEM_LAYOUT_HORIZONTAL => __('Horizontally', 'abp01-trip-summary'), 
			self::ITEM_LAYOUT_VERTICAL => __('Vertically', 'abp01-trip-summary')
		);
	}

	public static function isItemLayoutSupported($itemLayout) {
		return in_array($itemLayout, array_keys(self::getAvailableItemLayouts()));
	}

	public function render(stdClass $data) {
		$viewerContent = array(
			'teaserHtml' => null,
			'viewerHtml' => null
		);

		if ($this->_canBeRendered($data)) {
			$viewerContent = $this->_readCachedViewerContent($data->postId);
			if ($viewerContent === null) {
				$viewerContent = array(
					'teaserHtml' => $this->_view->renderFrontendTeaser($data),
					'viewerHtml' => $this->_view->renderFrontendViewer($data)
				);

				$this->_cacheViewerContent($data->postId, 
					$viewerContent);
			}
		}

		return $viewerContent;
	}

	private function _canBeRendered(stdClass $data) {
		return !empty($data->postId) 
			&& ($data->info->exists || $data->track->exists);
	}

	private function _readCachedViewerContent($postId) {
		return isset($this->_contentCache[$postId]) 
			? $this->_contentCache[$postId] 
			: null;
	}

	private function _cacheViewerContent($postId, array $viewerContent) {
		$this->_contentCache[$postId] = $viewerContent;
	}

	public function renderAndAttachToContent(stdClass $data, $postContent) {
		$viewerContentParts = $this->render($data);
		$postContent = $viewerContentParts['teaserHtml'] . $postContent;

		if (!$this->_contentHasAnyTypeOfShortCode($postContent)) {
			$postContent = $postContent . $viewerContentParts['viewerHtml'];
		} elseif ($this->_contentHasViewerShortcode($postContent)) {
			//Replace all but on of the shortcode references
			$postContent = $this->_ensureContentHasUniqueShortcode($postContent);
		}
	
		return $postContent;
	}

	private function _contentHasAnyTypeOfShortCode(&$postContent) {
		return $this->_contentHasViewerShortcode($postContent) 
			|| $this->_contentHasViewerShortCodeBlock($postContent);
	}

	private function _contentHasViewerShortcode(&$postContent) {
		return preg_match($this->_getViewerShortcodeRegexp(), $postContent);
	}

	private function _getViewerShortcodeRegexp() {
		return '/(\[\s*' . ABP01_VIEWER_SHORTCODE . '\s*\])/';
	}
	
	private function _contentHasViewerShortCodeBlock(&$postContent) {
		return function_exists('has_block') 
			&& has_block('abp01/block-editor-shortcode', $postContent);
	}

	private function _ensureContentHasUniqueShortcode(&$postContent) {
		$replaced = false;
		return preg_replace_callback($this->_getViewerShortcodeRegexp(), 
			function($matches) use (&$replaced) {
				if ($replaced === false) {
					$replaced = true;
					return $this->_getViewerShortcode();
				} else {
					return '';
				}
			}, $postContent);
	}

	private function _getViewerShortcode() {
		return '[' . ABP01_VIEWER_SHORTCODE . ']';
	}

	public function includeFrontendViewerStyles() {
		$this->_view->includeFrontendViewerStyles();
	}

	public function includeFrontendViewerScripts(array $translations) {
		$this->_view->includeFrontendViewerScripts($translations);
	}
}