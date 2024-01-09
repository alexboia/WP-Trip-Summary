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

class ReadmeChangelogExtractorTests extends WP_UnitTestCase {
	use TestDataFileHelpers;

	public function test_canExtractChangeLogs() {
		foreach ($this->_getTestFilesSpec() as $fileName => $expectedChangeLogVersions) {
			$this->_runChangeLogExtractionTests($fileName, $expectedChangeLogVersions);
		}
	}

	private function _runChangeLogExtractionTests($fileName, $expectedChangeLogVersions) {
		$extractor = new Abp01_ReadmeChangelogExtractor($this->_determineDataFilePath($fileName));
		$changeLog = $extractor->extractChangeLog();

		if (!empty($expectedChangeLogVersions)) {
			$this->assertEquals(count($expectedChangeLogVersions), count($changeLog));
			foreach ($expectedChangeLogVersions as $expectedVersion => $changeLogItemCount) {
				$this->assertArrayHasKey($expectedVersion, $changeLog);
				$this->assertEquals($changeLogItemCount, count($changeLog[$expectedVersion]));
			}
		} else {
			$this->assertEmpty($changeLog);
		}
	}

	private function _getTestFilesSpec() {
		return array(
			'test-readme-full.txt' => array(
				'0.2.6' => 8,
				'0.2.5' => 8,
				'0.2.4' => 8,
				'0.2.3' => 7,
				'0.2.2' => 4,
				'0.2.1' => 2,
				'0.2.0' => 3,
				'0.2b' => 1
			),
			'test-readme-no-changelog.txt' => array(),
			'test-readme-changelog-only.txt' => array(
				'0.2.6' => 8,
				'0.2.5' => 8,
				'0.2.3' => 7,
				'0.2.2' => 4,
				'0.2.1' => 2
			),
			'test-readme-empty-changelog-versions.txt' => array(
				'0.2.6' => 0,
				'0.2.5' => 8,
				'0.2.4' => 0,
				'0.2.3' => 0,
				'0.2.2' => 0,
				'0.2.1' => 0,
				'0.2.0' => 0,
				'0.2b' => 0
			)
		);
	}

	protected static function _getRootTestsDir() {
        return __DIR__;
    }
}