<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track_Part {
    public $lines;

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
        if ($line->minLat < $this->minLat) {
            $this->minLat = $line->minLat;
        }
        if ($line->maxLat > $this->maxLat) {
            $this->maxLat = $line->maxLat;
        }

        if ($line->minLng < $this->minLng) {
            $this->minLng = $line->minLng;
        }
        if ($line->maxLng > $this->maxLng) {
            $this->maxLng = $line->maxLng;
        }

        if ($line->minAlt < $this->minAlt) {
            $this->minAlt = $line->minAlt;
        }
        if ($line->maxAlt > $this->maxAlt) {
            $this->maxAlt = $line->maxAlt;
        }

        if (!is_array($this->lines)) {
            $this->lines = array();
        }

        $this->lines[] = $line;
    }

    public function simplify($threshold) {
        $track = new Abp01_Route_Track_Part($this->name);
        foreach ($this->lines as $line) {
            $track->addLine($line->simplify($threshold));
        }
        return $track;
    }
}