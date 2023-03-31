<?php

use Mockery\LegacyMockInterface;

/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class RequirementCheckerTests extends WP_UnitTestCase {
	const FAILED_REQUIREMENT_STATUS_CODE_BASE = 1000;

	const COULD_NOT_DETECT_REQUIREMENT_STATUS_CODE = Abp01_Installer_RequirementStatusCode::COULD_NOT_DETECT_INSTALLATION_CAPABILITIES;

	public function test_noRequirements() {
		$provider = new Abp01_Installer_Requirement_Provider_Collection(array());
		$checker = new Abp01_Installer_Requirement_Checker($provider);
		
		$result = $checker->check();
		$this->assertEquals(Abp01_Installer_RequirementStatusCode::ALL_REQUIREMENTS_MET, $result);
		$this->assertNull($checker->getLastError());
	}

	public function test_allRequirementsSatisfied() {
		$provider = new Abp01_Installer_Requirement_Provider_Collection($this->_generateSuccessfulRequirements(5));	
		$checker = new Abp01_Installer_Requirement_Checker($provider);

		$result = $checker->check();
		$this->assertEquals(Abp01_Installer_RequirementStatusCode::ALL_REQUIREMENTS_MET, $result);
		$this->assertNull($checker->getLastError());
	}

	private function _generateSuccessfulRequirements($count) {
		$descriptors = array();

		for ($i = 0; $i < $count; $i ++) {
			$reqMock = $this->_generateRequirement(true);
			$descriptors[] = new Abp01_Installer_Requirement_Descriptor($reqMock, self::FAILED_REQUIREMENT_STATUS_CODE_BASE + $i);
		}

		return $descriptors;
	}

	/**
	 * @param bool $satisfied
	 * @return \Mockery\MockInterface|\Mockery\LegacyMockInterface|Abp01_Installer_Requirement
	 */
	private function _generateRequirement($satisfied, $lastError = null) {
		$reqMock = Mockery::mock(Abp01_Installer_Requirement::class);
		/** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|Abp01_Installer_Requirement $reqMock */
		$reqMock = $reqMock->shouldReceive('isSatisfied')
			->andReturn($satisfied)
			->getMock();

		$reqMock = $reqMock->shouldReceive('getLastError')
			->andReturn($lastError)
			->getMock();

		return $reqMock;
	}

	public function test_someRequirementsFailed_first_noLastError() {
		$provider = new Abp01_Installer_Requirement_Provider_Collection($this->_generateOneFailedThenSuccessfulRequirements(5, 0));	
		$checker = new Abp01_Installer_Requirement_Checker($provider);

		$result = $checker->check();
		$this->assertEquals(self::FAILED_REQUIREMENT_STATUS_CODE_BASE, $result);
		$this->assertNull($checker->getLastError());
	}

	public function test_someRequirementsFailed_first_withLastError() {
		$expectedExc = $this->_generateException();
		$provider = new Abp01_Installer_Requirement_Provider_Collection($this->_generateOneFailedThenSuccessfulRequirements(5, 0, $expectedExc));	
		$checker = new Abp01_Installer_Requirement_Checker($provider);

		$result = $checker->check();
		$this->assertEquals(self::FAILED_REQUIREMENT_STATUS_CODE_BASE, $result);
		$this->assertSame($expectedExc, $checker->getLastError());
	}

	private function _generateOneFailedThenSuccessfulRequirements($count, $failedIndex, $lastError = null) {
		$descriptors = array();

		for ($i = 0; $i < $count; $i ++) {
			if ($i == $failedIndex) {
				$reqMock = $reqMock = $this->_generateRequirement(false, $lastError);
			} else {
				$reqMock = $reqMock = $this->_generateRequirement(true, null);
			}

			$descriptors[] = new Abp01_Installer_Requirement_Descriptor($reqMock, self::FAILED_REQUIREMENT_STATUS_CODE_BASE + $i);
		}

		return $descriptors;
	}

	public function test_someRequirementsFailed_someInTheMiddle_noLastError() {
		$provider = new Abp01_Installer_Requirement_Provider_Collection($this->_generateOneFailedThenSuccessfulRequirements(5, 2));	
		$checker = new Abp01_Installer_Requirement_Checker($provider);

		$result = $checker->check();
		$this->assertEquals(self::FAILED_REQUIREMENT_STATUS_CODE_BASE + 2, $result);
		$this->assertNull($checker->getLastError());
	}

	public function test_someRequirementsFailed_last_noLastError() {
		$provider = new Abp01_Installer_Requirement_Provider_Collection($this->_generateOneFailedThenSuccessfulRequirements(5, 4));	
		$checker = new Abp01_Installer_Requirement_Checker($provider);

		$result = $checker->check();
		$this->assertEquals(self::FAILED_REQUIREMENT_STATUS_CODE_BASE + 4, $result);
		$this->assertNull($checker->getLastError());
	}

	public function test_whenExceptionFailed() {
		$req = $this->_generateRequirementWithException();
		$expectedExc = $req->getLastError();

		$provider = new Abp01_Installer_Requirement_Provider_Collection(array(
			new Abp01_Installer_Requirement_Descriptor($req, self::FAILED_REQUIREMENT_STATUS_CODE_BASE)
		));	

		$checker = new Abp01_Installer_Requirement_Checker($provider);

		$result = $checker->check();
		$this->assertEquals(self::COULD_NOT_DETECT_REQUIREMENT_STATUS_CODE, $result);
		$this->assertSame($expectedExc, $checker->getLastError());
	}

	/**
	 * @return \Mockery\MockInterface|\Mockery\LegacyMockInterface|Abp01_Installer_Requirement
	 */
	private function _generateRequirementWithException() {
		$exc = $this->_generateException();
		$reqMock = Mockery::mock(Abp01_Installer_Requirement::class);
		/** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|Abp01_Installer_Requirement $reqMock */
		$reqMock = $reqMock->shouldReceive('isSatisfied')
			->andThrow($exc)
			->getMock();

		$reqMock = $reqMock->shouldReceive('getLastError')
			->andReturn($exc)
			->getMock();

		return $reqMock;
	}

	private function _generateException() {
		return new Exception('Something bad happened');
	}
}