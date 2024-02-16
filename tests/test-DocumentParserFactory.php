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

class DocumentParserFactoryTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canResolveExistingDocumentParser_registeredMimeType_noAdditionalRegistrations() {
		$factory = new Abp01_Route_Track_DocumentParser_Factory();
		$this->_assertDocumentFactoryCanResolveMimeTypes($factory, $this->_getRegisteredDocumentParserMimeTypes());
	}

	private function _assertDocumentFactoryCanResolveMimeTypes(Abp01_Route_Track_DocumentParser_Factory $factory, array $mimeTypeRegistrations) {
		foreach ($mimeTypeRegistrations as $mimeType => $documentParserClass) {
			$parserInstance = $factory->resolveDocumentParser($mimeType);
			$this->assertNotNull($parserInstance);
			$this->assertInstanceOf($documentParserClass, $parserInstance);
		}
	}

	public function test_tryResolveExistingDocumentParser_unregisteredMimeType() {
		$factory = new Abp01_Route_Track_DocumentParser_Factory();
		foreach ($this->_getUnregisteredDocumentParserMimeTypes() as $mimeType) {
			$parserInstance = $factory->resolveDocumentParser($mimeType);
			$this->assertNull($parserInstance);
		}
	}

	public function test_canGetRecognizedMimeTypes_noAdditionalRegistrations() {
		$factory = new Abp01_Route_Track_DocumentParser_Factory();
		$expectedMimeTypes = array_keys($this->_getRegisteredDocumentParserMimeTypes());
		$this->assertEquals($expectedMimeTypes, $factory->getRecognizedDocumentMimeTypes());
	}

	public function test_canCheckIfCanResolveMimeType_registeredMimeType_noAdditionalRegistrations() {
		$factory = new Abp01_Route_Track_DocumentParser_Factory();
		$registeredMimeTypes = array_keys($this->_getRegisteredDocumentParserMimeTypes());
		$this->_assertDocumentFactoryCanCheckIfCanResolveRegisteredMimeTypes($factory, $registeredMimeTypes);
	}

	private function _assertDocumentFactoryCanCheckIfCanResolveRegisteredMimeTypes(Abp01_Route_Track_DocumentParser_Factory $factory, array $mimeTypes) {
		foreach ($mimeTypes as $mimeType) {
			$this->assertTrue($factory->canResolveDocumentParserForMimeType($mimeType));
		}
	}

	public function test_canCheckIfCanresolveMimeType_unregisteredMimeType() {
		$factory = new Abp01_Route_Track_DocumentParser_Factory();
		$unregisteredMimeTypes = $this->_getUnregisteredDocumentParserMimeTypes();
		foreach ($unregisteredMimeTypes as $mimeType) {
			$this->assertFalse($factory->canResolveDocumentParserForMimeType($mimeType));
		}
	}

	public function test_canRegisterDocumentParserForMimeTypes() {
		$factory = new Abp01_Route_Track_DocumentParser_Factory();
		$newMimeTypeRegistrations = $this->_getNewDocumentParserMimeTypeRegistrations();
		
		$newMimeTypes = array_keys($newMimeTypeRegistrations);
		$factory->registerDocumentParserForMimeTypes(MockDocumentParser::class, 
			$newMimeTypes);

		$checkMimeTypeRegistrations = array_merge($this->_getRegisteredDocumentParserMimeTypes(), 
			$newMimeTypeRegistrations);

		$this->_assertDocumentFactoryCanResolveMimeTypes($factory, 
			$checkMimeTypeRegistrations);

		$checkMimeTypes = array_keys($checkMimeTypeRegistrations);
		$this->_assertDocumentFactoryCanCheckIfCanResolveRegisteredMimeTypes($factory, 
			$checkMimeTypes);
	}

	private function _getNewDocumentParserMimeTypeRegistrations() {
		$newMimeTypeRegistrations = array();
		$mimeTypes = $this->_getUnregisteredDocumentParserMimeTypes();
		
		foreach ($mimeTypes as $mimeType) {
			$newMimeTypeRegistrations[$mimeType] = MockDocumentParser::class;
		}

		return $newMimeTypeRegistrations;
	}

	private function _getRegisteredDocumentParserMimeTypes() {
		return array(
			'application/gpx' => Abp01_Route_Track_DocumentParser_Gpx::class,
			'application/x-gpx+xml' => Abp01_Route_Track_DocumentParser_Gpx::class,
			'application/xml-gpx' => Abp01_Route_Track_DocumentParser_Gpx::class,
			'application/xml' => Abp01_Route_Track_DocumentParser_Gpx::class,
			'application/gpx+xml' => Abp01_Route_Track_DocumentParser_Gpx::class,
			'text/xml' => Abp01_Route_Track_DocumentParser_Gpx::class,
			'application/json' => Abp01_Route_Track_DocumentParser_GeoJson::class,
			'application/geo+json' => Abp01_Route_Track_DocumentParser_GeoJson::class,
			'application/vnd.geo+json' => Abp01_Route_Track_DocumentParser_GeoJson::class,
			'application/vnd.google-earth.kml+xml' => Abp01_Route_Track_DocumentParser_Kml::class
		);
	}

	private function _getUnregisteredDocumentParserMimeTypes() {
		return array(
			'text/plain',
			'image/jpeg',
			'application/zip'
		);
	}
}