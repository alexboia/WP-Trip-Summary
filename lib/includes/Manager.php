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
	exit ;
}

class Abp01_Includes_Manager {
	private $_refPluginsPath;

	private $_scriptsInFooter = false;

	private $_styles = array();

	private $_scripts = array();

	/**
	 * @var Abp01_Includes_PathRewriter
	 */
	private $_stylesPathRewriter;

	/**
	 * @var Abp01_Includes_PathRewriter
	 */
	private $_scriptsPathRewriter;

	/**
	 * @var Abp01_Includes_DependencySelector
	 */
	private $_stylesDependencySelector;

	/**
	 * @var Abp01_Includes_DependencySelector
	 */
	private $_scriptsDependencySelector;

	public function __construct(array $scripts,
		array $styles, 
		$refPluginsPath, 
		$scriptsInFooter) {

		if (empty($refPluginsPath)) {
			throw new \InvalidArgumentException('The $refPluginsPath parameter is required and may not be empty.');
		}

		$this->_refPluginsPath = $refPluginsPath;
		$this->_scriptsInFooter = $scriptsInFooter;

		$this->_stylesPathRewriter = new Abp01_Includes_NoopPathRewriter();
		$this->_scriptsPathRewriter = new Abp01_Includes_NoopPathRewriter();

		$this->_stylesDependencySelector = new Abp01_Includes_UnconditionalDependencySelector();
		$this->_scriptsDependencySelector = new Abp01_Includes_UnconditionalDependencySelector();

		$this->_scripts = $scripts;
		$this->_styles = $styles;
	}

	public function setStylesPathRewriter(Abp01_Includes_PathRewriter $rewriter) {
		$this->_stylesPathRewriter = $rewriter;
		return $this;
	}

	public function setScriptsPathRewriter(Abp01_Includes_PathRewriter $rewriter) {
		$this->_scriptsPathRewriter = $rewriter;
		return $this;
	}

	public function setStylesDependencySelector(Abp01_Includes_DependencySelector $selector) {
		$this->_stylesDependencySelector = $selector;
		return $this;
	}

	public function setScriptsDependencySelector(Abp01_Includes_DependencySelector $selector) {
		$this->_scriptsDependencySelector = $selector;
		return $this;
	}

	public function enqueueScript($handle) {
		if (empty($handle)) {
			return;
		}

		if (isset($this->_scripts[$handle])) {
			if (!wp_script_is($handle, 'registered')) {
				$script = $this->getActualScriptToInclude($handle);

				$deps = isset($script['deps']) && is_array($script['deps']) 
					? $script['deps'] 
					: array();

				$deps = $this->_scriptsDependencySelector
					->selectDependencies($deps);

				if (!empty($deps)) {
					$this->_ensureScriptDependencies($deps);
				}

				$srcUrl = $this->_determineScriptSrcUrl($script);
				wp_enqueue_script($handle, 
					$srcUrl, 
					$deps, 
					$script['version'], 
					$this->_scriptsInFooter);

				if (isset($script['inline-setup'])) {
					wp_add_inline_script($handle, $script['inline-setup']);
				}
			} else {
				wp_enqueue_script($handle);
			}
		} else {
			wp_enqueue_script($handle);
		}
	}

	private function _determineScriptPath($script) {
		if ($this->_scriptsPathRewriter->needsRewriting($script)) {
			$scriptPath = $this->_scriptsPathRewriter->rewritePath($script);
		} else {
			$scriptPath = $script['path'];
		}

		return $scriptPath;
	}

	private function _determineScriptSrcUrl($script) {
		$scriptPath = $this->_determineScriptPath($script);
		return !$this->_isExplicityAbsolute($scriptPath) 
			? plugins_url($scriptPath, $this->_refPluginsPath)
			: $scriptPath['path'];
	}

	private function _isExplicityAbsolute($path) {
		return is_array($path) 
			&& isset($path['absolute']) 
			&& $path['absolute'] === true;
	}

	public function getActualScriptToInclude($handle) {
		return $this->_getActualElement($handle, $this->_scripts);
	}

	public function getScriptSrcUrl($handle) {
		$script = $this->getActualScriptToInclude($handle);
		$srcUrl = $this->_determineScriptSrcUrl($script);

		return add_query_arg(array('ver' => $script['version']), 
			$srcUrl);
	}

	private function _ensureScriptDependencies(array $deps) {
		foreach ($deps as $depHandle) {
			if ($this->_hasScript($depHandle)) {
				$this->enqueueScript($depHandle);
			}
		}
	}

	private function _hasScript($handle) {
		return !empty($this->_scripts[$handle]);
	}

	private function _getActualElement($handle, array &$collection) {
		$script = null;
		$actual = null;

		if (isset($collection[$handle])) {
			$script = $collection[$handle];
			if (!empty($script['alias'])) {
				$handle = $script['alias'];
				$actual = isset($collection[$handle]) 
					? $collection[$handle]
					: null;
			}

			if (!empty($actual)) {
				$deps = isset($script['deps']) 
					? $script['deps'] 
					: null;
				if (!empty($deps)) {
					$actual['deps'] = $deps;
				}
			} else {
				$actual = $script;
			}
		}

		return $actual;
	}

	public function enqueueStyle($handle) {
		if (empty($handle)) {
			return;
		}

		if (isset($this->_styles[$handle])) {
			$style = $this->getActualStyleToInclude($handle);

			if (!isset($style['media']) || !$style['media']) {
				$style['media'] = 'all';
			}

			$deps = isset($style['deps']) && is_array($style['deps']) 
				? $style['deps'] 
				: array();

			$deps = $this->_stylesDependencySelector
				->selectDependencies($deps);

			if (!empty($deps)) {
				$this->_ensureStyleDependencies($deps);
			}

			$srcUrl = $this->_determineStyleSrcUrl($style);
			wp_enqueue_style($handle, 
				$srcUrl, 
				$deps, 
				$style['version'], 
				$style['media']);
		} else {
			wp_enqueue_style($handle);
		}
	}

	private function _determineStylePath($style) {
		if ($this->_stylesPathRewriter->needsRewriting($style)) {
			$stylePath = $this->_stylesPathRewriter->rewritePath($style);
		} else {
			$stylePath = $style['path'];
		}

		return $stylePath;
	}
	
	private function _determineStyleSrcUrl($style) {
		$stylePath = $this->_determineStylePath($style);
		return !$this->_isExplicityAbsolute($stylePath) 
			? plugins_url($stylePath, $this->_refPluginsPath)
			: $stylePath['path'];
	}

	public function getStyleSrcUrl($handle) {
		$style = $this->getActualStyleToInclude($handle);
		$srcUrl = $this->_determineStyleSrcUrl($style);

		return add_query_arg(array('ver' => $style['version']), 
			$srcUrl);
	}

	public function getActualStyleToInclude($handle) {
		return $this->_getActualElement($handle, $this->_styles);
	}

	private function _ensureStyleDependencies(array $deps) {
		foreach ($deps as $depHandle) {
			if ($this->_hasStyle($depHandle)) {
				$this->enqueueStyle($depHandle);
			}
		}
	}

	private function _hasStyle($handle) {
		return !empty($this->_styles[$handle]);
	}
}