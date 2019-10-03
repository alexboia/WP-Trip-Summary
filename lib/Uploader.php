<?php
/**
 * Copyright (c) 2014-2019 Alexandru Boia
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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Uploader {
    const UPLOAD_OK = 0;

    const UPLOAD_INVALID_MIME_TYPE = 1;

    const UPLOAD_TOO_LARGE = 2;

    const UPLOAD_NO_FILE = 3;

    const UPLOAD_INTERNAL_ERROR = 4;

    const UPLOAD_STORE_FAILED = 5;

    const UPLOAD_NOT_VALID = 6;

    private $_destinationPath = null;

    private $_maxFileSize = 0;

    private $_chunkSize = 0;

    private $_allowedFileTypes = array();

    private $_key = null;

    private $_customValidator = null;

    private $_isReady = false;

    private $_chunk = 0;

    private $_chunks = 0;

    public function __construct($key, $destinationPath, array $config = array()) {
        if (empty($key)) {
            throw new InvalidArgumentException();
        }
        if (empty($destinationPath) || !is_dir(dirname($destinationPath))) {
            throw new InvalidArgumentException();
        }

        $this->_destinationPath = $destinationPath;
        $this->_key = $key;

        if (isset($config['maxFileSize'])) {
            $this->_maxFileSize = max(0, intval($config['maxFileSize']));
        }
        if (isset($config['chunkSize'])) {
            if ($config['chunkSize'] > 0) {
                if (!isset($config['chunk']) || !isset($config['chunks'])) {
                    throw new InvalidArgumentException();
                }
            }
            $this->_chunk = max(0, intval($config['chunk']));
            $this->_chunks = max(0, intval($config['chunks']));
            $this->_chunkSize = max(0, intval($config['chunkSize']));
        }
        if (isset($config['allowedFileTypes']) && is_array($config['allowedFileTypes'])) {
            $this->_allowedFileTypes = $config['allowedFileTypes'];
        }
    }

    public function hasFileUploaded() {
        return isset($_FILES[$this->_key]) && is_array($_FILES[$this->_key]) &&
            !empty($_FILES[$this->_key]['size']) &&
            !empty($_FILES[$this->_key]['tmp_name']);
    }

    public function receive() {
        if (!$this->hasFileUploaded()) {
            return self::UPLOAD_NO_FILE;
        }

        if ($this->_chunkSize > 0 && $this->_chunk > 0 && !file_exists($this->_destinationPath)) {
            return self::UPLOAD_INTERNAL_ERROR;
        }

        if (($this->_chunkSize == 0 || $this->_chunk == 0) && file_exists($this->_destinationPath)) {
            @unlink($this->_destinationPath);
        }

        $temp = $this->_getTmpFilePath();
        if (!is_uploaded_file($temp)) {
            return self::UPLOAD_NO_FILE;
        }

        if (!$this->_isFileSizeValid()) {
            return self::UPLOAD_TOO_LARGE;
        }

        if (!$this->_isTypeValid()) {
            return self::UPLOAD_INVALID_MIME_TYPE;
        }

        $out = @fopen($this->_destinationPath, $this->_chunkSize > 0 ? 'ab' : 'wb');
        if (!$out) {
            return self::UPLOAD_INTERNAL_ERROR;
        }

        $in = @fopen($temp, 'rb');
        if (!$in) {
            @fclose($out);
            return self::UPLOAD_INTERNAL_ERROR;
        }

        while ($buffer = fread($in, 4096)) {
            fwrite($out, $buffer);
        }

        @fclose($in);
        @fclose($out);

        $isReady = $this->_chunkSize == 0 || ($this->_chunk + 1 >= $this->_chunks);
        if ($isReady && !$this->_passesCustomValidator()) {
            @unlink($this->_destinationPath);
            return self::UPLOAD_NOT_VALID;
        }

        $this->_isReady = $isReady;
        return self::UPLOAD_OK;
    }

    public function isReady() {
        return $this->_isReady;
    }

    public function setCustomValidator($customValidator) {
        if (!empty($customValidator) && !is_callable($customValidator)) {
            throw new InvalidArgumentException('The custom validator must be a valid callable');
        }
        $this->_customValidator = !empty($customValidator) ? $customValidator : null;
    }

    private function _isTypeValid() {
        if (count($this->_allowedFileTypes) == 0) {
            return true;
        }

        $file = null;
        if ($this->_chunkSize == 0 || !file_exists($this->_destinationPath)) {
            $file = $this->_getTmpFilePath();
        } else {
            $file = $this->_destinationPath;
        }

        $sniffer = new MimeReader($file);
        $detectedType = $sniffer->getType();

        if (empty($detectedType) || !in_array($detectedType, $this->_allowedFileTypes)) {
            return false;
        }

        return true;
    }

    private function _isFileSizeValid() {
        if ($this->_maxFileSize == 0) {
            return true;
        }
        return $this->_calculateFileSize() <= $this->_maxFileSize;
    }

    private function _calculateFileSize() {
        $size = filesize($this->_getTmpFilePath());
        if ($this->_chunkSize == 0) {
            if (is_file($this->_destinationPath)) {
                $size += filesize($this->_destinationPath);
            }
        }
        return $size;
    }

    private function _passesCustomValidator() {
        if (is_callable($this->_customValidator)) {
            return call_user_func($this->_customValidator, $this->_destinationPath);
        }
        return true;
    }

    private function _getTmpFilePath() {
        return $_FILES[$this->_key]['tmp_name'];
    }
}