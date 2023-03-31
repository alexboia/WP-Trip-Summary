<?php
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

class HasRequiredMysqlSpatialFunctionsRequirementTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	const QUERY_HAVE_GEOMETRY_FUNCTIONS_CHECK = 
		'SELECT ST_AsText(ST_Envelope(LINESTRING(
			ST_GeomFromText(ST_AsText(POINT(1, 2)), 3857),
			ST_GeomFromText(ST_AsText(POINT(3, 4)), 3857)
		))) AS SPATIAL_TEST';

	const QUERY_HAVE_GEOMETRY_FUNCTIONS_CHECK_RESULT_OK = 
		'POLYGON((1 2,3 2,3 4,1 4,1 2))';

	protected function setUp(): void {
		MysqliDbTestWrapper::resetRawQueryFixtures();
	}

	protected function tearDown(): void {
		MysqliDbTestWrapper::resetRawQueryFixtures();
	}

	public function test_canCheck_whenAvailable() {
		$this->_setupDbTestWrapperForCheckOk();
		$req = new Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions($this->_getEnv());

		$this->assertTrue($req->isSatisfied());
		$this->assertNull($req->getLastError());
	}

	private function _setupDbTestWrapperForCheckOk() {
		MysqliDbTestWrapper::setUpRawQueryFixtures(array(
			'query' => self::QUERY_HAVE_GEOMETRY_FUNCTIONS_CHECK,
			'return' => $this->_haveRequiredFunctionsResult(self::QUERY_HAVE_GEOMETRY_FUNCTIONS_CHECK_RESULT_OK)
		));
	}

	public function test_canCheck_whenNotAvailable() {
		$this->_setupDbTestWrapperForCheckFailed();
		$req = new Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions($this->_getEnv());

		$this->assertFalse($req->isSatisfied());
		$this->assertNull($req->getLastError());
	}

	private function _setupDbTestWrapperForCheckFailed() {
		MysqliDbTestWrapper::setUpRawQueryFixtures(array(
			'query' => self::QUERY_HAVE_GEOMETRY_FUNCTIONS_CHECK,
			'return' => $this->_haveRequiredFunctionsResult('__BOGUS__')
		));
	}

	public function test_canCheck_whenExceptionThrown() {
		$throwExc = $this->_setupDbTestWrapperForCheckFailedWithException();
		$req = new Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions($this->_getEnv());

		$this->assertFalse($req->isSatisfied());
		$this->assertSame($throwExc, $req->getLastError());
	}

	private function _setupDbTestWrapperForCheckFailedWithException() {
		$throwExc = new mysqli_sql_exception('Something really bad happened here!');
		MysqliDbTestWrapper::setUpRawQueryFixtures(array(
			'query' => self::QUERY_HAVE_GEOMETRY_FUNCTIONS_CHECK,
			'return' => $throwExc
		));
		return $throwExc;
	}

	public function test_canCheck_realDbHit() {
		$req = new Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions($this->_getEnv());
		$this->assertTrue($req->isSatisfied());
		$this->assertNull($req->getLastError());
	}

	public function test_lastErrorIsReset() {
		$throwExc = $this->_setupDbTestWrapperForCheckFailedWithException();
		$req = new Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions($this->_getEnv());

		$this->assertFalse($req->isSatisfied());
		$this->assertSame($throwExc, $req->getLastError());

		MysqliDbTestWrapper::resetRawQueryFixtures();
		$this->assertTrue($req->isSatisfied());
		$this->assertNull($req->getLastError());
	}

	private function _haveRequiredFunctionsResult($value) {
		return array(
			array(
				'SPATIAL_TEST' => $value
			)
		);
	}
}