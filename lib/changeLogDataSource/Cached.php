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

class Abp01_ChangeLogDataSource_Cached implements Abp01_ChangeLogDataSource {
	const OPT_CHANGELOG_CACHE_KEY = 'abp01.option.changeLogCache';
	
	/**
	 * @var Abp01_ChangeLogDataSource
	 */
	private $_dataSource;

	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct(Abp01_ChangeLogDataSource $dataSource, Abp01_Env $env) {
		$this->_dataSource = $dataSource;
		$this->_env = $env;
	}

	public static function clearCache() {
		delete_option(self::OPT_CHANGELOG_CACHE_KEY);
	}

	public function getChangeLog() {
		$changeLog = null;
		$cachedChangeLog = $this->_readCachedChangeLog();

		if (!$this->_isCachedChangeLogValid($cachedChangeLog)) {
			$changeLog = $this->_dataSource->getChangeLog();
			$this->_cacheChangeLogData($changeLog);
		} else {
			$changeLog = $cachedChangeLog['_data'];
		}

		return $changeLog;
	}

	private function _readCachedChangeLog() {
		return get_option(self::OPT_CHANGELOG_CACHE_KEY, null);
	}

	private function _isCachedChangeLogValid($cachedChangeLog) {
		return $cachedChangeLog !== null 
			&& is_array($cachedChangeLog) 
			&& isset($cachedChangeLog['_version'])
			&& isset($cachedChangeLog['_data'])
			&& $cachedChangeLog['_version'] == $this->_getCurrentVersion();
	}

	private function _getCurrentVersion() {
		return $this->_env->getVersion();
	}

	private function _cacheChangeLogData($changeLog) {
		$changeLogCache = array(
			'_version' => $this->_getCurrentVersion(),
			'_data' => $changeLog
		);

		update_option(self::OPT_CHANGELOG_CACHE_KEY, 
			$changeLogCache, 
			true);
	}
}