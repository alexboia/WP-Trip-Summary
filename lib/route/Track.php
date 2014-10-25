<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track {
    private $_bounds;

    private $_file;

    public $maxAlt;

    public $minAlt;

    public function __construct($file, Abp01_Route_Track_Bbox $bounds, $minAlt = 0, $maxAlt = 0) {
        if (empty($file)) {
            throw new InvalidArgumentException();
        }
        if ($bounds == null) {
            $this->_bounds = $bounds;
        }

        $this->_file = $file;
        $this->_bounds = $bounds;

        $this->minAlt = $minAlt;
        $this->maxAlt = $maxAlt;
    }

    public function getBounds() {
        return $this->_bounds;
    }

    public function getFile() {
        return $this->_file;
    }
}