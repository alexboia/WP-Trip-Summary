<?php
class Abp01_Validate_GpxDocument {
    private $_bufferLength;

    public function __construct($bufferLength = 50) {
        if ($bufferLength <= 0) {
            throw new InvalidArgumentException('Buffer length must be greater than 0');
        }
        $this->_bufferLength = $bufferLength;
    }

    public function validate($filePath) {
        if (!is_readable($filePath)) {
            return false;
        }
        $fp = fopen($filePath, 'rb');
        if (!$fp) {
            return false;
        }

        $buffer = fread($fp, $this->_bufferLength);
        if (!$buffer) {
            return false;
        }

        if (function_exists('mb_stripos')) {
            $xmlPos = mb_stripos($buffer, '<?xml', 0, 'UTF-8');
            $gpxMarkerPos = mb_stripos($buffer, '<gpx', 0, 'UTF-8');
        } else {
            $xmlPos = stripos($buffer, '<?xml', 0, 'UTF-8');
            $gpxMarkerPos = stripos($buffer, '<gpx', 0, 'UTF-8');
        }

        return ($xmlPos === 0 || $xmlPos === 1) && $gpxMarkerPos > 0;
    }
}