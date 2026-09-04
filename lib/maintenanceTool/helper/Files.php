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

use WpTripSummary\Env;

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit ;
}

class Abp01_MaintenanceTool_Helper_Files {
	public const string CLEAR_CACHE_FILES_GLOB_PATTERN = 'track*.cache';

	public const string CLEAR_GPX_TRACK_FILES_GLOB_PATTERN = 'track*.gpx';

	public const string CLEAR_GEOJSON_TRACK_FILES_GLOB_PATTERN = 'track*.geojson';

	private Env $_env;

	public function __construct(Env $env) {
		$this->_env = $env;
	}

	/**
	 * @return string[]
	 */
	public function clearTrackFiles(): array {
		$clearGpxTrackFilesGlobPath = $this->_constructClearGpxTrackFilesGlobPath();
		$removedGpxFiles = $this->_deleteFilesByGlobPattern($clearGpxTrackFilesGlobPath);

		$clearGeojsonTrackFilesGlobPath = $this->_constructClearGeojsonTrackFilesGlobPath();
		$removedGeojsonFiles = $this->_deleteFilesByGlobPattern($clearGeojsonTrackFilesGlobPath);

		$removedFiles = array();
		if (!empty($removedGpxFiles)) {
			$removedFiles = array_merge($removedFiles, $removedGpxFiles);
		}

		if (!empty($removedGeojsonFiles)) {
			$removedFiles = array_merge($removedFiles, $removedGeojsonFiles);
		}

		return $removedFiles;
	}

	private function _constructClearGpxTrackFilesGlobPath(): string {
		$cacheStorageDir = $this->_getTrackStorageDir();
		return wp_normalize_path($cacheStorageDir . '/' . self::CLEAR_GPX_TRACK_FILES_GLOB_PATTERN);
	}

	private function _getTrackStorageDir(): string {
		return $this->_env->getTracksStorageDir();
	}

	private function _constructClearGeojsonTrackFilesGlobPath(): string {
		$cacheStorageDir = $this->_getTrackStorageDir();
		return wp_normalize_path($cacheStorageDir . '/' . self::CLEAR_GEOJSON_TRACK_FILES_GLOB_PATTERN);
	}

	public function clearCacheFiles(): ?array {
		$clearFilesGlobPath = $this->_constructClearCacheFilesGlobPath();
		return $this->_deleteFilesByGlobPattern($clearFilesGlobPath);
	}

	private function _constructClearCacheFilesGlobPath(): string {
		$cacheStorageDir = $this->_getCacheStorageDir();
		return wp_normalize_path($cacheStorageDir . '/' . self::CLEAR_CACHE_FILES_GLOB_PATTERN);
	}

	private function _getCacheStorageDir(): string {
		return $this->_env->getCacheStorageDir();
	}

	private function _deleteFilesByGlobPattern(string $globPathPattern): ?array {
		return abp01_delete_files_by_glob_pattern($globPathPattern);
	}
}