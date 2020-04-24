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

/**
 * Source for the formulas: http://www.movable-type.co.uk/scripts/latlong.html
 * Also see: http://www.edwilliams.org/ftp/avsig/avform.txt
 * */
class Abp01_Route_Track_Point {
    public $name;

    public $coordinate;

    public $description;

    public function __construct(Abp01_Route_Track_Coordinate $coord) {
        $this->coordinate = $coord;
    }

    private function _distanceToPointMeters(Abp01_Route_Track_Point $to) {
        $latFrom2Rad = deg2rad($this->coordinate->lat);
        $latTo2Rad = deg2rad($to->coordinate->lat);

        $deltaLat = deg2rad($to->coordinate->lat -
            $this->coordinate->lat);
        $deltaLng = deg2rad($to->coordinate->lng -
            $this->coordinate->lng);

        $a = pow(sin($deltaLat / 2), 2) + cos($latFrom2Rad) * cos($latTo2Rad)
            * pow(sin($deltaLng / 2), 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $c * Abp01_Route_Track_Constants::EARTH_RADIUS_METERS;
    }

    public function distanceToLine(Abp01_Route_Track_Point $a, Abp01_Route_Track_Point $b) {
        if ($a == null || $b == null) {
            throw new InvalidArgumentException('Line endpoints cannot be null');
        }

        $c = $this;
        $radius = Abp01_Route_Track_Constants::EARTH_RADIUS_METERS;

        $bearingAB = deg2rad($a->bearingToPoint($b));
        $bearingAC = deg2rad($a->bearingToPoint($c));
        $distanceAC = $a->_distanceToPointMeters($c) / $radius;

        $dXT = asin(sin($distanceAC) * sin($bearingAC - $bearingAB)) * $radius;

        return round(abs($dXT) / 1000, 2);
    }

    public function distanceToPoint(Abp01_Route_Track_Point $to) {
        return round($this->_distanceToPointMeters($to) / 1000, 2);
    }

    public function bearingToPoint(Abp01_Route_Track_Point $to) {
        if ($to == null) {
            throw new InvalidArgumentException('Destination point cannot be null');
        }

        $latFrom2Rad = deg2rad($this->coordinate->lat);
        $latTo2Rad = deg2rad($to->coordinate->lat);

        $deltaLng2Rad = deg2rad($to->coordinate->lng - $this->coordinate->lng);

        $x = cos($latFrom2Rad) * sin($latTo2Rad) - 
            sin($latFrom2Rad) * cos($latTo2Rad) * cos($deltaLng2Rad);

        $y = sin($deltaLng2Rad) * cos($latTo2Rad);

        $bearing = atan2($y, $x);

        return fmod(rad2deg($bearing) + 360, 360);
    }
}