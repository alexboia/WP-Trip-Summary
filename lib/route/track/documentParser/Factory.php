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

class Abp01_Route_Track_DocumentParser_Factory {
	private $_parserRegistrations = array();

	public function __construct() {
		$this->registerDocumentParserForMimeTypes(Abp01_Route_Track_DocumentParser_Gpx::class, 
			Abp01_KnownMimeTypes::getGpxDocumentMimeTypes());

		$this->registerDocumentParserForMimeTypes(Abp01_Route_Track_DocumentParser_GeoJson::class, 
			Abp01_KnownMimeTypes::getGeoJsonDocumentMimeTypes());

		$this->registerDocumentParserForMimeTypes(Abp01_Route_Track_DocumentParser_Kml::class, 
			Abp01_KnownMimeTypes::getKmlDocumentMimeTypes());
	}

	public function registerDocumentParserForMimeTypes($documentParserClass, array $mimeTypes) {
		if (empty($documentParserClass)) {
			throw new InvalidArgumentException('Document parser class may not be empty');
		}

		if (empty($mimeTypes)) {
			throw new InvalidArgumentException('Document parser mime types list may not be empty');
		}

		if (!$this->_classImplementsDocumentParserInterface($documentParserClass)) {
			throw new InvalidArgumentException('Document parser class does not implement <' . Abp01_Route_Track_DocumentParser::class . '>');
		}

		if ($this->_mimeTypesOverlapRegisteredDocumentParsers($mimeTypes)) {
			throw new InvalidArgumentException('Document parser mime types list overlap part of already registered mime types');
		}

		$this->_parserRegistrations[$documentParserClass] = $mimeTypes;
		return $this;
	}

	private function _classImplementsDocumentParserInterface($documentParserClass) {
		return in_array(Abp01_Route_Track_DocumentParser::class, 
			class_implements($documentParserClass));
	}

	private function _mimeTypesOverlapRegisteredDocumentParsers(array $mimeTypes) {
		$overlaps = false;
		foreach ($this->_parserRegistrations as $parentMimeTypes) {
			$overlaps = !empty(array_intersect($mimeTypes, $parentMimeTypes));
			if ($overlaps) {
				break;
			}
		}
		return $overlaps;
	}

	public function getRecognizedDocumentMimeTypes() {
		$allMimeTypes = array();

		foreach ($this->_parserRegistrations as $parserMimeTypes) {
			$allMimeTypes = array_merge($allMimeTypes, $parserMimeTypes);
		}

		return $allMimeTypes;
	}

	/**
	 * @param string $mimeType 
	 * @return Abp01_Route_Track_DocumentParser|null
	 * @throws InvalidArgumentException 
	 */
	public function resolveDocumentParser($mimeType) {
		if (empty($mimeType)) {
			throw new InvalidArgumentException('Mime type may not be null');
		}

		$parserClass = $this->_resolveDocumentParserClassForMimeType($mimeType);

		$parserInstance = null;
		if (!empty($parserClass)) {
			$parserInstance = new $parserClass();
		}

		return $parserInstance;
	}

	private function _resolveDocumentParserClassForMimeType($mimeType) {
		$parserClass = null;
		foreach ($this->_parserRegistrations as $candidateClass => $parserMimeTypes) {
			if (in_array($mimeType, $parserMimeTypes)) {
				$parserClass = $candidateClass;
				break;
			}
		}
		return $parserClass;
	}

	public function canResolveDocumentParserForMimeType($mimeType) {
		$parserClass = $this->_resolveDocumentParserClassForMimeType($mimeType);
		return !empty($parserClass);
	}
}