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

class Abp01_Route_Track_Part {
	/**
	 * @var Abp01_Route_Track_Line[] Array of track lines for this track part.
	 */
	public $lines;

	/**
	 * @var string The name of the track part
	 */
	public $name;

	public $maxLat;

	public $maxLng;

	public $minLat;

	public $minLng;

	public $minAlt;

	public $maxAlt;

	public function __construct($name = null) {
		$this->lines = array();
		$this->name = $name;

		$this->minAlt = PHP_INT_MAX;
		$this->maxAlt = ~PHP_INT_MAX;

		$this->minLat = PHP_INT_MAX;
		$this->maxLat = ~PHP_INT_MAX;

		$this->minLng = PHP_INT_MAX;
		$this->maxLng = ~PHP_INT_MAX;
	}

	public function addLine(Abp01_Route_Track_Line $line) {
		$this->minLat = min($this->minLat, $line->getMinimumLatitude());
		$this->minLng = min($this->minLng, $line->getMinimumLongitude());

		$this->maxLat = max($this->maxLat, $line->getMaximumLatitude());
		$this->maxLng = max($this->maxLng, $line->getMaximumLongitude());

		$this->minAlt = min($this->minAlt, $line->getMinimumAltitude());
		$this->maxAlt = max($this->maxAlt, $line->getMaximumAltitude());

		if (!is_array($this->lines)) {
			$this->lines = array();
		}

		$this->lines[] = $line;
	}

	public function getLines() {
		return $this->lines;
	}

	public function getLinesCount() {
		return is_array($this->lines) 
			? count($this->lines) 
			: 0;
	}

	public function isEmpty() {
		return $this->getLinesCount() == 0;
	}

	/**
	 * @return Abp01_Route_Track_Bbox
	 */
	public function getBounds() {
		$bounds = new Abp01_Route_Track_Bbox($this->minLat,
			$this->minLng,
			$this->maxLat,
			$this->maxLng);
		return $bounds;
	}

	public function simplify($threshold) {
		$track = new Abp01_Route_Track_Part($this->name);
		foreach ($this->lines as $line) {
			$track->addLine($line->simplify($threshold));
		}
		return $track;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function getMinimumLatitude() {
		return $this->minLat;
	}

	public function getMinimumLongitude() {
		return $this->minLng;
	}

	public function getMaximumLatitude() {
		return $this->maxLat;
	}

	public function getMaximumLongitude() {
		return $this->maxLng;
	}

	public function getMinimumAltitude() {
		return $this->minAlt;
	}

	public function getMaximumAltitude() {
		return $this->maxAlt;
	}
}