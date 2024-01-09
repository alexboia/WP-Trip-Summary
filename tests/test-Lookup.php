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

class LookupTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use LookupDataTestHelpers;
	use RouteInfoTestDataSets;

	const SAMPLE_LOOKUP_LANG = 'sample';

	private $_postIdGenerator = 1;

	private $_initialLookupData = array();

	private $_sampleLookupData = array();

	protected function setUp(): void {
		parent::setUp();
		$this->_initialLookupData = $this->_readAllLookupData();
		$this->_clearTestData();
		$this->_installTestData();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_clearTestData();
		$this->_restoreAllLookupData($this->_initialLookupData);
	}

	public function test_canReadExistingLookup_genericRead_defaultLang() {
		$lookup = new Abp01_Lookup();
		foreach ($this->_sampleLookupData as $itemId => $data) {
			$result = $lookup->lookup($data['data_category'], $itemId);
			$this->_assertLookupMatchesSampleData($result, $data, false);
		}
	}

	public function test_canReadExistingLookup_genericRead_sampleLang() {
		$lookup = new Abp01_Lookup(self::SAMPLE_LOOKUP_LANG);
		foreach ($this->_sampleLookupData as $itemId => $data) {
			$result = $lookup->lookup($data['data_category'], $itemId);
			$this->_assertLookupMatchesSampleData($result, $data, true);
		}
	}

	public function test_canCreateLookupItem() {
		$label = $this->_newFakeLabel();
		$type = $this->_getRandomLookupType();
		
		$lookup = new Abp01_Lookup();
		$item = $lookup->createLookupItem($type, $label);
		
		$this->assertNotNull($item);
		$this->assertEquals($label, $item->label);
		$this->assertEquals($type, $item->type);
		$this->assertGreaterThan(0, $item->id);
	}

	public function test_canModifyLookupItem() {
		$items = array(
			$this->_getLookupItemWithPostAssociation(),
			$this->_getLookupItemWithoutPostAssociation()
		);

		foreach ($items as $item) {
			$lookup = new Abp01_Lookup();	
			$existingItemId = $item['data_id'];
			$category = $item['data_category'];

			$newLabel = $this->_newFakeLabel();

			$lookup->modifyLookupItem($existingItemId, $newLabel);
			$item = $lookup->lookup($category, $existingItemId);

			$this->assertNotNull($item);
			$this->assertEquals($newLabel, $item->label);
			$this->assertEquals($category, $item->type);
			$this->assertEquals($existingItemId, $item->id);
		}
	}

	public function test_canDeleteLookupItem_withoutPostsAssociation() {
		$lookup = new Abp01_Lookup();
		$existingItem = $this->_getLookupItemWithoutPostAssociation();
		$existingItemId = $existingItem['data_id'];

		$lookup->deleteLookup($existingItemId);
		$item = $this->_getLookup($existingItemId);
		$itemLangs = $this->_getLookupTranslations($existingItemId);
		
		$this->assertEmpty($item);
		$this->assertEmpty($itemLangs);
		$this->assertFalse($lookup->isLookupInUse($existingItemId));
	}

	public function test_canDeleteLookupItem_withPostsAssociation() {
		$lookup = new Abp01_Lookup();
		$existingItem = $this->_getLookupItemWithPostAssociation();
		$existingItemId = $existingItem['data_id'];
		$initialInfo = $existingItem['data_route_info'];
		
		$allFields = Abp01_Route_Info::getValidFieldsForType($existingItem['data_route_info_type']);
		$fieldName = $existingItem['data_route_info_field'];

		$fieldDef = $allFields[$fieldName];
		$isMultiple = isset($fieldDef['multiple']) && $fieldDef['multiple'] == true;

		$lookup->deleteLookup($existingItemId);
		$item = $this->_getLookup($existingItemId);
		$itemLangs = $this->_getLookupTranslations($existingItemId);
		
		$this->assertEmpty($item);
		$this->assertEmpty($itemLangs);
		$this->assertFalse($lookup->isLookupInUse($existingItemId));

		$info = $this->_getRouteInfo($existingItemId);
		$this->assertNotEmpty($info);
		
		$initialValue = $initialInfo->$fieldName;		
		$newValue = $info->$fieldName;

		if ($isMultiple) {
			$diff = array_diff($initialValue, $newValue);
			$this->assertEquals(1, count($diff));
			$this->assertEquals($existingItemId, $diff[0]);
		} else {
			$this->assertEmpty($newValue);
		}
	}

	public function test_canCreateLookupItemTranslation() {
		$items = array(
			$this->_getLookupItemWithoutPostAssociation(),
			$this->_getLookupItemWithPostAssociation()
		);

		foreach ($items as $item) {
			$lang = 'fr';
			$newLabel = $this->_newFakeLabel();
			$existingItemId = $item['data_id'];
	
			$lookup = new Abp01_Lookup($lang);
			$result = $lookup->addLookupItemTranslation($existingItemId, $newLabel);
	
			$this->assertTrue($result);
			$this->_assertLookupTranslationMatchesLabel($existingItemId, $lang, $newLabel);
		}
	}

	public function test_canModifyLookupItemTranslation() {
		$items = array(
			$this->_getLookupItemWithoutPostAssociation(),
			$this->_getLookupItemWithPostAssociation()
		);

		foreach ($items as $item) {
			$newLabel = $this->_newFakeLabel();
			$existingItemId = $item['data_id'];
			$existingItemLang = self::SAMPLE_LOOKUP_LANG;
	
			$lookup = new Abp01_Lookup($existingItemLang);
			$result = $lookup->modifyLookupItemTranslation($existingItemId, $newLabel);
			$repeatedResult = $lookup->modifyLookupItemTranslation($existingItemId, $newLabel);
	
			$this->assertTrue($result);
			$this->assertTrue($repeatedResult);
			$this->_assertLookupTranslationMatchesLabel($existingItemId, $existingItemLang, $newLabel);
		}
	}

	public function test_canDeleteLookupItemTranslation() {
		$items = array(
			$this->_getLookupItemWithoutPostAssociation(),
			$this->_getLookupItemWithPostAssociation()
		);

		foreach ($items as $item) {
			$existingItemId = $item['data_id'];
			$existingItemLang = self::SAMPLE_LOOKUP_LANG;
	
			$lookup = new Abp01_Lookup($existingItemLang);
			$result = $lookup->deleteLookupItemTranslation($existingItemId);
			
			$this->assertTrue($result);
			$this->_assertLookupItemTranslationMissing($existingItemId, $existingItemLang);
		}
	}

	public function test_canCheckIfLookupItemInUse_itemInUse() {
		$item = $this->_getLookupItemWithPostAssociation();
		$lookup = new Abp01_Lookup();
		$this->assertTrue($lookup->isLookupInUse($item['data_id']));
	}

	public function testCanCheckIfLookupItemInUse_itemNotInUse() {
		$item = $this->_getLookupItemWithoutPostAssociation();
		$lookup = new Abp01_Lookup();
		$this->assertFalse($lookup->isLookupInUse($item['data_id']));
	}

	private function _installTestDataSet($associateWithPost) {
		$db = $this->_getDb();
		$table = $this->_getEnv()->getLookupTableName();
		$langTable = $this->_getEnv()->getLookupLangTableName();
		$lookupPostsTable = $this->_getEnv()->getRouteDetailsLookupTableName();
		$routeDetailsTable = $this->_getEnv()->getRouteDetailsTableName();
		$faker = self::_getFaker();

		foreach (Abp01_Route_Info::getSupportedTypes() as $infoType) {
			$allFields = Abp01_Route_Info::getValidFieldsForType($infoType);
			$lookupFieldNames = Abp01_Route_Info::getAllLookupFieldsForType($infoType);

			foreach ($lookupFieldNames as $fieldName) {
				$fieldDef = $allFields[$fieldName];
				$lookupCategory = Abp01_Route_Info::getLookupKeyForType($infoType, $fieldName);

				$newName = $faker->words(3, true);
				$newNameLang = $faker->words(3, true);

				$newLookupId = $db->insert($table, array(
					'lookup_category' => $lookupCategory,
					'lookup_label' => $newName
				));

				if ($newLookupId) {
					$db->insert($langTable, array(
						'ID' => $newLookupId,
						'lookup_label' => $newNameLang,
						'lookup_lang' => self::SAMPLE_LOOKUP_LANG,
					));

					$this->_sampleLookupData[$newLookupId] = array(
						'data_id' => $newLookupId,
						'data_label' => $newName,
						'data_label_lang' => $newNameLang,
						'data_category' => $lookupCategory, 
						'data_route_info' => null,
						'data_route_info_type' => $infoType,
						'data_route_info_field' => $fieldName,
						'has_post' => $associateWithPost,
						'post_id' => null
					);

					if ($associateWithPost) {
						$newLookupPostId = $this->_postIdGenerator ++;

						$info = new Abp01_Route_Info($infoType);
						$info->$fieldName = $this->_generateValue($fieldDef);

						$db->insert($lookupPostsTable, array(
							'post_ID' => $newLookupPostId,
							'lookup_ID' => $newLookupId
						));

						$db->insert($routeDetailsTable, array(
							'post_ID' => $newLookupPostId,
							'route_type' => $infoType,
							'route_data_serialized' => $info->toJson(),
							'route_data_last_modified_by' => 1,
							'route_data_last_modified_at' => $db->now()
						));

						$this->_sampleLookupData[$newLookupId] = array_merge($this->_sampleLookupData[$newLookupId], 
							array(
								'data_route_info' => $info,
								'post_id' => $newLookupPostId
							));
					}
				}
			}
		}
	}

	private function _installTestData() {
		$this->_installTestDataSet(true);
		$this->_installTestDataSet(false);
	}

	private function _clearTestData() {
		$this->_clearAllLookupData();
		$this->_sampleLookupData = array();
		$this->_postIdGenerator = 1;
	}

	private function _getLookup($lookupId) {
		$db = $this->_getDb();
		$table = $this->_getEnv()->getLookupTableName();
		$db->where('ID', $lookupId);
		return $db->getOne($table);
	}

	private function _getLookupTranslations($lookupId, $lang = null) {
		$db = $this->_getDb();
		$table = $this->_getEnv()->getLookupLangTableName();
		$db->where('ID', $lookupId);
		if ($lang) {
			$db->where('lookup_lang', $lang);
			return $db->getOne($table);
		}
		return $db->get($table);
	}

	private function _getRouteInfo($lookupId) {
		$db = $this->_getDb();
		$table = $this->_getEnv()->getRouteDetailsTableName();
		$postId = $this->_sampleLookupData[$lookupId]['post_id'];

		$db->where('post_ID', $postId);
		$row = $db->getOne($table);
		if (!empty($row)) {
			return Abp01_Route_Info::fromJson($row['route_type'], $row['route_data_serialized']);
		} else {
			return null;
		}
	}

	private function _assertLookupMatchesSampleData(stdClass $result, array $data, $useLang) {
		$this->assertNotNull($result);
		if  (!$useLang) {
			$this->assertEquals($data['data_label'], $result->label);
		} else {
			$this->assertEquals($data['data_label_lang'], $result->label);
		}
		$this->assertEquals($data['data_id'], $result->id);
		$this->assertEquals($data['data_category'], $result->type);
	}

	private function _assertLookupTranslationMatchesLabel($lookupId, $lang, $label) {
		$itemTranslation = $this->_getLookupTranslations($lookupId, $lang);
		$this->assertNotEmpty($itemTranslation);
		$this->assertEquals($label, $itemTranslation['lookup_label']);
	}

	private function _assertLookupItemTranslationMissing($lookupId, $lang) {
		$itemTranlation = $this->_getLookupTranslations($lookupId, $lang);
		$this->assertNull($itemTranlation);
	}

	private function _getLookupItemWithoutPostAssociation() {
		foreach ($this->_sampleLookupData as $itemId => $data) {
			if (!$data['has_post']) {
				return $data;
			}
		}
	}

	private function _getLookupItemWithPostAssociation() {
		foreach ($this->_sampleLookupData as $itemId => $data) {
			if ($data['has_post']) {
				return $data;
			}
		}
	}

	private function _getRandomLookupType() {
		$supportedTypes = Abp01_Lookup::getSupportedCategories();
		shuffle($supportedTypes);
		return $supportedTypes[0];
	}

	private function _newFakeLabel() {
		$faker = self::_getFaker();
		return $faker->words(3, true);
	}

	private function _newFakeIntNumber($min = 0, $max = PHP_INT_MAX) {
		$faker = self::_getFaker();
		return $faker->numberBetween($min, $max);
	}
}