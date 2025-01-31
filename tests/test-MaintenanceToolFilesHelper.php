<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class MaintenanceToolFilesHelperTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use RouteTrackPathHelpers;

	public function test_canClearTrackFiles_whenNoFilesPresent() {
		$this->_runClearTrackFilesTests();
	}

	private function _runClearTrackFilesTests() {
		$filesHelper = $this->_getFilesHelper();
		$filesHelper->clearTrackFiles();
		$this->_assertTrackDirEmpty();
	}

	private function _assertTrackDirEmpty() {
		$globGpx = $this->_getTrackDocumentFilePath('*', 'gpx');
		$globGeojson = $this->_getTrackDocumentFilePath('*', 'geojson');
		
		$gpx = glob($globGpx);
		$this->assertEmpty($gpx);

		$geojson = glob($globGeojson);
		$this->assertEmpty($geojson);
	}

	public function test_canClearCacheFiles_whenNoFilesPresent() {
		$this->_runClearCacheFilesTests();
	}

	private function _runClearCacheFilesTests() {
		$filesHelper = $this->_getFilesHelper();
		$filesHelper->clearCacheFiles();
		$this->_assertCacheDirEmpty();
	}

	private function _assertCacheDirEmpty() {
		$globCache = $this->_getCachedTrackDocumentFilePath('*');
		$cache = glob($globCache);
		$this->assertEmpty($cache);
	}

	public function test_canClearTrackFiles_whenFilesPresent() {
		$this->_createDummyTrackFiles(5);
		$this->_runClearTrackFilesTests();
	}

	private function _createDummyTrackFiles($count) {
		$faker = $this->_getFaker();
		$postId = $faker->numberBetween(1, 1000);

		for ($i = 0; $i < $count; $i ++) {
			$gpxFilePath = $this->_getTrackDocumentFilePath($postId, 'gpx');
			$geojsonFilePath = $this->_getTrackDocumentFilePath($postId, 'gpx');

			file_put_contents($gpxFilePath, $faker->text());
			file_put_contents($geojsonFilePath, $faker->text());

			$postId ++;
		}
	}

	public function test_canClearCacheFiles_whenFilesPresent() {
		$this->_createDummyCacheFiles(5);
		$this->_runClearCacheFilesTests();
	}

	private function _createDummyCacheFiles($count) {
		$faker = $this->_getFaker();
		$postId = $faker->numberBetween(1, 1000);

		for ($i = 0; $i < $count; $i ++) {
			$cacheFilePath = $this->_getCachedTrackDocumentFilePath($postId);
			file_put_contents($cacheFilePath, $faker->text());
			$postId ++;
		}
	}

	private function _getFilesHelper() {
		return new Abp01_MaintenanceTool_Helper_Files($this->_getEnv());
	}
}