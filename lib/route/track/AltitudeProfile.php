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

class Abp01_Route_Track_AltitudeProfile {
	public $profile;

	public $distanceUnit;

	public $heightUnit;

	public $stepPoints;

	public function __construct(array $profile, $distanceUnit, $heightUnit, $stepPoints) {
		$this->profile = $profile;
		$this->distanceUnit = $distanceUnit;
		$this->heightUnit = $heightUnit;
		$this->stepPoints = $stepPoints;
	}

	/**
	 * @return Abp01_Route_Track_AltitudeProfile|null 
	 */
	public static function fromSerializedDocument($serialized) {
		if (!$serialized || empty($serialized)) {
			return null;
		}
		return unserialize($serialized);
	}

	public function getProfilePoints() {
		return $this->profile;
	}

	public function getProfilePointCount() {
		return count($this->profile);
	}

	public function getStepPoints() {
		return $this->stepPoints;
	}

	public function getDistanceUnit() {
		return $this->distanceUnit;
	}

	public function getHeightUnit() {
		return $this->heightUnit;
	}

	public function hasBeenGeneratedFor(Abp01_UnitSystem $unitSystem, $stepPoints) {
		return $this->distanceUnit === $unitSystem->getDistanceUnit()
			&& $this->heightUnit === $unitSystem->getHeightUnit()
			&& $this->stepPoints == $stepPoints;
	}

	public function toPlainObject() {
		$data = new stdClass();
		$data->profile = &$this->profile;
		$data->distanceUnit = $this->distanceUnit;
		$data->heightUnit = $this->heightUnit;
		return $data;
	}

	public function serializeDocument() {
		return serialize($this);
	}

	public function toJson() {
		return json_encode($this->toPlainObject());
	}
}