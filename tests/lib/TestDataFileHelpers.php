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

 trait TestDataFileHelpers {
	protected static function _ensureActualPluginDirectoriesAndAssetsCreated() {
		$env = Abp01_Env::getInstance();
		$directoriesService = new Abp01_Installer_Service_CreateStorageDirectories($env->getRootStorageDir(), 
			$env->getTracksStorageDir(), 
			$env->getCacheStorageDir(),
			$env->getLogStorageDir());

		$directoriesService->execute();

		$securityAssetsService = new Abp01_Installer_Service_CreateStorageDirsSecurityAssets($env->getRootStorageDir(), 
			$env->getTracksStorageDir(), 
			$env->getCacheStorageDir());

		$securityAssetsService->execute();
	}

	protected static function _ensurePluginTestDirectoriesCreated() {
		list($rootStorageDir, $tracksStorageDir, $cacheStorageDir) = 
			self::_getTestPluginStorageDirectories();

		if (!is_dir($rootStorageDir)) {
			mkdir($rootStorageDir);
		}

		if (!is_dir($tracksStorageDir)) {
			mkdir($tracksStorageDir);
		}

		if (!is_dir($cacheStorageDir)) {
			mkdir($cacheStorageDir);
		}
	}

	protected static function _ensurePluginTestDirectoriesRemoved() {
		list($rootStorageDir, $tracksStorageDir, $cacheStorageDir) = 
			self::_getTestPluginStorageDirectories();

		if (is_dir($cacheStorageDir)) {
			self::_removeFiles($cacheStorageDir);
			@rmdir($cacheStorageDir);
		}

		if (is_dir($tracksStorageDir)) {
			self::_removeFiles($tracksStorageDir);
			@rmdir($tracksStorageDir);
		}

		if (is_dir($rootStorageDir)) {
			self::_removeFiles($rootStorageDir);
			@rmdir($rootStorageDir);
		}
	}

	protected static function _removeFiles($directory) {
		$files = scandir($directory);
		if (!empty($files)) {
			foreach ($files as $f) {
				if ($f != '.' && $f != '..') {
					$path = $directory . '/' . $f;
					if (is_file($path)) {
						@unlink($path);
					} else {
						self::_removeFiles($path);
					}
				}
			}
		}
	}

	protected static function _getTestPluginStorageDirectories() {
		$rootTestDataDir = self::_determineTestDataDir();
		$rootStorageDir = $rootTestDataDir . '/storage';
		$tracksStorageDir = $rootStorageDir . '/tracks';
		$cacheStorageDir = $rootStorageDir . '/cache';
		$logStorageDir = $rootStorageDir . '/logs';

		return array($rootStorageDir, 
			$tracksStorageDir, 
			$cacheStorageDir,
			$logStorageDir);
	}

	protected static function _getActualPluginStorageDirectories() {
		$env = Abp01_Env::getInstance();
		return array(
			$env->getRootStorageDir(), 
			$env->getTracksStorageDir(), 
			$env->getCacheStorageDir()
		);
	}

	protected static function _deleteAllDataFiles($fileNames) {
		foreach ($fileNames as $fileName) {
			self::_deleteDataFile($fileName);
		}
	}

	protected static function _deleteDataFile($fileName) {
		unlink(self::_determineDataFilePath($fileName));
	}

	protected static function _writeTestDataFileContents($fileName, $contents) {
		file_put_contents(self::_determineDataFilePath($fileName), $contents);
	}
	
	protected static function _readTestDataFileContents($fileName) {
		return file_get_contents(self::_determineDataFilePath($fileName));
	}

	protected static function _determineDataFilePath($fileName) {
		return wp_normalize_path(self::_determineTestDataDir() . '/' . $fileName);
	}

	protected static function _determineTestDataDir() {
		return wp_normalize_path(self::_getRootTestsDir() . '/' . 'assets');
	}

	protected abstract static function _getRootTestsDir();
 }