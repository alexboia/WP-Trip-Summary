<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

class Abp01_Route_Track_Line {
    public $minLat;

    public $minLng;

    public $maxLat;

    public $maxLng;

    public $maxAlt;

    public $minAlt;

    /**
     * @var Abp01_Route_Track_Point[] Array of track points for this line
     */
    public $trackPoints;

    public function __construct() {
        $this->minLat = PHP_INT_MAX;
        $this->maxLat = ~PHP_INT_MAX;

        $this->minLng = PHP_INT_MAX;
        $this->maxLng = ~PHP_INT_MAX;
        ;
        $this->minAlt = PHP_INT_MAX;
        $this->maxAlt = ~PHP_INT_MAX;

        $this->trackPoints = array();
    }

    public function addPoint(Abp01_Route_Track_Point $point) {

        if ($point->coordinate->lat > $this->maxLat) {
            $this->maxLat = $point->coordinate->lat;
        }
        if ($point->coordinate->lng > $this->maxLng) {
            $this->maxLng = $point->coordinate->lng;
        }

        if ($point->coordinate->lat < $this->minLat) {
            $this->minLat = $point->coordinate->lat;
        }
        if ($point->coordinate->lng < $this->minLng) {
            $this->minLng = $point->coordinate->lng;
        }

        if ($point->coordinate->alt > $this->maxAlt) {
            $this->maxAlt = $point->coordinate->alt;
        }
        if ($point->coordinate->alt < $this->minAlt) {
            $this->minAlt = $point->coordinate->alt;
        }

        if (!is_array($this->trackPoints)) {
            $this->trackPoints = array();
        }

        $this->trackPoints[] = $point;
    }

    public function simplify($threshold) {
        $line = new Abp01_Route_Track_Line();
        foreach ($this->_runDouglasPeucker($this->trackPoints, $threshold) as $p) {
            $line->addPoint($p);
        }
        return $line;
    }

    private function _runDouglasPeucker(array $pointList, $threshold) {
        $length = count($pointList);

        if ($length <= 2) {
            return $pointList;
        }

        $iMax = 0;
        $dMax = 0;

        $first = isset($pointList[0]) ? $pointList[0] : null;
        $last = isset($pointList[$length - 1]) ? $pointList[$length - 1] : 0;

        for ($k = 1; $k < ($length - 1); $k ++) {
            $d = $pointList[$k]->distanceToLine($first, $last);
            if ($d > $dMax) {
                $dMax = $d;
                $iMax = $k;
            }
        }

        if ($dMax > $threshold) {
            $rFirstMax = $this->_runDouglasPeucker(array_slice($pointList, 0, $iMax + 1), $threshold);
            $rMaxLast = $this->_runDouglasPeucker(array_slice($pointList, $iMax), $threshold);
            return array_merge(array_slice($rFirstMax, 0, count($rFirstMax) - 1), $rMaxLast);
        } else {
            return array($first, $last);
        }
    }
}