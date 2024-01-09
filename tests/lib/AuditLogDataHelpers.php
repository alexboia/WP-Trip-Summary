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

trait AuditLogDataHelpers {
	use GenericTestHelpers;

	private function _getDateFormat() {
		return 'Y-m-d H:i:s';
	}

	protected function _generateTripSummaryInfoAuditLogData() {
		$faker = $this->_getFaker();
		return array(
			'route_data_created_at' => $faker->dateTime->format($this->_getDateFormat()),
			'route_data_last_modified_at' => $faker->dateTime->format($this->_getDateFormat()),
			'route_data_last_modified_by' => $faker->numberBetween(1, PHP_INT_MAX),
			'route_data_last_modified_by_name' => $faker->userName
		);
	}

	protected function _generateTripSummaryTrackAuditLogData() {
		$faker = $this->_getFaker();
		return array(
			'route_track_created_at' => $faker->dateTime->format($this->_getDateFormat()),
			'route_track_modified_at' => $faker->dateTime->format($this->_getDateFormat()),
			'route_track_modified_by' => $faker->numberBetween(1, PHP_INT_MAX),
			'route_track_modified_by_name' => $faker->userName
		);
	}
}