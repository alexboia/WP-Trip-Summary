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

/** @property \IntegerIdGenerator $_idGenerator */
trait RouteLogTestHelpers {
	use GenericTestHelpers;

	protected function _generatePostId(array $excludeIds = array()) {
		return $this->_idGenerator->generateId($excludeIds);
	}

	protected function _generateCurrentUserId() {
		return self::_getFaker()->numberBetween(1, PHP_INT_MAX);
	}

	protected function _generateLogEntryId() {
		return self::_getFaker()->numberBetween(1, PHP_INT_MAX);
	}

	protected function _generateRouteLogEntries($postId = null, $count = null) {
		if (empty($postId)) {
			$postId = $this->_generatePostId();
		}

		if (empty($count)) {
			$count = self::_getFaker()->numberBetween(1, 100);
		}

		$logEntries = array();

		for ($i = 0; $i < $count; $i ++) {
			$logEntries[] = $this->_generateRouteLogEntry($postId);
		}

		return $logEntries;
	}

	protected function _generateRouteLogEntry($postId = null, $logEntryId = null, $createdByUserId = null) {
		if (empty($logEntryId)) {
			$logEntryId = $this->_generateLogEntryId();
		}

		$data = $this->_generateRouteLogEntryData($postId, 
			$createdByUserId);
		
		$data['log_ID'] = $logEntryId;
		$logEntry = Abp01_Route_Log_Entry::fromDbArray($data);

		return $logEntry;
	}

	protected function _generateRouteLogEntryData($postId = null, $createdByUserId = null) {
		if (empty($postId)) {
			$postId = $this->_generatePostId();
		}

		if (empty($createdByUserId)) {
			$createdByUserId = $this->_generateCurrentUserId();
		}

		return $this->_updateRouteLogEntryData(array(
			'log_post_ID' => $postId,
			'log_created_by' => $createdByUserId
		), $createdByUserId);
	}

	protected function _updateRouteLogEntryData($routeLogEntryData, $updatedByUserId = null) {
		$faker = self::_getFaker();

		if (empty($updatedByUserId)) {
			$updatedByUserId = $this->_generateCurrentUserId();
		}

		return array_merge($routeLogEntryData, array(
			'log_rider' => sprintf('%s %s', $faker->firstName, $faker->lastName),
			'log_date' => $faker->date(),
			'log_vehicle' => $faker->realText(),
			'log_gear' => $faker->realText(),
			'log_notes' => $faker->boolean() ? $faker->realText() : null,
			'log_duration_hours' => $faker->numberBetween(1, 100),
			'log_is_public' => $faker->boolean() ? 1 : 0,
			'log_updated_by' => $updatedByUserId
		));
	}

	protected function _assertLogEntriesMatch(Abp01_Route_Log_Entry $expected, Abp01_Route_Log_Entry $actual) {
		$this->assertEquals($expected->id, $actual->id);
		$this->assertEquals($expected->postId, $actual->postId);
		$this->assertEquals($expected->rider, $actual->rider);
		$this->assertEquals($expected->gear, $actual->gear);
		$this->assertEquals($expected->vehicle, $actual->vehicle);
		$this->assertEquals($expected->notes, $actual->notes);
		$this->assertEquals($expected->date, $actual->date);
		$this->assertEquals($expected->timeInHours, $actual->timeInHours);
		$this->assertEquals($expected->isPublic, $actual->isPublic);
		$this->assertEquals($expected->createdBy, $actual->createdBy);
	}
 }