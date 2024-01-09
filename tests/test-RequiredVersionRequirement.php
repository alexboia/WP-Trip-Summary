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

class RequiredVersionRequirementTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canCheck_satisfied() {
		$req = new Abp01_Installer_Requirement_RequiredVersion('8.0.0', '7.0.0');
		$this->assertTrue($req->isSatisfied());
	}

	public function test_canCheck_notSatisfied() {
		$req = new Abp01_Installer_Requirement_RequiredVersion('6.1.1', '6.1.2');
		$this->assertFalse($req->isSatisfied());
	}

	public function test_canCheck_currentEnv() {
		$req = new Abp01_Installer_Requirement_RequiredVersion(PHP_VERSION, $this->_getEnv()->getRequiredPhpVersion());
		$this->assertNull($req->getLastError());
	}

	public function test_getLastError_returnsNull_satisfied() {
		$req = new Abp01_Installer_Requirement_RequiredVersion('8.0.0', '7.0.0');
		$this->assertNull($req->getLastError());
	}

	public function test_getLastError_returnsNull_notSatisfied() {
		$req = new Abp01_Installer_Requirement_RequiredVersion('6.1.1', '6.1.2');
		$this->assertNull($req->getLastError());
	}

	public function test_getLastError_returnsNull_currentEnv() {
		$req = new Abp01_Installer_Requirement_RequiredVersion(PHP_VERSION, $this->_getEnv()->getRequiredPhpVersion());
		$this->assertNull($req->getLastError());
	}
}