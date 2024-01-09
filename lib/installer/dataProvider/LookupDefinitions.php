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

class Abp01_Installer_DataProvider_LookupDefinitions {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	private $_cachedDefinitions = null;

	private $_lastError;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;	
	}

	public function read() {
		$this->_clearLastError();
		return $this->_readLookupDefinitions();
	}

	private function _clearLastError() {
		$this->_lastError = null;
	}

	private function _readLookupDefinitions() {
		if ($this->_cachedDefinitions === null) {			
			$definitions = array();
			$filePath = $this->_getLookupDefsFile();
			$categories = array(
				Abp01_Lookup::BIKE_TYPE,
				Abp01_Lookup::DIFFICULTY_LEVEL,
				Abp01_Lookup::PATH_SURFACE_TYPE,
				Abp01_Lookup::RAILROAD_ELECTRIFICATION,
				Abp01_Lookup::RAILROAD_LINE_STATUS,
				Abp01_Lookup::RAILROAD_OPERATOR,
				Abp01_Lookup::RAILROAD_LINE_TYPE,
				Abp01_Lookup::RECOMMEND_SEASONS
			);

			if (!is_readable($filePath)) {
				return null;
			}

			$prevUseErrors = libxml_use_internal_errors(true);
			$xml = simplexml_load_file($filePath, 'SimpleXMLElement');

			if ($xml) {
				foreach ($categories as $c) {
					$definitions[$c] = $this->_parseDefinitions($xml, $c);
				}
			} else {
				$this->_lastError = libxml_get_last_error();
				libxml_clear_errors();
			}

			libxml_use_internal_errors($prevUseErrors);
			$this->_cachedDefinitions = $definitions;
		}

		return $this->_cachedDefinitions;
	}

	private function _getLookupDefsFile() {
		$env = $this->_env;
		$dataDir = $env->getDataDir();

		if ($env->isDebugMode()) {
			$dirName = 'dev/setup';
			$testDir = sprintf('%s/%s', $dataDir, $dirName);
			if (!is_dir($testDir)) {
				$dirName = 'setup';
			}
		} else {
			$dirName = 'setup';
		}

		$filePath = sprintf('%s/%s/lookup-definitions.xml', $dataDir, $dirName);
		return $filePath;
	}
	
	private function _parseDefinitions($xml, $category) {
		$lookup = array();
		$node = $xml->{$category};
		if (empty($node) || empty($node->lookup)) {
			return array();
		}
		foreach ($node->lookup as $lookupNode) {
			if (empty($lookupNode['default'])) {
				continue;
			}
			$lookup[] = array(
				'default' => (string)$lookupNode['default'],
				'translations' => $this->_readLookupTranslations($lookupNode)
			);
		}

		return $lookup;
	}

	private function _readLookupTranslations($xml) {
		$translations = array();
		if (empty($xml->lang)) {
			return array();
		}
		foreach ($xml->lang as $langNode) {
			if (empty($langNode['code'])) {
				continue;
			}
			$tx = (string)$langNode;
			if (!empty($tx)) {
				$translations[(string)$langNode['code']] = $tx;
			}
		}
		return $translations;
	}

	public function getLastError() {
		return $this->_lastError;
	}
}