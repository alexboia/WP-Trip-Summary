<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class NoopPathRewriterTests extends WP_UnitTestCase {
	use IncludesTestDataHelpers;

	public function test_canCheckIfNeedsRewriting() {
		$rewriter = new Abp01_Includes_NoopPathRewriter();
		$items = $this->_generateRandomIncludesItems();
		foreach ($items as $item) {
			$this->assertFalse($rewriter->needsRewriting($item));
		}
	}

	public function test_canRewritePath_itemWithPathKey() {
		$items = $this->_generateRandomIncludesItems();
		$rewriter = new Abp01_Includes_NoopPathRewriter();

		foreach ($items as $item) {
			$rewrittenPath = $rewriter->rewritePath($item);
			$this->assertEquals($item['path'], $rewrittenPath);
		}
	}

	public function test_canRewritePath_itemWithoutPathKey() {
		$rewriter = new Abp01_Includes_NoopPathRewriter();
		$this->assertNull($rewriter->rewritePath(array()));
		$this->assertNull($rewriter->rewritePath(array(
			'path' => null
		)));
	}
}