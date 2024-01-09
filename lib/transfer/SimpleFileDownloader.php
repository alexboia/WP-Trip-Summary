<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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
	exit ;
}

class Abp01_Transfer_SimpleFileDownloader implements Abp01_Transfer_FileDownloader {
    public function sendFileWithMimeType($fileToDownload, $withMimeType) {
        if (empty($withMimeType)) {
            throw new InvalidArgumentException('File download mime type must be specified');
        }

        if ($this->_fileExistsAndCanBeRead($fileToDownload)) {
            $this->_sendFile($fileToDownload, $withMimeType);
        } else {
            $this->_send404NotFound();
        }
    }

    private function _fileExistsAndCanBeRead($fileToDownload) {
        return !empty($fileToDownload) 
            && is_readable($fileToDownload);
    }

    private function _sendFile($fileToDownload, $withMimeType) {
        $fileSize = filesize($fileToDownload);
        $fileName = basename($fileToDownload);
    
        abp01_send_header('Content-Type: ' . $withMimeType);
        abp01_send_header('Content-Length: ' . $fileSize);
        abp01_send_header('Content-Disposition: attachment; filename="' . $fileName . '"');
    
        readfile($fileToDownload);
    }

    private function _send404NotFound() {
        abp01_set_http_response_code(404);
    }
}