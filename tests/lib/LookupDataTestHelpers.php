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

 trait LookupDataTestHelpers {
	use DbTestHelpers;
	use GenericTestHelpers;

	protected function _clearLookupDataTablesOnly() {
		$env = $this->_getEnv();
		$clearTables = array(
			$env->getLookupLangTableName(),
			$env->getLookupTableName()
		);

		foreach ($clearTables as $tableName) {
			$this->_truncateTables($this->_getDb(), $tableName);
		}
	}

    protected function _clearAllLookupData() {
		$env = $this->_getEnv();
		$db = $env->getDb();

		$lookupTableName = $env->getLookupTableName();
		$langTableName = $env->getLookupLangTableName();
		$postsLookupTableName = $env->getRouteDetailsLookupTableName();
		$routeDetailsTableName = $env->getRouteDetailsTableName();

		$this->_truncateTables($db,
			$routeDetailsTableName, 
			$postsLookupTableName, 
			$langTableName, 
			$lookupTableName);
	}

	protected function _readAllLookupData() {
		$env = $this->_getEnv();

		$db = $env->getDb();
		$lookup = $db->get($env->getLookupTableName());
		$lookupLang = $db->get($env->getLookupLangTableName());

		return array(
			'lookup' => $lookup,
			'lookupLang' => $lookupLang
		);
	}

	protected function _restoreAllLookupData($data) {
		$env = $this->_getEnv();

		$db = $env->getDb();
		foreach ($data['lookup'] as $insertRow) {
			$db->insert($env->getLookupTableName(), $insertRow);
		}
		
		foreach ($data['lookupLang'] as $insertRow) {
			$db->insert($env->getLookupLangTableName(), $insertRow);
		}
	}
 }