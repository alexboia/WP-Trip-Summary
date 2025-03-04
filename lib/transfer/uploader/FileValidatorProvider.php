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

class Abp01_Transfer_Uploader_FileValidatorProvider {
	private $_validatorRegistrations = array();

	public function __construct() {
		$this->registerValidatorForMimeTypes(new Abp01_Validate_GpxDocument(), 
			Abp01_KnownMimeTypes::getGpxDocumentMimeTypes());
		$this->registerValidatorForMimeTypes(new Abp01_Validate_GeoJsonDocument(), 
			Abp01_KnownMimeTypes::getGeoJsonDocumentMimeTypes());
		$this->registerValidatorForMimeTypes(new Abp01_Validate_KmlDocument(), 
			Abp01_KnownMimeTypes::getKmlDocumentMimeTypes());
	}

	public function registerValidatorForMimeTypes(Abp01_Validate_File $validatorInstance, array $mimeTypes) {
		if (empty($mimeTypes)) {
			throw new InvalidArgumentException('Mime types list may not be empty');
		}

		$newRegistrations = array();
		foreach ($mimeTypes as $mimeType) {
			if (isset($this->_validatorRegistrations[$mimeType])) {
				throw new InvalidArgumentException('Validator mime types list overlap part of already registered mime types');
			}
			$newRegistrations[$mimeType] = $validatorInstance;
		}

		$this->_validatorRegistrations = array_merge($this->_validatorRegistrations, 
			$newRegistrations);

		return $this;
	}

	/**
	 * @return Abp01_Validate_File
	 * @throws InvalidArgumentException 
	 */
	public function resolveValidator($mimeType) {
		if (empty($mimeType)) {
			throw new InvalidArgumentException('Mime type may not be null');
		}

		return isset($this->_validatorRegistrations[$mimeType])
			? $this->_validatorRegistrations[$mimeType]
			: null;
	}

	public function canResolveValidatorForMimeType($mimeType) {
		return !empty($mimeType) && isset($this->_validatorRegistrations[$mimeType]);
	}

	public function getRecognizedDocumentMimeTypes() {
		return array_keys($this->_validatorRegistrations);
	}
}