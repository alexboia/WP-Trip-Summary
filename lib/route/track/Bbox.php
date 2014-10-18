<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track_Bbox {
    public $northWest;

    public $southWest;

    public $northEast;

    public $southEast;

    public function  __construct($minLat, $minLng, $maxLat, $maxLng) {
        $this->northWest = new Abp01_Route_Track_Coordinate($maxLat, $minLng);
        $this->southEast = new Abp01_Route_Track_Coordinate($minLat, $maxLng);

        $this->southWest = new Abp01_Route_Track_Coordinate($minLat, $minLng);
        $this->northEast = new Abp01_Route_Track_Coordinate($maxLat, $maxLng);
    }
}