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

class Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions implements Abp01_Installer_Requirement {
	const EXPECTED_RESULT = 'POLYGON((1 2,3 2,3 4,1 4,1 2))';
	
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var \Exception|null
	 */
	private $_lastError = null;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	/**
	 * @return bool 
	 */
	public function isSatisfied() { 
		$this->_reset();

		$result = false;
		$db = $this->_env->getDb();
		$expected = self::EXPECTED_RESULT;

		if (!$db) {
			return false;
		}

		try {
			$spatialTest = $db->rawQuery('SELECT ST_AsText(ST_Envelope(LINESTRING(
				ST_GeomFromText(ST_AsText(POINT(1, 2)), 3857),
				ST_GeomFromText(ST_AsText(POINT(3, 4)), 3857)
			))) AS SPATIAL_TEST');
		} catch (Exception $exc) {
			$this->_lastError = $exc;
			$spatialTest = null;
		}

		if (!empty($spatialTest) && is_array($spatialTest)) {
			$result = strcasecmp($spatialTest[0]['SPATIAL_TEST'], $expected) === 0;
		}

		return $result;
	}

	private function _reset() {
		$this->_lastError = null;
	}

	/**
	 * @return Exception|null 
	 */
	public function getLastError() {
		return $this->_lastError;
	}
}