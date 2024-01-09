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

class FileValidatorProviderTests extends WP_UnitTestCase {
	public function test_canResolveFileValidator_registeredMimeType_noAdditionalRegistrations() {
		$provider = new Abp01_Transfer_Uploader_FileValidatorProvider();
		$this->_assertFileValidatorProviderCanResolveMimeTypes($provider, $this->_getRegisteredValidatorMimeTypes());
	}

	private function _assertFileValidatorProviderCanResolveMimeTypes(Abp01_Transfer_Uploader_FileValidatorProvider $provider, array $mimeTypeRegistrations) {
		foreach ($mimeTypeRegistrations as $mimeType => $validatorClass) {
			$validatorInstance = $provider->resolveValidator($mimeType);
			$this->assertNotNull($validatorInstance);
			$this->assertInstanceOf($validatorClass, $validatorInstance);
		}
	}

	public function test_tryResolveFileValidator_unregisteredMimeType() {
		$provider = new Abp01_Transfer_Uploader_FileValidatorProvider();
		foreach ($this->_getUnregisteredValidatorMimeTypes() as $mimeType) {
			$parserInstance = $provider->resolveValidator($mimeType);
			$this->assertNull($parserInstance);
		}
	}

	public function test_canCheckIfCanResolveMimeType_registeredMimeType_noAdditionalRegistrations() {
		$provider = new Abp01_Transfer_Uploader_FileValidatorProvider();
		$registeredMimeTypes = array_keys($this->_getRegisteredValidatorMimeTypes());
		$this->_assertFileValidatorProviderCanCheckIfCanResolveRegisteredMimeTypes($provider, $registeredMimeTypes);
	}

	private function _assertFileValidatorProviderCanCheckIfCanResolveRegisteredMimeTypes(Abp01_Transfer_Uploader_FileValidatorProvider $factory, array $mimeTypes) {
		foreach ($mimeTypes as $mimeType) {
			$this->assertTrue($factory->canResolveValidatorForMimeType($mimeType));
		}
	}

	public function test_canCheckIfCanresolveMimeType_unregisteredMimeType() {
		$provider = new Abp01_Transfer_Uploader_FileValidatorProvider();
		$unregisteredMimeTypes = $this->_getUnregisteredValidatorMimeTypes();
		foreach ($unregisteredMimeTypes as $mimeType) {
			$this->assertFalse($provider->canResolveValidatorForMimeType($mimeType));
		}
	}

	public function test_canRegisterValidatorForMimeTypes() {
		$factory = new Abp01_Transfer_Uploader_FileValidatorProvider();
		$newMimeTypeRegistrations = $this->_getNewFileValidatorMimeTypeRegistrations();
		
		$newMimeTypes = array_keys($newMimeTypeRegistrations);
		$factory->registerValidatorForMimeTypes(new MockFileValidator(), 
			$newMimeTypes);

		$checkMimeTypeRegistrations = array_merge($this->_getRegisteredValidatorMimeTypes(), 
			$newMimeTypeRegistrations);

		$this->_assertFileValidatorProviderCanResolveMimeTypes($factory, 
			$checkMimeTypeRegistrations);

		$checkMimeTypes = array_keys($checkMimeTypeRegistrations);
		$this->_assertFileValidatorProviderCanCheckIfCanResolveRegisteredMimeTypes($factory, 
			$checkMimeTypes);
	}

	private function _getNewFileValidatorMimeTypeRegistrations() {
		$newMimeTypeRegistrations = array();
		$mimeTypes = $this->_getUnregisteredValidatorMimeTypes();
		
		foreach ($mimeTypes as $mimeType) {
			$newMimeTypeRegistrations[$mimeType] = MockFileValidator::class;
		}

		return $newMimeTypeRegistrations;
	}

	private function _getRegisteredValidatorMimeTypes() {
		return array(
			'application/gpx' => Abp01_Validate_GpxDocument::class,
			'application/x-gpx+xml' => Abp01_Validate_GpxDocument::class,
			'application/xml-gpx' => Abp01_Validate_GpxDocument::class,
			'application/xml' => Abp01_Validate_GpxDocument::class,
			'application/gpx+xml' => Abp01_Validate_GpxDocument::class,
			'text/xml' => Abp01_Validate_GpxDocument::class,
			'application/json' => Abp01_Validate_GeoJsonDocument::class,
			'application/geo+json' => Abp01_Validate_GeoJsonDocument::class,
			'application/vnd.geo+json' => Abp01_Validate_GeoJsonDocument::class
		);
	}

	private function _getUnregisteredValidatorMimeTypes() {
		return array(
			'text/plain',
			'image/jpeg',
			'application/zip'
		);
	}
}