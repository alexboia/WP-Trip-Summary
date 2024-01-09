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

class Abp01_Route_Track_Document {
	/**
	 * @var Abp01_Route_Track_Part[] Array of track segments
	 */
	public $parts;

	/**
	 * @var Abp01_Route_Track_Point[] Array of waypoints
	 */
	public $waypoints;

	public $metadata;

	public $maxLat;

	public $maxLng;

	public $minLat;

	public $minLng;

	public $maxAlt;

	public $minAlt;

	public function __construct($metadata = null) {
		$this->parts = array();
		$this->waypoints = array();
		$this->metadata = $metadata;

		$this->minLat = PHP_INT_MAX;
		$this->maxLat = ~PHP_INT_MAX;

		$this->minLng = PHP_INT_MAX;
		$this->maxLng = ~PHP_INT_MAX;

		$this->minAlt = PHP_INT_MAX;
		$this->maxAlt = ~PHP_INT_MAX;
	}

	public static function fromSerializedDocument($serialized) {
		if (!$serialized || empty($serialized)) {
			return null;
		}
		return unserialize($serialized);
	}

	public function isEmpty() {
		return empty($this->parts);
	}

	public function setMedata($metadata) {
		$this->metadata = $metadata;
	}

	public function getMetadata() {
		return $this->metadata;
	}

	public function addTrackPart(Abp01_Route_Track_Part $track) {
		$this->minLat = min($this->minLat, $track->minLat);
		$this->maxLat = max($this->maxLat, $track->maxLat);

		$this->minLng = min($this->minLng, $track->minLng);
		$this->maxLng = max($this->maxLng, $track->maxLng);

		$this->minAlt = min($this->minAlt, $track->minAlt);
		$this->maxAlt = max($this->maxAlt, $track->maxAlt);

		if (!is_array($this->parts)) {
			$this->parts = array();
		}

		$this->parts[] = $track;
	}

	public function getTrackParts() {
		return $this->parts;
	}

	public function addWayPoint(Abp01_Route_Track_Point $wpt) {
		if ($wpt->coordinate->lat > $this->maxLat) {
			$this->maxLat = $wpt->coordinate->lat;
		}
		if ($wpt->coordinate->lng > $this->maxLng) {
			$this->maxLng = $wpt->coordinate->lng;
		}
		if ($wpt->coordinate->lat < $this->minLat) {
			$this->minLat = $wpt->coordinate->lat;
		}
		if ($wpt->coordinate->lng < $this->minLng) {
			$this->minLng = $wpt->coordinate->lng;
		}
		if ($wpt->coordinate->alt > $this->maxAlt) {
			$this->maxAlt = $wpt->coordinate->alt;
		}
		if ($wpt->coordinate->alt < $this->minAlt) {
			$this->minAlt = $wpt->coordinate->alt;
		}

		if (!is_array($this->waypoints)) {
			$this->waypoints = array();
		}

		$this->waypoints[] = $wpt;
	}

	public function getWaypoints() {
		return $this->waypoints;
	}

	/**
	 * @return Abp01_Route_Track_Document
	 */
	public function simplify($threshold) {
		$document = new Abp01_Route_Track_Document($this->metadata);

		foreach ($this->waypoints as $wpt) {
			$document->addWayPoint($wpt);
		}
		foreach ($this->parts as $trk) {
			$document->addTrackPart($trk->simplify($threshold));
		}

		return $document;
	}

	/**
	 * @return Abp01_Route_Track_AltitudeProfile
	 */
	public function computeAltitudeProfile($targetSystem, $stepPoints) {
		$distance = 0;
		$lastPoint = null;
		$sampleIndex = 0;
		$profile = array();

		foreach ($this->parts as $part) {
			foreach ($part->lines as $line) {
				foreach ($line->trackPoints as $point) {
					if ($lastPoint != null) {
						$distance += round($point->distanceToPoint($lastPoint), 2);
					}

					if (!is_null($point->coordinate->alt) && $sampleIndex++ % $stepPoints == 0) {
						$altitude = round($point->coordinate->getAltitude(), 2);
						$displayDistance = new Abp01_UnitSystem_Value_Distance($distance);
						$displayAltitude = new Abp01_UnitSystem_Value_Height($altitude);

						$profile[] = array(
							'displayDistance' => $displayDistance
								->convertTo($targetSystem)
								->getValue(),

							'displayAlt' => $displayAltitude
								->convertTo($targetSystem)
								->getValue(),

							'coord' => array(
								'lat' => $point->coordinate->getLatitude(),
								'lng' => $point->coordinate->getLongitude()
							)
						);
					}

					$lastPoint = $point;
				}
			}
		}

		return new Abp01_Route_Track_AltitudeProfile($profile, 
			$targetSystem->getDistanceUnit(), 
			$targetSystem->getHeightUnit(),
			$stepPoints);
	}

	public function computeTotalPointsCount() {
		$total = 0;

		foreach ($this->parts as $part) {
			foreach ($part->lines as $line) {
				$total += count($line->trackPoints);
			}
		}

		return $total;
	}

	/**
	 * @return Abp01_Route_Track_Info
	 */
	public function getDisplayableTrackInfo($targetSystem) {
		$minAlt = new Abp01_UnitSystem_Value_Height($this->minAlt);
		$maxAlt = new Abp01_UnitSystem_Value_Height($this->maxAlt);

		$minAlt = $minAlt->convertTo($targetSystem);
		$maxAlt = $maxAlt->convertTo($targetSystem);

		return new Abp01_Route_Track_Info($minAlt, $maxAlt);
	}

	/**
	 * @return stdClass
	 */
	public function toPlainObject() {
		$data = new stdClass();
		$data->route = $this;
		$data->bounds = $this->getBounds();
		$data->start = $this->getStartPoint();
		$data->end = $this->getEndPoint();
		$data->minAltitude = $this->getMinAlt();
		$data->maxAltitude = $this->getMaxAlt();
		return $data;
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

	public function getMinAlt() {
		return $this->minAlt;
	}

	public function getMaxAlt() {
		return $this->maxAlt;
	}

	/**
	 * @return Abp01_Route_Track_Point|null
	 */
	function getStartPoint() {
		$part = reset($this->parts);
		if (!$part) {
			return null;
		}

		$line = reset($part->lines);
		if (!$line) {
			return null;
		}

		$trackPoint = reset($line->trackPoints);
		return $trackPoint instanceof Abp01_Route_Track_Point
			? $trackPoint 
			: null;
	}

	/**
	 * @return Abp01_Route_Track_Point|null
	 */
	function getEndPoint() {
		$part = end($this->parts);
		reset($this->parts);
		if (!$part) {
			return null;
		}

		$line = end($part->lines);
		reset($part->lines);
		if (!$line) {
			return null;
		}

		$trackPoint = end($line->trackPoints);
		reset($line->trackPoints);
		return $trackPoint instanceof Abp01_Route_Track_Point
			? $trackPoint 
			: null;
	}

	/**
	 * @return string
	 */
	public function serializeDocument() {
		return serialize($this);
	}

	/**
	 * @return string
	 */
	public function toJson() {
		return json_encode($this);
	}
}