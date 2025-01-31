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

class AssertExpectedLookupDataInstalled extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function check() {
		$env = $this->_getEnv();
		$db = $env->getDb();

		foreach (ExpectedLookupData::getLookupDataToCheck() as $category => $expectedData) {
			$db->where('lookup_category', $category);
			$dbData = $db->get($env->getLookupTableName());

			if (!empty($dbData)) {
				$this->assertEquals(count($expectedData), 
					count($dbData));

				foreach ($dbData as $row) {
					$id  = intval($row['ID']);
					$actualDefaultLabel = $row['lookup_label'];

					$actualTranslations = $this->_getTranslations($id);

					$this->_assertExpectedDataContains($expectedData, 
						$actualDefaultLabel, 
						$actualTranslations);
				}
			} else {
				$this->assertEmpty($expectedData);
			}
		}
	}

	private function _assertExpectedDataContains($expectedData, $actualDefaultLabel, $actualTranslations) {
		$expectedDataItem = null;

		foreach ($expectedData as $item) {
			if (strtolower($item['default']) == strtolower($actualDefaultLabel)) {
				$expectedDataItem = $item;
			}
		}

		$this->assertNotNull($expectedDataItem);

		$this->assertEquals(
			count($expectedDataItem['translations']), 
			count($actualTranslations),
			sprintf('Error asserting translation count for label %s.', $actualDefaultLabel)
		);

		foreach ($expectedDataItem['translations'] as $expectedLang => $expectedLabel) {
			$this->assertTrue(!empty($actualTranslations[$expectedLang]));
			$this->assertEquals($expectedLabel, $actualTranslations[$expectedLang]);
		}
	}

	private function _getTranslations($lookupId) {
		$env = $this->_getEnv();
		$db = $env->getDb();
		$db->where('ID', $lookupId);

		$translations = array();
		$dbTranslations = $db->get($env->getLookupLangTableName());

		foreach ($dbTranslations as $row) {
			$translations[$row['lookup_lang']] = $row['lookup_label'];
		}

		return $translations;
	}
}