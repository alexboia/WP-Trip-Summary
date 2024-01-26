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

class Abp01_Route_Log_Entry {
	public $id;

	public $postId;
	
	public $date;

	public $vehicle;

	public $gear;

	public $notes;

	public $timeInHours;

	public $rider;

	public $dateCreated;

	public $dateUpdated;

	public $isPublic;

	public $createdBy;

	public $createdByUserDesc;

	public $lastUpdatedBy;

	public $lastUpdatedByUserDesc;

	/**
	 * @param array $data 
	 * @return null|Abp01_Route_Log_Entry 
	 */
	public static function fromArray(array $data) {
		if (empty($data)) {
			return null;
		}

		$logEntry = new self();
		$logEntry->id = isset($data['id']) ? $data['id'] : 0;
		$logEntry->postId = isset($data['postId']) ? $data['postId'] : 0;
		$logEntry->date = isset($data['date']) ? $data['date'] : null;
		$logEntry->vehicle = isset($data['vehicle']) ? $data['vehicle'] : null;
		$logEntry->gear = isset($data['gear']) ? $data['gear'] : null;
		$logEntry->notes = isset($data['notes']) ? $data['notes'] : null;
		$logEntry->timeInHours = isset($data['timeInHours']) ? intval($data['timeInHours']) : 0;
		$logEntry->rider = isset($data['rider']) ? $data['rider'] : null;
		$logEntry->dateCreated = isset($data['dateCreated']) ? $data['dateCreated'] : null;
		$logEntry->dateUpdated = isset($data['dateUpdated']) ? $data['dateUpdated'] : null;
		$logEntry->createdBy = isset($data['createdBy']) ? intval($data['createdBy']) : 0;
		$logEntry->createdByUserDesc = isset($data['createdByUserDesc']) ? $data['createdByUserDesc'] : null;
		$logEntry->lastUpdatedByUserDesc = isset($data['lastUpdatedByUserDesc']) ? $data['lastUpdatedByUserDesc'] : null;
		$logEntry->lastUpdatedBy = isset($data['lastUpdatedBy']) ? intval($data['lastUpdatedBy']) : 0;
		$logEntry->isPublic = isset($data['isPublic']) ? $data['isPublic'] === true : false;

		return $logEntry;
	}

	/**
	 * @param array $data 
	 * @return null|Abp01_Route_Log_Entry 
	 */
	public static function fromDbArray(array $data) {
		if (empty($data)) {
			return null;
		}

		$logEntry = new self();
		$logEntry->id = isset($data['log_ID']) ? $data['log_ID'] : 0;
		$logEntry->postId = isset($data['log_post_ID']) ? $data['log_post_ID'] : 0;
		$logEntry->date = isset($data['log_date']) ? $data['log_date'] : null;
		$logEntry->vehicle = isset($data['log_vehicle']) ? $data['log_vehicle'] : null;
		$logEntry->gear = isset($data['log_gear']) ? $data['log_gear'] : null;
		$logEntry->notes = isset($data['log_notes']) ? $data['log_notes'] : null;
		$logEntry->timeInHours = isset($data['log_duration_hours']) ? intval($data['log_duration_hours']) : 0;
		$logEntry->rider = isset($data['log_rider']) ? $data['log_rider'] : null;
		$logEntry->dateCreated = isset($data['log_created_by']) ? $data['log_created_by'] : null;
		$logEntry->dateUpdated = isset($data['log_updated_by']) ? $data['log_updated_by'] : null;
		$logEntry->createdBy = isset($data['log_created_by']) ? intval($data['log_created_by']) : 0;
		$logEntry->lastUpdatedBy = isset($data['log_updated_by']) ? intval($data['log_updated_by']) : 0;
		$logEntry->createdByUserDesc = isset($data['log_created_by_user_desc']) ? $data['log_created_by_user_desc'] : null;
		$logEntry->lastUpdatedByUserDesc = isset($data['log_updated_by_user_desc']) ? $data['log_updated_by_user_desc'] : null;
		$logEntry->isPublic = isset($data['log_is_public']) ? intval($data['log_is_public']) === 1 : false;

		return $logEntry;
	}

	public function fasterThan(Abp01_Route_Log_Entry $entry) {
		return $this->timeInHours < $entry->timeInHours;
	}

	public function slowerThan(Abp01_Route_Log_Entry $entry) {
		return $this->timeInHours > $entry->timeInHours;
	}

	public function toPlainObject() {
		$data = new stdClass();
		$data->id = $this->id;
		$data->postId = $this->postId;
		$data->date = $this->date;
		$data->vehicle = $this->vehicle;
		$data->gear = $this->gear;
		$data->notes = $this->notes;
		$data->timeInHours = $this->timeInHours;
		$data->rider = $this->rider;
		$data->isPublic = $this->isPublic;
		$data->dateCreated = $this->dateCreated;
		$data->dateUpdated = $this->dateUpdated;
		$data->createdBy = $this->createdBy;
		$data->lastUpdatedBy = $this->lastUpdatedBy;
		return $data;
	}

	public function toArray() {
		return (array)$this->toPlainObject();
	}

	public function toDbArray() {
		return array(
			'log_ID' => $this->id > 0 ? $this->id : null,
			'log_post_ID' => $this->postId,
			'log_rider' => $this->rider,
			'log_date' => $this->date,
			'log_vehicle' => $this->vehicle,
			'log_gear' => $this->gear,
			'log_notes' => $this->notes,
			'log_duration_hours' => $this->timeInHours,
			'log_is_public' => $this->isPublic ? 1 : 0,
			'log_created_by' => $this->createdBy,
			'log_updated_by' => $this->lastUpdatedBy
		);
	}
}