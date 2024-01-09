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

class AuditLogDataTests extends WP_UnitTestCase {
	use AuditLogDataHelpers;

	public function test_canCreateAuditLogData_nonEmpty() {
		$infoAuditLogData = $this->_generateTripSummaryInfoAuditLogData();
		$trackAuditLogData = $this->_generateTripSummaryTrackAuditLogData();

		$data = new Abp01_AuditLog_Data($infoAuditLogData, 
			$trackAuditLogData);

		$this->_assertInfoDataCorrect($infoAuditLogData, 
			$data);
		$this->_assertTrackDataCorrect($trackAuditLogData, 
			$data);
	}

	private function _assertInfoDataCorrect(array $infoAuditLogData, Abp01_AuditLog_Data $data) {
		$this->assertEquals($infoAuditLogData['route_data_created_at'], 
			$data->getInfoCreatedAt());
		$this->assertEquals($infoAuditLogData['route_data_last_modified_at'], 
			$data->getInfoLastModifiedAt());
		$this->assertEquals($infoAuditLogData['route_data_last_modified_by'], 
			$data->getInfoLastModifiedByUserId());
		$this->assertEquals($infoAuditLogData['route_data_last_modified_by_name'], 
			$data->getInfoLastModifiedByUserName());
	}

	private function _assertTrackDataCorrect(array $trackAuditLogData, Abp01_AuditLog_Data $data) {
		$this->assertEquals($trackAuditLogData['route_track_created_at'], 
			$data->getTrackCreatedAt());
		$this->assertEquals($trackAuditLogData['route_track_modified_at'], 
			$data->getTrackLastModifiedAt());
		$this->assertEquals($trackAuditLogData['route_track_modified_by'], 
			$data->getTrackLastModifiedByUserId());
		$this->assertEquals($trackAuditLogData['route_track_modified_by_name'], 
			$data->getTrackLastModifiedByUserName());
	}

	public function test_canCreateAuditLogData_empty() {
		$data = Abp01_AuditLog_Data::empty();
		$this->assertEmpty($data->getInfoCreatedAt());
		$this->assertEmpty($data->getInfoLastModifiedAt());
		$this->assertEmpty($data->getInfoLastModifiedByUserId());
		$this->assertEmpty($data->getInfoLastModifiedByUserName());

		$this->assertEmpty($data->getTrackCreatedAt());
		$this->assertEmpty($data->getTrackLastModifiedAt());
		$this->assertEmpty($data->getTrackLastModifiedByUserId());
		$this->assertEmpty($data->getTrackLastModifiedByUserName());
	}

	public function test_canConvertToPlainObject_nonEmpty() {
		$infoAuditLogData = $this->_generateTripSummaryInfoAuditLogData();
		$trackAuditLogData = $this->_generateTripSummaryTrackAuditLogData();

		$data = new Abp01_AuditLog_Data($infoAuditLogData, 
			$trackAuditLogData);

		$plainObject = $data->asPlainObject();
		$this->assertNotNull($plainObject);

		$this->_assertPlainObjectMatchesData($plainObject, 
			$data);
	}

	private function _assertPlainObjectMatchesData(stdClass $plainObject, Abp01_AuditLog_Data $data) {
		$this->assertEquals($data->getInfoCreatedAt(), 
			$plainObject->infoCreatedAt);
		$this->assertEquals($data->getInfoLastModifiedAt(), 
			$plainObject->infoLastModifiedAt);
		$this->assertEquals($data->getInfoLastModifiedByUserId(), 
			$plainObject->infoLastModifiedByUserId);
		$this->assertEquals($data->getInfoLastModifiedByUserName(), 
			$plainObject->infoLastModifiedByUserName);

		$this->assertEquals($data->getTrackCreatedAt(), 
			$plainObject->trackCreatedAt);
		$this->assertEquals($data->getTrackLastModifiedAt(), 
			$plainObject->trackLastModifiedAt);
		$this->assertEquals($data->getTrackLastModifiedByUserId(), 
			$plainObject->trackLastModifiedByUserId);
		$this->assertEquals($data->getTrackLastModifiedByUserName(), 
			$plainObject->trackLastModifiedByUserName);
	}

	public function test_canConvertToPlainObject_eEmpty() {
		$data = Abp01_AuditLog_Data::empty();

		$plainObject = $data->asPlainObject();
		$this->assertNotNull($plainObject);

		$this->_assertPlainObjectMatchesData($plainObject, $data);
		$this->_assertPlainObjectEmpty($plainObject);
	}

	private function _assertPlainObjectEmpty(stdClass $plainObject) {
		$this->assertEmpty($plainObject->infoCreatedAt);
		$this->assertEmpty($plainObject->infoLastModifiedAt);
		$this->assertEmpty($plainObject->infoLastModifiedByUserId);
		$this->assertEmpty($plainObject->infoLastModifiedByUserName);

		$this->assertEmpty($plainObject->trackCreatedAt);
		$this->assertEmpty($plainObject->trackLastModifiedAt);
		$this->assertEmpty($plainObject->trackLastModifiedByUserId);
		$this->assertEmpty($plainObject->trackLastModifiedByUserName);
	}
}