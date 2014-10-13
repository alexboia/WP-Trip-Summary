<?php
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