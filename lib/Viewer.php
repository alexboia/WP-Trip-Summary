<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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
	public const string TAB_INFO = 'abp01-tab-info';

	public const string TAB_MAP = 'abp01-tab-map';

	public const string ITEM_LAYOUT_HORIZONTAL = 'abp01-item-layout-horizontal';

	public const string ITEM_LAYOUT_VERTICAL = 'abp01-item-layout-vertical';

	private Abp01_View $_view;

	private ?array $_contentCache = array();

	public function __construct(Abp01_View $view) {
		$this->_view = $view;
	}

	public static function getAvailableTabsInfo(): array {
		$availableTabs = self::_getDefaultTabs();
		$additionalTabs = self::getAdditionalTabs(null);

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

	/**
	 * @return array<string, array>
	 */
	public static function getAdditionalTabs(?int $postId): array {
		if ($postId <= 0) {
			$postId = null;
		}

		/**
		 * Filters the additional front-end viewer tabs.
		 * It cannot be used to modify the existing ones.
		 * 
		 * Must be an array with tab codes as keys and, for each key, 
		 * an array with a mandatory "label" key as values. 
		 * Other keys are valid, but left alone.
		 * 
		 * Post ID can be an positive integer or a null value.
		 * If a null value is passed, all available tabs should be provided, 
		 * since this is called from the settings page where the default selected tab is configured.
		 * 
		 * @since 0.3.2
		 * @category Front-end Viewer
		 * 
		 * @param array<string, array> $additionaTabs The list of additional tabs
		 * @param ?int $postId The post ID or null
		 */
		$additionalTabs = apply_filters('abp01_additional_frontend_viewer_tabs', 
			array(), 
			$postId);

		if (!is_array($additionalTabs)) {
			$additionalTabs = array();
		}

		return $additionalTabs;
	}

	/**
	 * @return array <string, string>
	 */
	private static function _getDefaultTabs(): array {
		return array(
			self::TAB_INFO => __('Prosaic details', 'abp01-trip-summary'), 
			self::TAB_MAP => __('Map', 'abp01-trip-summary')
		);
	}

	public static function isTabSupported(?string $tab): bool {
		return in_array($tab, array_keys(self::getAvailableTabsInfo()));
	}

	/**
	 * @return array <string, string>
	 */
	public static function getAvailableItemLayouts(): array {
		return array(
			self::ITEM_LAYOUT_HORIZONTAL => __('Horizontally', 'abp01-trip-summary'), 
			self::ITEM_LAYOUT_VERTICAL => __('Vertically', 'abp01-trip-summary')
		);
	}

	public static function isItemLayoutSupported(?string $itemLayout): bool {
		$validKeys = array_keys(self::getAvailableItemLayouts());	
		return !empty($itemLayout) && in_array($itemLayout, $validKeys);
	}

	public function render(stdClass $data): ?array {
		$postId = isset($data->postId)
			? intval($data->postId)
			: 0;

		if ($postId <= 0) {
			return null;
		}

		$viewerContent = array(
			'teaserHtml' => null,
			'viewerHtml' => null
		);

		if ($this->_canBeRendered($data)) {
			$viewerContent = $this->_readCachedViewerContent($postId);
			if ($viewerContent === null) {
				$viewerContent = array(
					'teaserHtml' => $this->_view->renderFrontendTeaser($data),
					'viewerHtml' => $this->_view->renderFrontendViewer($data)
				);

				$this->_cacheViewerContent($postId, 
					$viewerContent);
			}
		}

		return $viewerContent;
	}

	private function _canBeRendered(stdClass $data): bool {
		return !empty($data->postId) 
			&& ($data->info->exists || $data->track->exists);
	}

	private function _readCachedViewerContent(int $postId): ?array {
		return isset($this->_contentCache[$postId]) 
			? $this->_contentCache[$postId] 
			: null;
	}

	private function _cacheViewerContent(int $postId, array $viewerContent) {
		$this->_contentCache[$postId] = $viewerContent;
	}

	public function renderAndAttachToContent(stdClass $data, ?string $postContent) {
		$viewerContentParts = $this->render($data);

		$postContent = $postContent ?? "";
		$postContent = $viewerContentParts['teaserHtml'] . $postContent;

		if (!$this->_contentHasAnyTypeOfShortCode($postContent)) {
			$postContent = $postContent . $viewerContentParts['viewerHtml'];
		} elseif ($this->_contentHasViewerShortcode($postContent)) {
			//Replace all but on of the shortcode references
			$postContent = $this->_ensureContentHasUniqueShortcode($postContent);
		}
	
		return $postContent;
	}

	private function _contentHasAnyTypeOfShortCode(?string &$postContent): bool {
		return $this->_contentHasViewerShortcode($postContent) 
			|| $this->_contentHasViewerShortCodeBlock($postContent);
	}

	private function _contentHasViewerShortcode(?string &$postContent) {
		return preg_match($this->_getViewerShortcodeRegexp(), $postContent);
	}

	private function _getViewerShortcodeRegexp(): string {
		return '/(\[\s*' . ABP01_VIEWER_SHORTCODE . '\s*\])/';
	}
	
	private function _contentHasViewerShortCodeBlock(?string &$postContent) {
		return function_exists('has_block') 
			&& has_block('abp01/block-editor-shortcode', $postContent);
	}

	private function _ensureContentHasUniqueShortcode(?string &$postContent) {
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

	private function _getViewerShortcode(): string {
		return '[' . ABP01_VIEWER_SHORTCODE . ']';
	}

	public function includeFrontendViewerStyles(): void {
		$this->_view->includeFrontendViewerStyles();
	}

	public function includeFrontendViewerScripts(array $translations) {
		$this->_view->includeFrontendViewerScripts($translations);
	}
}