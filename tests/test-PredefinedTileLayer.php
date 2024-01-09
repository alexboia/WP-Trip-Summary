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

class PredefinedTileLayerTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	protected function setUp(): void {
		parent::setUp();
		Abp01_Settings_PredefinedTileLayer::clearPredefinedTileLayersCache();
		$this->_removeAllFilterHooks();
	}

	private function _removeAllFilterHooks() {
		remove_all_filters(Abp01_Settings_PredefinedTileLayer::FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS);
		remove_all_filters(Abp01_Settings_PredefinedTileLayer::FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID);
	}

	protected function tearDown(): void {
		parent::tearDown();
		Abp01_Settings_PredefinedTileLayer::clearPredefinedTileLayersCache();
		$this->_removeAllFilterHooks();
	}

	public function test_correctlyCreated_withApiKeyPlaceholderInUrl() {
		$this->_runPredefinedTileLayerCreationTest(true);
	}

	private function _runPredefinedTileLayerCreationTest($withApieKeyPlaceholderInUrl) {
		$faker = $this->_getFaker();
		$id = $faker->uuid;
		$label = $faker->words(3, true);
		$url = $faker->url;

		$attributionTxt = $faker->words(3, true);
		$attributionUrl = $faker->url;
		$infourl = $faker->url;

		if ($withApieKeyPlaceholderInUrl) {
			$url = $this->_addApiKeyPlaceholderToUrl($url);
		} else {
			$url = $this->_stripApiKeyPlaceholderFromUrl($url);
		}

		$predefinedTileLayer = new Abp01_Settings_PredefinedTileLayer($id, 
			$label, 
			$url, 
			$attributionTxt, 
			$attributionUrl, 
			$infourl);

		$this->assertEquals($id, 
			$predefinedTileLayer->getId());
		$this->assertEquals($label, 
			$predefinedTileLayer->getLabel());
		$this->assertEquals($url, 
			$predefinedTileLayer->getUrl());
		$this->assertEquals($attributionTxt, 
			$predefinedTileLayer->getAttributionText());
		$this->assertEquals($attributionUrl, 
			$predefinedTileLayer->getAttributionUrl());
		$this->assertEquals($infourl, 
			$predefinedTileLayer->getInfoUrl());
		$this->assertEquals($withApieKeyPlaceholderInUrl, 
			$predefinedTileLayer->isApiKeyRequired());
	}

	private function _addApiKeyPlaceholderToUrl($url) {
		if (stripos($url, '{apiKey}') === false) {
			$modifiedUrl = $url[strlen($url) - 1] != '/' 
				? ($url . '/') 
				: $url;
			$modifiedUrl = $modifiedUrl . '{apiKey}';
		} else {
			$modifiedUrl = $url;
		}
		return $modifiedUrl;
	}

	private function _stripApiKeyPlaceholderFromUrl($url) {
		$faker = $this->_getFaker();
		$modifiedUrl = str_ireplace('{apiKey}', 
			$faker->uuid, 
			$url);
		return $modifiedUrl;
	}

	public function test_correctlyCreated_withoutApiKeyPlaceholderInUrl() {
		$this->_runPredefinedTileLayerCreationTest(false);
	}

	public function test_canGetPredefinedTileLayers_noFilters() {
		$expectedLayerIds = $this->_getExpectedDefaultPredefinedTileLayerIds();
		$predefinedTileLayers = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayers();

		$this->_assertPredefinedTileLayersMatchesExpectedIds($expectedLayerIds, 
			$predefinedTileLayers);
	}

	private function _assertPredefinedTileLayersMatchesExpectedIds($expectedIds, array $predefinedTileLayers) {
		$this->assertEquals(count($expectedIds), 
			count($predefinedTileLayers));

		foreach ($expectedIds as $expectedId) {
			$this->assertArrayHasKey($expectedId, $predefinedTileLayers);
			$this->assertNotEmpty($predefinedTileLayers[$expectedId]);
			$this->assertEquals($expectedId, $predefinedTileLayers[$expectedId]->getId());
		}
	}

	private function _getExpectedDefaultPredefinedTileLayerIds() {
		return array(
			Abp01_Settings_PredefinedTileLayer::TL_OPEN_STREET_MAP,
			Abp01_Settings_PredefinedTileLayer::TL_OPEN_STREET_MAP_HIKEBIKE,
			Abp01_Settings_PredefinedTileLayer::TL_TF_ATLAS,
			Abp01_Settings_PredefinedTileLayer::TL_TF_LANDSCAPE,
			Abp01_Settings_PredefinedTileLayer::TL_TF_MOBILE_ATLAS,
			Abp01_Settings_PredefinedTileLayer::TL_TF_NEIGHBOORHOOD,
			Abp01_Settings_PredefinedTileLayer::TL_TF_OPENCYCLEMAP,
			Abp01_Settings_PredefinedTileLayer::TL_TF_OUTDOORS,
			Abp01_Settings_PredefinedTileLayer::TL_TF_PIONEER,
			Abp01_Settings_PredefinedTileLayer::TL_TF_SPINAL_MAP,
			Abp01_Settings_PredefinedTileLayer::TL_TF_TRANSPORT,
			Abp01_Settings_PredefinedTileLayer::TL_TF_TRANSPORT_DARK
		);
	}

	public function test_canGetPredefinedTileLayers_withFilters_removeExistingEntries() {
		$faker = $this->_getFaker();
		$expectedLayerIds = $this->_getExpectedDefaultPredefinedTileLayerIds();
		$layerIdToRemove = $faker->randomElement($expectedLayerIds);

		add_filter(Abp01_Settings_PredefinedTileLayer::FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS, 
			function($predefinedTileLayers) use ($layerIdToRemove, &$expectedLayerIds) {
				unset($predefinedTileLayers[$layerIdToRemove]);
				$expectedLayerIds = array_filter($expectedLayerIds, 
					function($evalLayerId) use ($layerIdToRemove) {
						return $evalLayerId != $layerIdToRemove;
					});

				return $predefinedTileLayers;
			});

		$predefinedTileLayers = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayers();
		$this->_assertPredefinedTileLayersMatchesExpectedIds($expectedLayerIds, 
			$predefinedTileLayers);
	}

	public function test_canGetPredefinedTileLayers_withFilters_addNewEntries() {
		$expectedLayerIds = $this->_getExpectedDefaultPredefinedTileLayerIds();
		
		add_filter(Abp01_Settings_PredefinedTileLayer::FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS, 
			function($predefinedTileLayers) use (&$expectedLayerIds) {
				$newPredefinedTileLayerWithApiKeyPlaceholder = 
					$this->_generateRandomPredefinedTileLayer(true);
				$newPredefinedTileLayerWithoutApiKeyPlaceholder = 
					$this->_generateRandomPredefinedTileLayer(false);

				$predefinedTileLayers[$newPredefinedTileLayerWithApiKeyPlaceholder->getId()] = 
					$newPredefinedTileLayerWithApiKeyPlaceholder;

				$predefinedTileLayers[$newPredefinedTileLayerWithoutApiKeyPlaceholder->getId()] = 
					$newPredefinedTileLayerWithoutApiKeyPlaceholder;

				$expectedLayerIds[] = $newPredefinedTileLayerWithApiKeyPlaceholder->getId();
				$expectedLayerIds[] = $newPredefinedTileLayerWithoutApiKeyPlaceholder->getId();

				return $predefinedTileLayers;
			});

		$predefinedTileLayers = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayers();
		$this->_assertPredefinedTileLayersMatchesExpectedIds($expectedLayerIds, 
			$predefinedTileLayers);
	}

	private function _generateRandomPredefinedTileLayer($withApieKeyInUrl) {
		$faker = $this->_getFaker();
		$id = $faker->uuid;
		$label = $faker->words(3, true);
		$url = $faker->url;

		$attributionTxt = $faker->words(3, true);
		$attributionUrl = $faker->url;
		$infourl = $faker->url;

		if ($withApieKeyInUrl) {
			$url = $this->_addApiKeyPlaceholderToUrl($url);
		} else {
			$url = $this->_stripApiKeyPlaceholderFromUrl($url);
		}

		$predefinedTileLayer = new Abp01_Settings_PredefinedTileLayer($id, 
			$label, 
			$url, 
			$attributionTxt, 
			$attributionUrl, 
			$infourl);

		return $predefinedTileLayer;
	}

	public function test_canGetPredefinedTileLayerInstance_byId_existingId() {
		$expectedIds = $this->_getExpectedDefaultPredefinedTileLayerIds();
		foreach ($expectedIds as $expectedId) {
			$layer = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayer($expectedId);
			$this->assertNotNull($layer);
			$this->assertEquals($expectedId, $layer->getId());
		}
	}

	public function test_tryGetPredefinedTileLayerInstance_byId_emptyId() {
		$emptyIds = array('', null);
		foreach ($emptyIds as $emptyId) {
			$layer = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayer($emptyId);
			$this->assertNull($layer);
		}
	}

	public function test_tryGetPredefinedTileLayerInstance_byId_nonExistingId() {
		$faker = $this->_getFaker();
		for ($i = 0; $i < 10; $i ++) {
			$nonExistingId = $faker->uuid;
			$layer = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayer($nonExistingId);
			$this->assertNull($layer);
		}
	}

	public function test_canGetDefaultTileLayer_noFilters() {
		$this->_assertDefaultTileLayerIs(Abp01_Settings_PredefinedTileLayer::TL_OPEN_STREET_MAP);
	}

	private function _assertDefaultTileLayerIs($expectedDefaultTileLayerId) {
		$defaultTileLayer = Abp01_Settings_PredefinedTileLayer::getDefaultTileLayer();
		$this->assertNotNull($defaultTileLayer);
		$this->assertEquals($expectedDefaultTileLayerId, 
			$defaultTileLayer->getId());
	}

	public function test_canGetDefaultTileLayer_withFilters_filterReturnsValidId() {
		$expectedLayerIds = $this->_getExpectedDefaultPredefinedTileLayerIds();
		foreach ($expectedLayerIds as $expectedLayerId) {
			add_filter(Abp01_Settings_PredefinedTileLayer::FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID, 
				function($defaultTileLayeId) use ($expectedLayerId) {
					return $expectedLayerId;
				});

			$this->_assertDefaultTileLayerIs($expectedLayerId);
			$this->_removeAllFilterHooks();
		}
	}

	public function test_canGetDefaultTileLayer_withFilters_filterReturnsInvalidId() {
		$faker = $this->_getFaker();
		for ($i = 0; $i < 10; $i ++) {
			$thrownException = null;
			$invalidId = $faker->uuid;

			add_filter(Abp01_Settings_PredefinedTileLayer::FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID, 
				function($defaultTileLayeId) use ($invalidId) {
					return $invalidId;
				});

			try {
				$defaultTileLayer = Abp01_Settings_PredefinedTileLayer::getDefaultTileLayer();
			} catch (Abp01_Exception $exc) {
				$thrownException = $exc;
			}

			$this->assertInstanceOf(Abp01_Exception::class, $thrownException);
			$this->_removeAllFilterHooks();
		}
	}

	public function test_canConvertToTileLayerObject_withApiKeyPlaceholderInUrl() {
		$this->_runToLayerObjectConversionTests(true);
	}

	private function _runToLayerObjectConversionTests($withApieKeyPlaceholderInUrl) {
		$predefinedTileLayer = $this->_generateRandomPredefinedTileLayer($withApieKeyPlaceholderInUrl);
		$tileLayerObj = $predefinedTileLayer->getTileLayerObject();
		$this->assertNotNull($tileLayerObj);
		$this->assertEquals($predefinedTileLayer->getUrl(), $tileLayerObj->url);
		$this->assertEquals($predefinedTileLayer->getAttributionText(), $tileLayerObj->attributionTxt);
		$this->assertEquals($predefinedTileLayer->getAttributionUrl(), $tileLayerObj->attributionUrl);
		$this->assertNull($tileLayerObj->apiKey);
	}

	public function test_canConvertToTileLayerObject_withoutApiKeyPlaceholderInUrl() {
		$this->_runToLayerObjectConversionTests(false);
	}

	public function test_canConvertToPlainObject_withApiKeyPlaceholderInUrl() {
		$this->_runToPlainObjectConversionTests(true);
	}

	private function _runToPlainObjectConversionTests($withApieKeyPlaceholderInUrl) {
		$predefinedTileLayer = $this->_generateRandomPredefinedTileLayer($withApieKeyPlaceholderInUrl);
		$plainObj = $predefinedTileLayer->asPlainObject();
		
		$this->assertNotNull($plainObj);
		$this->assertEquals($predefinedTileLayer->getId(), $plainObj->id);
		$this->assertEquals($predefinedTileLayer->getLabel(), $plainObj->label);
		$this->assertEquals($predefinedTileLayer->getInfoUrl(), $plainObj->infoUrl);
		$this->assertEquals($predefinedTileLayer->isApiKeyRequired(), $plainObj->apiKeyRequired);
		$this->assertEquals($predefinedTileLayer->getTileLayerObject(), $plainObj->tileLayerObject);
	}

	public function test_canConvertToPlainObject_withoutApiKeyPlaceholderInUrl() {
		$this->_runToPlainObjectConversionTests(false);
	}
}