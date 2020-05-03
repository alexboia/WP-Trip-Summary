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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Uploader {
    /**
     * No error while uploading and validating the file.
     * 
     * @var int
     */
    const UPLOAD_OK = 0;

    /**
     * File doesn't have a valid mime type.
     * 
     * @var int
     */
    const UPLOAD_INVALID_MIME_TYPE = 0x01;

    /**
     * File is larger than the given maximum size.0
     * 
     * @var int
     */
    const UPLOAD_TOO_LARGE = 2;

    /**
     * No file has been uploaded or uploaded file not found
     * 
     * @var int
     */
    const UPLOAD_NO_FILE = 3;

    /**
     * Some internal error occure while processing the uploaded file
     * 
     * @var int
     */
    const UPLOAD_INTERNAL_ERROR = 4;

    /**
     * Could not store the uploaded file
     * 
     * @var int
     */
    const UPLOAD_STORE_FAILED = 5;

    /**
     * The uploaded file did not pass the custom validation routine
     * 
     * @var int
     */
    const UPLOAD_NOT_VALID = 6;

    /**
     * There was an error while preparing to store the file
     * 
     * @var int
     */
    const UPLOAD_STORE_INITIALIZATION_FAILED = 7;

    /**
     * There was a problem with the additional parameters that 
     *  must be present with the uploaded file
     * 
     * @var int
     */
    const UPLOAD_INVALID_UPLOAD_PARAMS = 8;

    /**
     * Altough no error occured while uploading and processing the file
     *  the resulting destination file could not be found or read
     * 
     * @var int
     */
    const UPLOAD_DESTINATION_FILE_NOT_FOUND = 9;

    /**
     * Altough no error occured while uploading and processing the file
     *  the resulting destination file is corrupted
     * 
     * @var int
     */
    const UPLOAD_DESTINATION_FILE_CORRUPT = 10;

    /**
     * The destination file path where the uploaded file is stored
     * 
     * @var string
     */
    private $_destinationPath = null;

    /**
     * The maximum allowed size for the uploaded file, in bytes
     * 
     * @var int
     */
    private $_maxFileSize = 0;

    /**
     * The maximum size of the file chunk, 
     * if chunked upload is enabled for the file
     * 
     * @var int
     */
    private $_chunkSize = 0;

    /**
     * The allowed file mime types
     * 
     * @var string[]
     */
    private $_allowedFileTypes = array();

    /**
     * The key that represents the uploaded file in the _FILES superglobal array
     * 
     * @var string
     */
    private $_key = null;

    /**
     * The custom validation routine handler, called after 
     *  the built-in validation takes place and only after 
     * the entire file has been uploaded.
     * 
     * @var callable
     */
    private $_customValidator = null;

    /**
     * Whether or not the entire file has been completely 
     *  and successfully uploaded
     * 
     * @var boolean
     */
    private $_isReady = false;

    /**
     * The detected mime type
     * 
     * @var string
     */
    private $_detectedType = null;

    /**
     * The file chunk currently being processed
     * 
     * @var int
     */
    private $_chunk = 0;

    /**
     * The number of chunks that the file has been split into
     * 
     * @var int
     */
    private $_chunks = 0;

    public function __construct($key, $destinationPath, array $config = array()) {
        if (empty($key)) {
            throw new InvalidArgumentException('No file key has been specified');
        }

        if (empty($destinationPath) || !is_dir(dirname($destinationPath))) {
            throw new InvalidArgumentException('No destination path was specified');
        }

        $this->_destinationPath = $destinationPath;
        $this->_key = $key;

        if (isset($config['maxFileSize'])) {
            $this->_maxFileSize = max(0, intval($config['maxFileSize']));
        }

        if (isset($config['chunkSize'])) {
            if ($config['chunkSize'] > 0) {
                if (!isset($config['chunk']) || !isset($config['chunks'])) {
                    throw new InvalidArgumentException('The chunkSize option is set, but at least one of the chunks or chunk option is missing.');
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
        $this->_detectedType = null;

        if (!$this->hasFileUploaded()) {
            write_log('File upload failed because no file has been uploaded.');
            return self::UPLOAD_NO_FILE;
        }

        if ($this->_chunkSize > 0 && $this->_chunk > 0 && !file_exists($this->_destinationPath)) {
            write_log('File upload failed because chunk configuration is not valid.');
            return self::UPLOAD_INVALID_UPLOAD_PARAMS;
        }

        if (($this->_chunkSize == 0 || $this->_chunk == 0) && file_exists($this->_destinationPath)) {
            write_log('Chunk size=0 or first chunk - remove existing destination file.');
            @unlink($this->_destinationPath);
        }

        $temp = $this->_getTmpFilePath();
        if (!is_uploaded_file($temp)) {
            write_log('File upload failed because temporary location does not contain a safe source file.');
            return self::UPLOAD_NO_FILE;
        }

        if (!$this->_isFileSizeValid()) {
            write_log('File upload failed because uploaded file is too large.');
            return self::UPLOAD_TOO_LARGE;
        }

        if (!$this->_detectTypeAndValidate()) {
            write_log('File upload failed because uploaded file does not have a valid mime type: "' . $this->_detectedType . '".');
            return self::UPLOAD_INVALID_MIME_TYPE;
        }

        $out = @fopen($this->_destinationPath, $this->_chunkSize > 0 ? 'ab' : 'wb');
        if (!$out) {
            write_log('File upload failed because destination file could not be open.');
            return self::UPLOAD_STORE_INITIALIZATION_FAILED;
        }

        $in = @fopen($temp, 'rb');
        if (!$in) {
            @fclose($out);
            write_log('File upload failed because source file could not be open.');
            return self::UPLOAD_STORE_INITIALIZATION_FAILED;
        }

        while ($buffer = fread($in, 4096)) {
            fwrite($out, $buffer);
        }

        @fclose($in);
        @fclose($out);

        $isReady = $this->_chunkSize == 0 || ($this->_chunk + 1 >= $this->_chunks);
        if ($isReady && !$this->_passesCustomValidator()) {
            @unlink($this->_destinationPath);
            write_log('File upload failed because source file did not pass custom validation.');
            return self::UPLOAD_NOT_VALID;
        } else {
            write_log('The file upload has been successfully processed and completed.');
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

    private function _detectTypeAndValidate() {
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
        $this->_detectedType = $sniffer->getType();

        if (empty($this->_detectedType) || !in_array($this->_detectedType, $this->_allowedFileTypes)) {
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
        if ($this->_chunkSize > 0) {
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