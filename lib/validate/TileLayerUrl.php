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
 * Uses the core functionality from the simple url validator (@see Abp01_Validate_Url) with the following changes:
 * - protocols are restricted to http://, https://, ftp:// and ftps://
 * - it checks for presence of the required placeholders: {z} - zoom, {x} - tile X, {y} - tile Y
 * - it does not allow empty values
 * */
class Abp01_Validate_TileLayerUrl extends Abp01_Validate_Url {
	/**
	 * @var array The placeholders to check for
	 * */
	private $_checkPlaceholders = array('{z}', '{x}', '{y}');

	/**
	 * Initializes a new instance
	 * */
	public function __construct() {
		parent::__construct(false, array('http://', 'https://', 'ftp://', 'ftps://'));
	}

	/**
	 * Prepares a tile layer URL for basic URL format validation. 
	 * The issue is that the domain main contain a placeholder for the subdomain and this would fail the validation.
	 * To overcome this, we remove the "{" and "}" from the placeholders.
	 * @param string $tileLayerUrl The URL to prepare
	 * @return string The prepared URl
	 * */
	private function _prepareForValidation($tileLayerUrl) {
		return preg_replace('/(\{([a-zA-z0-9]+)\})/','$2', $tileLayerUrl);
	}

	/**
	 * Validates that a given URL is valid as a tile layer URL template
	 * @param string $tileLayerUrl The URL to check for
	 * @return boolean True if it's valid, false otherwise
	 * */
	public function validate($tileLayerUrl) {
		//prepare URL for basic format testing
		$safeTestUrl = $this->_prepareForValidation($tileLayerUrl);
		//should not be empty and should be valid URL format
		if (!parent::validate($safeTestUrl)) {
			return false;
		}

		//test for required placeholders
		foreach ($this->_checkPlaceholders as $p) {
			if (stripos($tileLayerUrl, $p) === false) {
				return false;
			}
		}
		
		return true;
	}
}