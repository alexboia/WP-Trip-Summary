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

class MaybeLeafletPluginScriptPathRewriterTests extends WP_UnitTestCase {
	use IncludesTestDataHelpers;

	protected function setUp(): void {
		parent::setUp();
		Abp01IsUrlRewriteEnabledState::resetReturnResult();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Abp01IsUrlRewriteEnabledState::resetReturnResult();
	}

	public function test_canCheckIfNeedsRewriting_leafletItems() {
		$items = $this->_generateRandomLeafletPluginIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertTrue($rewriter->needsRewriting($item));
		}
	}

	public function test_canCheckIfNeedsRewriting_nonLeafletItems() {
		$items = $this->_generateRandomNonLeafletPluginIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertFalse($rewriter->needsRewriting($item));
		}
	}

	public function test_canCheckIfNeedsRewriting_randomItems() {
		$items = $this->_generateRandomIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$expectedResult = $this->_isExpectedNeedRewritingForItem($item);

			$this->assertEquals($expectedResult, 
				$rewriter->needsRewriting($item));
		}
	}

	private function _isExpectedNeedRewritingForItem(array $item) {
		return $item['is-leaflet-plugin'] === true 
			&& $item['needs-wrap'] === true;
	}

	public function test_canRewritePath_leafletItems_whenServerUrlRewriteEnabled() {
		Abp01IsUrlRewriteEnabledState::setReturnResult(true);

		$items = $this->_generateRandomLeafletPluginIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertEquals($item['path'], $rewriter->rewritePath($item));
		}
	}

	public function test_canRewritePath_leafletItems_whenServerUrlRewriteNotEnabled() {
		Abp01IsUrlRewriteEnabledState::setReturnResult(false);

		$items = $this->_generateRandomLeafletPluginIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertEquals($this->_determineExpectedWrappedPath($item), 
				$rewriter->rewritePath($item));
		}
	}

	private function _determineExpectedWrappedPath(array $item) {
		return 'abp01-plugin-leaflet-plugins-wrapper.php?load=' . $item['path'];
	}

	public function test_canRewritePath_nonLeafletItems_whenServerUrlRewriteEnabled() {
		Abp01IsUrlRewriteEnabledState::setReturnResult(true);

		$items = $this->_generateRandomNonLeafletPluginIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertEquals($item['path'], 
				$rewriter->rewritePath($item));
		}
	}

	public function test_canRewritePath_nonLeafletItems_whenServerUrlRewriteNotEnabled() {
		Abp01IsUrlRewriteEnabledState::setReturnResult(false);

		$items = $this->_generateRandomNonLeafletPluginIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertEquals($item['path'], 
				$rewriter->rewritePath($item));
		}
	}

	public function test_canRewritePath_randomItems_whenServerUrlRewriteEnabled() {
		Abp01IsUrlRewriteEnabledState::setReturnResult(true);

		$items = $this->_generateRandomIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			$this->assertEquals($item['path'], 
				$rewriter->rewritePath($item));
		}
	}

	public function test_canRewritePath_randomItems_whenServerUrlRewriteNotEnabled() {
		Abp01IsUrlRewriteEnabledState::setReturnResult(false);

		$items = $this->_generateRandomIncludesItems();
		$rewriter = new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter();

		foreach ($items as $item) {
			if ($this->_isExpectedNeedRewritingForItem($item)) {
				$this->assertEquals($this->_determineExpectedWrappedPath($item), 
					$rewriter->rewritePath($item));
			} else {
				$this->assertEquals($item['path'], 
					$rewriter->rewritePath($item));
			}			
		}
	}
}