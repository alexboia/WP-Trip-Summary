<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

if (!defined('ABP01_LOADED')) {
	exit ;
}

class Abp01_Transfer_TrackFileDownloader {
	private Abp01_Route_Manager $_routeManager;

	private Abp01_Route_Track_FileNameProvider $_trackFileNameProvider;

	public function __construct(Abp01_Route_Manager $routeManager, 
		Abp01_Route_Track_FileNameProvider $trackFileNameProvider) {
		$this->_routeManager = $routeManager;
		$this->_trackFileNameProvider = $trackFileNameProvider;
	}

	public function sendTrackFileForPostId(int|string|null $postId): void {
		if (!$this->_isValidPostId($postId)) {
			throw new InvalidArgumentException('Invalid post identifier given');
		}

		$postId = intval($postId);
		$track = $this->_getTrack($postId);

		$trackFilePath = $this->_trackFileNameProvider->constructTrackFilePath($track);
		$trackFileMimeType = $track->getFileMimeType();

		$this->_sendFileWithMimeType($postId, 
			$trackFilePath, 
			$trackFileMimeType);
	}

	private function _isValidPostId(int|string|null $postId): bool {
		return !empty($postId) && is_numeric($postId) && intval($postId) > 0;
	}

	private function _getTrack(int $postId): ?Abp01_Route_Track {
		return $this->_routeManager
			->getRouteTrack($postId);
	}

	private function _sendFileWithMimeType(int $postId, string $trackFile, string $withMimeType): void {
		$this->_createFileDownloader($postId)
			->sendFileWithMimeType($trackFile, 
				$withMimeType);
	}

	private function _createFileDownloader(int $postId): Abp01_Transfer_FileDownloader {
		$defaultInstance = $this->_createDefaultFileDownloaderInstance();
		return $this->_getFinalFileDownloaderInstance($postId, 
			$defaultInstance);
	}

	private function _createDefaultFileDownloaderInstance(): Abp01_Transfer_FileDownloader {
		return new Abp01_Transfer_FileDownloaderWithScriptTermination(
			new Abp01_Transfer_SimpleFileDownloader()
		);
	}

	private function _getFinalFileDownloaderInstance(int $postId, Abp01_Transfer_FileDownloader $defaultInstance): Abp01_Transfer_FileDownloader {
		/**
		 * Filters the track file download channel for a given post.
		 * Must be a valid Abp01_Transfer_FileDownloader instance.
		 * If an invalid value is provided, then the default downloader is used.
		 * 
		 * @since 0.3.3
		 * @category Trip Summary Management
		 * @unstable Susceptible to breaking changes due to PSR-4 migration
		 * 
		 * @param Abp01_Transfer_FileDownloader $trackFileDownloaderInstance The current track file downloader instance
		 * @param int $postId The current post ID
		 */
		$finalInstance = apply_filters('abp01_get_track_file_downloader', 
			$defaultInstance,
			$postId);

		if (!$this->_isValidFileDownloaderInstance($finalInstance)) {
			$finalInstance = $defaultInstance;
		}

		return $finalInstance;
	}

	private function _isValidFileDownloaderInstance(mixed $instance): bool {
		return $instance instanceof Abp01_Transfer_FileDownloader;
	}
}