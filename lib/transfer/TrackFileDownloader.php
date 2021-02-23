<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class Abp01_Transfer_TrackFileDownloader {
	const GPX_TRACK_FILE_MIME_TYPE = 'application/gpx';

	public function sendTrackFileForPostId($postId) {
		if (!$this->_isValidPostId($postId)) {
			throw new InvalidArgumentException('Invalid post identifier given');
		}

		$trackFile = $this->_getTrackFilePath($postId);
		$withMimeType = $this->_getMimeType($postId);

		$this->_sendFileWithMimeType($trackFile, 
			$withMimeType);
	}

	private function _isValidPostId($postId) {
		return !empty($postId) && is_numeric($postId) && $postId > 0;
	}

	private function _getTrackFilePath($postId) {
		return $this->_getRouteManager()
			->getTrackFilePath($postId);
	}

	private function _getMimeType($postId) {
		return self::GPX_TRACK_FILE_MIME_TYPE;
	}

	private function _sendFileWithMimeType($trackFile, $withMimeType) {
		$this->_createFileDownloader()
			->sendFileWithMimeType($trackFile, $withMimeType);
	}

	private function _createFileDownloader() {
		$defaultInstance = $this->_createDefaultFileDownloaderInstance();
		return $this->_getFinalFileDownloaderInstance($defaultInstance);
	}

	private function _createDefaultFileDownloaderInstance() {
		return new Abp01_Transfer_FileDownloaderWithScriptTermination(
			new Abp01_Transfer_SimpleFileDownloader()
		);
	}

	private function _getFinalFileDownloaderInstance(Abp01_Transfer_FileDownloader $defaultInstance) {
		$finalInstance = apply_filters('abp01_get_track_file_downloader', 
			$defaultInstance);

		if (!$this->_isValidFileDownloaderInstance($finalInstance)) {
			$finalInstance = $defaultInstance;
		}

		return $finalInstance;
	}

	private function _isValidFileDownloaderInstance($instance) {
		return $instance instanceof Abp01_Transfer_FileDownloader;
	}

	private function _getRouteManager() {
		return abp01_get_route_manager();
	}
}