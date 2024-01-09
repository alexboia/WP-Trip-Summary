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

class Abp01_Installer_Service_InstallLookupData {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	private $_lastError = null;

	/**
	 * @var Abp01_Installer_DataProvider_LookupDefinitions
	 */
	private $_lookupDataProvider;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
		$this->_lookupDataProvider = new Abp01_Installer_DataProvider_LookupDefinitions($env);
	}

	public function execute() {
		$db = $this->_env->getDb();
		$table = $this->_getLookupTableName();
		$langTable = $this->_getLookupLangTableName();
		$definitions = $this->_readLookupDefinitions();
		$ok = true;

		if (!$db || !is_array($definitions)) {
			return false;
		}

		//make sure table is empty
		$stats = $db->getOne($table, 'COUNT(*) AS cnt');
		if ($stats && is_array($stats) && $stats['cnt'] > 0) {
			return true;
		}

		//save lookup data
		foreach ($definitions as $category => $data) {
			if (empty($data)) {
				continue;
			}
			foreach ($data as $lookup) {
				$id = $db->insert($table, array(
					'lookup_category' => $category,
					'lookup_label' => $lookup['default']
				));

				$ok = $ok && $id !== false;
				if (!$ok) {
					break 2;
				}

				foreach ($lookup['translations'] as $lang => $label) {
					$ok = $ok && $db->insert($langTable, array(
						'ID' => $id,
						'lookup_lang' => $lang,
						'lookup_label' => $label
					)) !== false;
					if (!$ok) {
						break 3;
					}
				}
			}
		}

		return $ok;
	}

	private function _readLookupDefinitions() {
		$lookupDefinitions = $this->_lookupDataProvider->read();
		$this->_lastError = $this->_lookupDataProvider->getLastError();
		return $lookupDefinitions;
	}

	private function _getLookupTableName() {
		return $this->_env->getLookupTableName();
	}

	private function _getLookupLangTableName() {
		return $this->_env->getLookupLangTableName();
	}

	public function getLastError() {
		return $this->_lastError;
	}
}