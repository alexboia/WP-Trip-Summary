<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

trait AdminTestDataHelpers {
	use GenericTestHelpers;
	use SettingsDataHelpers;

	protected function _generateAdminSettingsData() {
		$faker = $this->_getFaker();

		//init data and populate execution context
		$data = new stdClass();
		$data->nonce = $faker->randomAscii;
		$data->ajaxSaveAction = $faker->word;
		$data->ajaxUrl = $faker->url;

		//fetch and process tile layer information
		$data->settings = $this->_generateTestSettingsAsPlainObject();
		$data->optionsLimits = $this->_generateTestSettingsOptionLimits();

		return $data;
	}

	private function _generateTestSettingsAsPlainObject() {
		$asPlainObject = new stdClass();
		$settings = $this->_generateTestSettings();

		foreach ($settings as $key => $value) {
			if ($key != 'tileLayers') {
				$asPlainObject->$key = $value;
			} else {
				$asPlainObject->tileLayer = $value[0];
			}
		}

		$asPlainObject->allowedUnitSystems = Abp01_Settings::getAllowedUnitSystems();
		$asPlainObject->allowedViewerTabs = Abp01_Settings::getAllowedViewerTabs();
		$asPlainObject->allowedItemLayouts = Abp01_Settings::getAllowedItemLayouts();
		$asPlainObject->allowedPredefinedTileLayers = Abp01_Settings::getAllowedPredefinedTileLayers();

		return $asPlainObject;
	}

	protected function _generateHelpPageData() {
		$faker = $this->_getFaker();

		$data = new stdClass();	
		$data->context = new stdClass();
		$data->context->ajaxBaseUrl = $faker->url;
		$data->context->getHelpAction = $faker->word;
		$data->context->getHelpNonce = $faker->randomAscii;

		$data->currentLocale = $faker->locale;
		$data->localesWithHelpContents = array();
		$data->helpContents = $faker->sentences(10, true);

		return $data;
	}

	protected function _generateEmptyHelpPageData() {
		$faker = $this->_getFaker();

		$data = new stdClass();	
		$data->context = new stdClass();
		$data->context->ajaxBaseUrl = $faker->url;
		$data->context->getHelpAction = $faker->word;
		$data->context->getHelpNonce = $faker->randomAscii;
		$data->helpContents = null;

		return $data;
	}

	protected function _generateAboutPageData() {
		$faker = $this->_getFaker();

		$data = new stdClass();
		$data->pluginLogoPath = $faker->imageUrl();
		$data->pluginData = $this->_generateAboutPagePluginData();
		$data->envData = $this->_generateAboutPageEnvData();
		$data->changelog = $this->_generateAboutPageChangeLog();

		return $data;
	}

	private function _generateAboutPagePluginData() {
		$faker = $this->_getFaker();
		return array(
			'Version' => $faker->uuid,
			'WPTS Version Name' => $faker->words(3, true),
			'License URI' => $faker->url,
			'License' => $faker->words(3, true),
			'AuthorURI' => $faker->url,
			'AuthorName' => $faker->name,
			'RequiresWP' => $faker->uuid,
			'RequiresPHP' => $faker->uuid,
			'PluginURI' => $faker->url,

		);
	}

	private function _generateAboutPageEnvData() {
		$faker = $this->_getFaker();
		return array(
			'CurrentWP' => $faker->uuid,
			'CurrentPHP' => $faker->uuid
		);
	}

	private function _generateAboutPageChangeLog() {
		$faker = $this->_getFaker();
		$changelog = array();
		$versionCount = $faker->numberBetween(1, 10);
		
		for ($versionIndex = 0; $versionIndex < $versionCount; $versionIndex ++) {
			$version = $faker->uuid;
			$changelog[$version] = array();

			$versionItemCount = $faker->numberBetween(1, 10);
			for ($itemIndex = 0; $itemIndex < $versionItemCount; $itemIndex ++) {
				$changelog[$version][] = $faker->words(10, true);
			}
		}

		return $changelog;
	}

	protected function _generateAdminLookupPageData() {
		$faker = $this->_getFaker();

		$data = new stdClass();
		$data->controls = new stdClass();
		$data->controls->availableTypes = array();
		foreach (Abp01_Lookup::getSupportedCategories() as $category) {
			$data->controls->availableCategories[$category] = abp01_get_lookup_type_label($category);
		}

		$data->controls->availableLanguages = Abp01_Lookup::getSupportedLanguages();       
		$data->controls->selectedLanguage = '_default';
		$data->controls->selectedCategory = current(array_keys($data->controls->availableCategories));

		$data->context = new stdClass();
		$data->context->getLookupNonce = $faker->randomAscii;
		$data->context->getLookupAction = $faker->word;

		$data->context->addLookupNonce = $faker->randomAscii;
		$data->context->addLookupAction = $faker->word;

		$data->context->editLookupNonce = $faker->randomAscii;
		$data->context->editLookupAction = $faker->word;

		$data->context->deleteLookupNonce = $faker->randomAscii;
		$data->context->deleteLookupAction = $faker->word;
		$data->context->ajaxBaseUrl = $faker->url;

		return $data;
	}

	protected function _generateAdminTripSummaryEditorData() {
		$data = new stdClass();
		$faker = $this->_getFaker();
		$lookup = new Abp01_Lookup();

		$data->difficultyLevels = $lookup->getDifficultyLevelOptions();
		$data->difficultyLevelsAdminUrl = $faker->url;

		$data->pathSurfaceTypes = $lookup->getPathSurfaceTypeOptions();
		$data->pathSurfaceTypesAdminUrl = $faker->url;

		$data->recommendedSeasons = $lookup->getRecommendedSeasonsOptions();
		$data->recommendedSeasonsAdminUrl = $faker->url;

		$data->bikeTypes = $lookup->getBikeTypeOptions();
		$data->bikeTypesAdminUrl = $faker->url;

		$data->railroadOperators = $lookup->getRailroadOperatorOptions();
		$data->railroadOperatorsAdminUrl = $faker->url;

		$data->railroadLineStatuses = $lookup->getRailroadLineStatusOptions();
		$data->railroadLineStatusesAdminUrl = $faker->url;

		$data->railroadLineTypes = $lookup->getRailroadLineTypeOptions();
		$data->railroadLineTypesAdminUrl = $faker->url;

		$data->railroadElectrification = $lookup->getRailroadElectrificationOptions();
		$data->railroadElectrificationAdminUrl = $faker->url;

		//current context information
		$data->postId = $faker->randomNumber();
		$data->hasRouteTrack = false;
		$data->hasRouteInfo = false;
		$data->trackDownloadUrl = $faker->url;

		$data->editInfoNonce = $faker->randomAscii;
		$data->ajaxEditInfoAction = $faker->word;
		$data->uploadTrackNonce = $faker->randomAscii;
		$data->ajaxUploadTrackAction = $faker->word;

		$data->getTrackNonce = $faker->randomAscii;
		$data->ajaxGetTrackAction = $faker->word;	
		
		$data->clearTrackNonce = $faker->randomAscii;
		$data->ajaxClearTrackAction = $faker->word;

		$data->clearInfoNonce = $faker->randomAscii;
		$data->ajaxClearInfoAction = $faker->word;

		$data->ajaxUrl = $faker->url;
		$data->imgBaseUrl = $faker->url;

		$data->uploadMaxFileSize = $faker->randomNumber();
		$data->uploadChunkSize = $faker->randomNumber();
		$data->uploadKey = $faker->randomAscii;

		//TODO: generate randomly
		$data->tourType = null;
		$data->tourInfo = null;

		return $data;
	}

	protected function _getAdminEditorLauncherMetaboxData() {
		$faker = $this->_getFaker();
		
		$data = new stdClass();
		$data->postId = $faker->randomNumber();
		$data->hasRouteTrack = $faker->boolean();
		$data->hasRouteInfo = $faker->boolean();
		$data->trackDownloadUrl = $faker->url;

		return $data;
	}
}