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
	public $date;

	public $vehicle;

	public $gear;

	public $notes;

	public $timeInMinutes;

	public $identity;

	public function __construct($date, 
			$vehicle, 
			$gear, 
			$notes, 
			$timeInMinutes, 
			$identity) {
		$this->date = $date;
		$this->vehicle = $vehicle;
		$this->gear = $gear;
		$this->notes = $notes;
		$this->timeInMinutes = $timeInMinutes;
		$this->identity = $identity;
	}

	public static function fromArray(array $data) {
		if (empty($data)) {
			return null;
		}

		return new self(
			isset($data['date']) ? $data['date'] : null,
			isset($data['vehicle']) ? $data['vehicle'] : null,
			isset($data['gear']) ? $data['gear'] : null,
			isset($data['notes']) ? $data['notes'] : null,
			isset($data['timeInMinutes']) ? $data['timeInMinutes'] : 0,
			isset($data['identity']) ? $data['identity'] : null
		);
	}

	public function fasterThan(Abp01_Route_Log_Entry $entry) {
		return $this->timeInMinutes < $entry->timeInMinutes;
	}

	public function slowerThan(Abp01_Route_Log_Entry $entry) {
		return $this->timeInMinutes > $entry->timeInMinutes;
	}

	public function toPlainObject() {
		$data = new stdClass();
		$data->date = $this->date;
		$data->vehicle = $this->vehicle;
		$data->gear = $this->gear;
		$data->notes = $this->notes;
		$data->timeInMinutes = $this->timeInMinutes;
		$data->identity = $this->identity;
		return $data;
	}

	public function toArray() {
		return (array)$this->toPlainObject();
	}
}