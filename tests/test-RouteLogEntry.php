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

 class RouteLogEntryTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use RouteLogTestHelpers;

	/**
	 * @var IntegerIdGenerator
	 */
	private $_idGenerator;

	public function __construct($name = null, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->_idGenerator = new IntegerIdGenerator();
	}

	public function test_canCreateFromDbArray() {
		$count = self::_getFaker()->numberBetween(1, 100);
		for ($i = 0; $i < $count; $i ++) {
			$postId = $this->_generatePostId();
			$routeLogEntryData = $this->_generateRouteLogEntryData($postId);
			$logEntry = Abp01_Route_Log_Entry::fromDbArray($routeLogEntryData);
	
			$this->assertNotNull($logEntry);
			$this->_assertRouteLogEntryMatchesDbData($routeLogEntryData, 
				$logEntry);
		}
	}

	private function _assertRouteLogEntryMatchesDbData(array $data, Abp01_Route_Log_Entry $logEntry) {
		if (!empty($data['log_ID'])) {
			$this->assertEquals($logEntry->id, $data['log_ID']);
		} else {
			$this->assertEquals(0, $logEntry->id);
		}

		$this->assertEquals($logEntry->postId, $data['log_post_ID']);
		
		$this->assertEquals($logEntry->rider, $data['log_rider']);
		$this->assertEquals($logEntry->date, $data['log_date']);
		$this->assertEquals($logEntry->vehicle, $data['log_vehicle']);
		$this->assertEquals($logEntry->gear, $data['log_gear']);
		$this->assertEquals($logEntry->timeInHours, $data['log_duration_hours']);
		$this->assertEquals($logEntry->notes, $data['log_notes']);

		if ($data['log_is_public'] === 1) {
			$this->assertTrue($logEntry->isPublic);
		} else {
			$this->assertFalse($logEntry->isPublic);
		}
		
		$this->assertEquals($logEntry->createdBy, 
			$data['log_created_by']);
		$this->assertEquals($logEntry->lastUpdatedBy, 
			$data['log_updated_by']);
	}

	public function test_canConvertToDbArray() {
		$count = self::_getFaker()->numberBetween(1, 100);
		for ($i = 0; $i < $count; $i ++) {
			$logEntry = $this->_generateRouteLogEntry();
			$logEntryData = $logEntry->toDbArray();

			$this->assertNotNull($logEntryData);
			$this->assertNotEmpty($logEntryData);

			$this->_assertRouteLogEntryMatchesDbData($logEntryData, 
				$logEntry);
		}
	}

	public function test_canConvertToPlainObject() {
		$count = self::_getFaker()->numberBetween(1, 100);
		for ($i = 0; $i < $count; $i ++) {
			$logEntry = $this->_generateRouteLogEntry();
			$logEntryData = $logEntry->toPlainObject();

			$this->assertNotNull($logEntryData);
			$this->assertNotEmpty($logEntryData);

			$this->_assertRouteLogEntryMatchesPlainObject($logEntryData, 
				$logEntry);
		}
	}

	private function _assertRouteLogEntryMatchesPlainObject(stdClass $data, Abp01_Route_Log_Entry $logEntry) {
		$this->assertEquals($logEntry->id, $data->id);
		$this->assertEquals($logEntry->postId, $data->postId);
		
		$this->assertEquals($logEntry->rider, $data->rider);
		$this->assertEquals($logEntry->date, $data->date);
		$this->assertEquals($logEntry->vehicle, $data->vehicle);
		$this->assertEquals($logEntry->gear, $data->gear);
		$this->assertEquals($logEntry->timeInHours, $data->timeInHours);
		$this->assertEquals($logEntry->notes, $data->notes);
		$this->assertEquals($logEntry->isPublic, $data->isPublic);

		$this->assertEquals($logEntry->createdBy, $data->createdBy);
		$this->assertEquals($logEntry->lastUpdatedBy, $data->lastUpdatedBy);
	}
 }