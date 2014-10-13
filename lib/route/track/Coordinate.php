<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track_Coordinate {
    public $lat;

    public $lng;

    public $alt;

    public function __construct($lat, $lng, $alt = 0) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->alt = $alt;
    }
}