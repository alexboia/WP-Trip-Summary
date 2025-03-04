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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Installer_Step_Update_UpdateTo029 implements Abp01_Installer_Step_Update_Interface {

	private $_lastError;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var Abp01_Installer_Table_Definitions
	 */
	private $_tableDefs;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
		$this->_tableDefs = new Abp01_Installer_Table_Definitions($env);
	}

    public function execute() { 
		$this->_lastError = null;
		$this->_createTable($this->_getRouteLogTableName(), $this->_getRouteLogTableDefinition());
		return empty($this->_lastError);
	}

	private function _createTable($tableName, $tableDef) {
		$result = false;
		try {
			$service = new Abp01_Installer_Service_CreateDbTable($this->_env);
			$result = $service->execute($tableName, $tableDef);
		} catch (Exception $exc) {
			$this->_lastError = $exc;
		}
		return $result;
	}

	private function _getRouteLogTableDefinition() {
		return $this->_tableDefs->getRouteLogTableDefinition();
	}

	private function _getRouteLogTableName() {
		return $this->_env->getRouteLogTableName();
	}

    public function getLastError() { 
		return $this->_lastError;
	}

	public function getTargetVersion() { 
		return '0.2.9';
	}
}