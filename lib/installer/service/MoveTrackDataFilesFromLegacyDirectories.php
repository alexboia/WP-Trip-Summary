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
	exit;
}

class Abp01_Installer_Service_MoveTrackDataFilesFromLegacyDirectories {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var Abp01_Installer_Service_RemoveDirectoryAndContents
	 */
	private $_removeDirectoryAndContentsService;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
		$this->_removeDirectoryAndContentsService = new Abp01_Installer_Service_RemoveDirectoryAndContents();
	}

	public function execute() {
		$result = true;

		$legacyTracksStorageDir = wp_normalize_path(sprintf('%s/storage', 
			$this->_env->getDataDir()));
		$legacyCacheStorageDir = wp_normalize_path(sprintf('%s/cache', 
			$this->_env->getDataDir()));

		//1. Move GPX files
		if (is_dir($legacyTracksStorageDir)) {
			$result = $this->_cleanLegacyStorageDirectory($legacyTracksStorageDir, 
				$this->_env->getTracksStorageDir(), 
				'gpx');
		}

		//2. Move cache files
		if (is_dir($legacyCacheStorageDir)) {
			$result = $this->_cleanLegacyStorageDirectory($legacyCacheStorageDir, 
				$this->_env->getCacheStorageDir(), 
				'cache');
		}

		return $result;
	}

	private function _cleanLegacyStorageDirectory($legacyDirectoryPath, $newDirectoryPath, $searchExtension) {
		$failedCount = 0;
		$moveFiles = glob($legacyDirectoryPath . DIRECTORY_SEPARATOR . '*.' . $searchExtension);

		//Move all files that match the given extension
		if ($moveFiles !== false && !empty($moveFiles)) {
			foreach ($moveFiles as $sourceFilePath) {
				$destinationFilePath = wp_normalize_path(sprintf('%s/%s', 
					$newDirectoryPath,
					basename($sourceFilePath)));
					
				if (!@rename($sourceFilePath, $destinationFilePath)) {
					$failedCount++;
				}
			}
		}

		//If no failures were registered whilst moving the files, 
		//  remove the legacy directory
		if ($failedCount == 0) {
			return $this->_removeDirectoryAndContents($legacyDirectoryPath);
		} else {
			return false;
		}
	}

	private function _removeDirectoryAndContents($directoryPath) {
		return $this->_removeDirectoryAndContentsService
			->excecute($directoryPath);
	}
}