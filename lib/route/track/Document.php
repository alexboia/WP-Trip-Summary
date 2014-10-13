<?php
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
        $bounds = new stdClass();
        $bounds->minLat = $this->minLat;
        $bounds->minLng = $this->minLng;
        $bounds->maxLat = $this->maxLat;
        $bounds->maxLng = $this->maxLng;
        $bounds->minAlt = $this->minAlt;
        $bounds->maxAlt = $this->maxAlt;
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