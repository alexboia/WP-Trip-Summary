<?php
/**
 * Copyright (c) 2014-2019 Alexandru Boia
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
    public $parts;

    public $waypoints;

    public $metadata;

    public $maxLat;

    public $maxLng;

    public $minLat;

    public $minLng;

    public $maxAlt;

    public $minAlt;

    public function __construct($metadata) {
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

    public function addTrackPart(Abp01_Route_Track_Part $track) {
        if ($track->minLat < $this->minLat) {
            $this->minLat = $track->minLat;
        }
        if ($track->maxLat > $this->maxLat) {
            $this->maxLat = $track->maxLat;
        }

        if ($track->minLng < $this->minLng) {
            $this->minLng = $track->minLng;
        }
        if ($track->maxLng > $this->maxLng) {
            $this->maxLng = $track->maxLng;
        }

        if ($track->minAlt < $this->minAlt) {
            $this->minAlt = $track->minAlt;
        }
        if ($track->maxAlt > $this->maxAlt) {
            $this->maxAlt = $track->maxAlt;
        }

        if (!is_array($this->parts)) {
            $this->parts = array();
        }

        $this->parts[] = $track;
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

    public function getBounds() {
        $bounds = new Abp01_Route_Track_Bbox($this->minLat,
            $this->minLng,
            $this->maxLat,
            $this->maxLng);
        return $bounds;
    }

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
            ? $trackPoint : null;
    }

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
            ? $trackPoint : null;
    }

    public function serializeDocument() {
        return serialize($this);
    }

    public function toJson() {
        return json_encode($this);
    }
}