<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

/**
 * Class used to validate a URL. The validation has two stages:
 * - check that it has a valid url format;
 * - check that the URL starts with one of the accepted protocols.
 * If the list of accepted protocols is empty, then it is considered that any protocol is accepted
 * */
class Abp01_Validate_Url {
	/**
	 * @var boolean Whether empty strings are considered valid or not
	 * */
	private $_allowEmpty = true;

	/**
	 * @var array The list of allowed protocols. If set to empty, any protocol is allowed
	 * */
	private $_allowedProtocols = array();

	/**
	 * Initializes a new instance.
	 * @param boolean $allowEmpty Whether empty strings are considered valid or not. Defaults to true.
	 * @param array $allowedProtocols The list of allowed protocols. If set to empty, any protocol is allowed
	 * */ 
	public function __construct($allowEmpty = true, array $allowedProtocols = array('http://', 'https://', 'mailto:', 'ftp://', 'ftps://')) {
		$this->_allowedProtocols = $allowedProtocols;
		$this->_allowEmpty = !!$allowEmpty;
	}

	/**
	 * Checks that the given URL starts with one of the allowed protocols
	 * @param string $url The URL to check
	 * @return boolean True if valid, false otherwise
	 * */
	private function _checkProtocols($url) {
		//no allowed protocols configured - anything goes
		if (!count($this->_allowedProtocols)) {
			return true;
		}
		foreach ($this->_allowedProtocols as $p) {
			if (stripos($url, $p) === 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates the given URL. See class description for more details
	 * @param string $url The URL to validate
	 * @return boolean True if it's valid, false otherwise
	 * */
	public function validate($url) {
		$url = trim($url);
		if (empty($url)) {
			return $this->_allowEmpty;
		} 
		return filter_var($url, FILTER_VALIDATE_URL) !== false
			&& $this->_checkProtocols($url);
	}
}