<?php
/**
 * Copyright (c) 2014-2016, Alexandru Boia
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *  - Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

/**
 * Source for the formulas: http://www.movable-type.co.uk/scripts/latlong.html
 * */
class Abp01_Route_Track_Point {
    public $name;

    public $coordinate;

    public $description;

    public function __construct(Abp01_Route_Track_Coordinate $coord) {
        $this->coordinate = $coord;
    }

    public function distanceToLine(Abp01_Route_Track_Point $a, Abp01_Route_Track_Point $b) {
        if ($a == null || $b == null) {
            throw new InvalidArgumentException();
        }

        $c = $this;
        $distanceAC = $c->distanceToPoint($a);
        $bearingAB = $a->bearingToPoint($b);
        $bearingAC = $a->bearingToPoint($c);
        $radius = Abp01_Route_Track_Constants::EARTH_RADIUS;

        $dXT = asin(sin($distanceAC / $radius) * sin($bearingAC - $bearingAB)) * $radius;
        return abs($dXT);
    }

    public function distanceToPoint(Abp01_Route_Track_Point $to) {
        $latFrom2Rad = deg2rad($this->coordinate->lat);
        $latTo2Rad = deg2rad($to->coordinate->lat);

        $deltaLat = deg2rad($to->coordinate->lat -
            $this->coordinate->lat);
        $deltaLng = deg2rad($to->coordinate->lng -
            $this->coordinate->lng);

        $a = pow(sin($deltaLat / 2), 2) + cos($latFrom2Rad) * cos($latTo2Rad)
            * pow(sin($deltaLng / 2), 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $c * Abp01_Route_Track_Constants::EARTH_RADIUS;
    }

    public function bearingToPoint(Abp01_Route_Track_Point $to) {
        $latFrom2Rad = deg2rad($this->coordinate->lat);
        $latTo2Rad = deg2rad($to->coordinate->lat);

        $lngFrom2Rad = deg2rad($this->coordinate->lng);
        $lngTo2Rad = deg2rad($to->coordinate->lng);

        $y = sin($lngTo2Rad - $lngFrom2Rad) * cos($latTo2Rad);
        $x = cos($latFrom2Rad) * sin($latTo2Rad) - sin($latFrom2Rad) * cos($latTo2Rad)
            * cos($lngTo2Rad - $lngFrom2Rad);

        $bearing = atan2($y, $x);
        return fmod(rad2deg($bearing) + 360, 360);
    }
}