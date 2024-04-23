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

use StepanDalecky\KmlParser\Processor;

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track_DocumentParser_Kml implements Abp01_Route_Track_DocumentParser {
	public function __construct() {
		if (!self::isSupported()) {
			throw new Exception('The KML parser requirements are not met');
		}
	}

    public function parse($sourceString) { 
		if ($sourceString === null || empty($sourceString)) {
			throw new InvalidArgumentException('Empty KML string');
		}

		try {
			$document = new Abp01_Route_Track_Document();
			$delegate = new Abp01_Route_Track_DocumentParser_Kml_LibKmlProcessorDelegate($document);
			
			$processor = new Processor($delegate);
			$processor->processKmlString($sourceString);
			
			return $document;
		} catch (Exception $exc) {
			throw new Abp01_Route_Track_DocumentParser_Exception(
				$exc->getMessage(), 
				Abp01_Route_Track_DocumentParser_ErrorCode::ERROR_CATEGORY_DESERIALIZATION, 
				$exc->getCode()
			);
		}
		
		return null;
	}

	public static function isSupported() {
		return function_exists('simplexml_load_string') &&
			function_exists('simplexml_load_file');
	}

    public function getDefaultMimeType() { 
		return 'application/vnd.google-earth.kml+xml';
	}
}