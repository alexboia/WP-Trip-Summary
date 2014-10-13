<?php
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