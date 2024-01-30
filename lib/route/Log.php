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

class Abp01_Route_Log {
	private $_postId;

	/**
	 * @var Abp01_Route_Log_Entry[]
	 */
	private $_logEntries = array();

	private $_fastestEntryIdx = -1;

	private $_slowestEntryIdx = -1;

	public function __construct($postId, array $entries = array()) {
		$this->_postId = $postId;
		$this->setLogEntries($entries);
	}

	public function addLogEntry(Abp01_Route_Log_Entry $entry) {
		$fastestRun = $this->getFastestRun();
		$slowestRun = $this->getSlowestRun();

		$this->_logEntries[] = $entry;
		$newEntryIndex = count($this->_logEntries) - 1;

		if ($fastestRun != null) {
			if ($entry->fasterThan($fastestRun)) {
				$this->_fastestEntryIdx = $newEntryIndex;
			}
		} else {
			$this->_fastestEntryIdx = $newEntryIndex;
		}

		if ($slowestRun != null) {
			if ($entry->slowerThan($slowestRun)) {
				$this->_slowestEntryIdx = $newEntryIndex;
			}
		} else {
			$this->_slowestEntryIdx = $newEntryIndex;
		}
	}

	public function clearLogEntries() {
		$this->_logEntries = array();
		$this->_slowestEntryIdx = -1;
		$this->_fastestEntryIdx = -1;
	}

	/**
	 * @param Abp01_Route_Log_Entry[] $entries 
	 * @return void 
	 */
	public function setLogEntries(array $entries) {
		$this->clearLogEntries();
		foreach ($entries as $e) {
			$this->addLogEntry($e);
		}
	}

	public function getFastestTime() {
		$fastestRun = $this->getFastestRun();
		return $fastestRun != null 
			? $fastestRun->timeInHours
			: 0;
	}

	/**
	 * @return Abp01_Route_Log_Entry|null 
	 */
	public function getFastestRun() {
		if ($this->_fastestEntryIdx >= 0) {
			return $this->_logEntries[$this->_fastestEntryIdx];
		} else {
			return null;
		}
	}

	public function getSlowestTime() {
		$slowestRun = $this->getSlowestRun();
		return $slowestRun != null
			? $slowestRun->timeInHours
			: 0;
	}

	/**
	 * @return Abp01_Route_Log_Entry|null 
	 */
	public function getSlowestRun() {
		if ($this->_slowestEntryIdx >= 0) {
			return $this->_logEntries[$this->_slowestEntryIdx];
		} else {
			return null;
		}
	}

	public function getPostId() {
		return $this->_postId;
	}

	public function getLogEntries() {
		return $this->_logEntries;
	}

	public function toPlainObject() {
		$data = new stdClass();
		$data->postId = $this->_postId;
		
		$data->logEntries = array();
		foreach ($this->_logEntries as $e) {
			$data->logEntries[] = $e->toPlainObject();
		}

		$slowestRun = $this->getSlowestRun();
		$data->slowestRun = $slowestRun != null
			? $slowestRun->toPlainObject()
			: null;

		$fastestRun = $this->getFastestRun();
		$data->fastestRun = $fastestRun != null
			? $fastestRun->toPlainObject()
			: null;

		return $data;
	}

	public function getLogEntryCount() {
		return count($this->_logEntries);
	}

	public function hasLogEntries() {
		return $this->getLogEntryCount() > 0;
	}
}