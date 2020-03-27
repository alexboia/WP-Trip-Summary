<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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
        $fp = @fopen($filePath, 'rb');
        if (!$fp) {
            return false;
        }

        $buffer = @fread($fp, $this->_bufferLength);
        if (!$buffer) {
            return false;
        }

        if (function_exists('mb_stripos')) {
            $xmlPos = mb_stripos($buffer, '<?xml', 0, 'UTF-8');
            $gpxMarkerPos = mb_stripos($buffer, '<gpx', 0, 'UTF-8');
        } else {
            $xmlPos = stripos($buffer, '<?xml');
            $gpxMarkerPos = stripos($buffer, '<gpx');
        }

        @fclose($fp);
        return ($xmlPos >= 0 || $xmlPos <= 5)
            && $gpxMarkerPos > 0;
    }
}