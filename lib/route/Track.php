<?php
class Abp01_Route_Track {
    private $_bounds;

    private $_file;

    public function __construct($file, $bounds) {
        if (empty($file)) {
            throw new InvalidArgumentException();
        }
        if ($bounds == null) {
            $this->_bounds = $bounds;
        }

        $this->_file = $file;
        $this->_bounds = $bounds;
    }

    public function getBounds() {
        return $this->_bounds;
    }

    public function getFile() {
        return $this->_file;
    }
}