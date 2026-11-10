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
		$this->assertTrue($req->isSatisfied());
		$this->assertNull($req->getLastError());
	}

	/**
	 * @dataProvider versionComparisonProvider
	 */
	public function test_canCheck_normalizedVersions($currentVersion, $requiredVersion, $expected) {
		$req = new Abp01_Installer_Requirement_RequiredVersion($currentVersion, $requiredVersion);
		$this->assertSame($expected, $req->isSatisfied());
		$this->assertNull($req->getLastError());
	}

	public static function versionComparisonProvider() {
		return array(
			'major against major' => array('7', '7', true),
			'major against minor' => array('7', '7.0', true),
			'major against patch' => array('7', '7.0.0', true),
			'minor against major' => array('7.0', '7', true),
			'minor against minor' => array('7.0', '7.0', true),
			'minor against patch' => array('7.0', '7.0.0', true),
			'patch against major' => array('7.0.0', '7', true),
			'patch against minor' => array('7.0.0', '7.0', true),
			'patch against patch' => array('7.0.0', '7.0.0', true),
			'zero major against patch' => array('0', '0.0.0', true),
			'zero patch against major' => array('0.0.0', '0', true),
			'newer major' => array('8', '7.99.99', true),
			'older major' => array('6.99.99', '7', false),
			'newer minor' => array('7.1', '7.0.99', true),
			'older minor' => array('7.0.99', '7.1', false),
			'newer patch' => array('7.0.1', '7.0', true),
			'older patch' => array('7.0', '7.0.1', false),
			'multi-digit major' => array('10', '9', true),
			'multi-digit minor' => array('7.10', '7.2', true),
			'older multi-digit minor' => array('7.2', '7.10', false),
			'multi-digit patch' => array('7.0.10', '7.0.2', true),
			'older multi-digit patch' => array('7.0.2', '7.0.10', false),
			'fourth component retained' => array('7.0.0.1', '7.0.0.2', false),
			'newer fourth component' => array('7.0.0.2', '7.0.0.1', true),
			'fourth component against short release' => array('7.0.0.1', '7', true),
			'underscore numeric separators' => array('7_0', '7.0.0', true),
			'hyphen numeric separators' => array('7-0', '7.0.0', true),
			'plus numeric separators' => array('7+0', '7.0.0', true),
			'alternate separators in requirement' => array('7.0', '7_0_0', true),
			'dev before alpha' => array('7.0-dev', '7.0.0alpha1', false),
			'alpha after dev' => array('7.0alpha1', '7.0.0-dev', true),
			'alpha before beta' => array('7.0-alpha1', '7.0.0beta1', false),
			'beta after alpha' => array('7.0beta1', '7.0.0alpha1', true),
			'beta before RC' => array('7.0beta1', '7.0.0RC1', false),
			'RC after beta' => array('7.0RC1', '7.0.0beta1', true),
			'short major RC before stable' => array('7-RC1', '7.0.0', false),
			'short minor RC before stable' => array('7.0-RC1', '7.0.0', false),
			'full RC before short stable' => array('7.0.0RC1', '7', false),
			'stable after RC' => array('7', '7.0.0RC1', true),
			'equal short and full RC' => array('7.0RC1', '7.0.0RC1', true),
			'equal full and short RC' => array('7.0.0RC1', '7.0RC1', true),
			'RC number ordering' => array('7.0RC1', '7.0.0RC2', false),
			'multi-digit RC number' => array('7.0RC10', '7.0.0RC2', true),
			'underscore before suffix' => array('7.0_RC1', '7.0.0', false),
			'plus before suffix' => array('7.0+RC1', '7.0.0', false),
			'dot before suffix' => array('7.0.RC1', '7.0.0', false),
			'alpha alias' => array('7.0a1', '7.0.0alpha1', true),
			'beta alias' => array('7.0b1', '7.0.0beta1', true),
			'RC alias' => array('7.0rc1', '7.0.0RC1', true),
			'patch-level release after stable' => array('7.0pl1', '7.0.0', true),
			'stable before patch-level release' => array('7', '7.0.0pl1', false),
			'patch-level alias' => array('7.0p1', '7.0.0pl1', true),
			'newer release with prerelease suffix' => array('7.1-alpha1', '7.0.99', true),
			'unknown suffix retains PHP ordering' => array('7.0unknown1', '7.0.0-dev', false),
			'unknown equal suffix' => array('7.0unknown1', '7.0.0unknown1', true),
			'empty versions retain PHP equality' => array('', '', true),
			'empty current version' => array('', '7.0', false),
			'empty required version' => array('7.0', '', true),
			'non-numeric versions retain PHP equality' => array('dev', 'dev', true)
		);
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
