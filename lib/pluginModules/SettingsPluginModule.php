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

/**
 * @package WP-Trip-Summary
 */
class Abp01_PluginModules_SettingsPluginModule extends Abp01_PluginModules_PluginModule {
	const ADMIN_ENQUEUE_STYLES_HOOK_PRIORITY = 10;

	const ADMIN_ENQUEUE_SCRIPTS_HOOK_PRIORITY = 10;

	const PLUGIN_TOP_LEVEL_MENU_ENTRY_POSITION = 81;

	const PLUGIN_TOP_LEVEL_MENU_ENTRY_ICON = 'dashicons-chart-area';

	const DEFAULT_TRACK_LINE_WEIGHT = 3;

	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_Settings
	 */
	private $_settings;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_saveSettingsAjaxAction;

	public function __construct(Abp01_Settings $settings, Abp01_View $view, Abp01_Env $env, Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_settings = $settings;
		$this->_view = $view;

		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createManagePluginSettingsAuthCallback();

		$this->_saveSettingsAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_SAVE_SETTINGS, array($this, 'saveSettings'))
				->useDefaultNonceProvider('abp01_nonce_settings')
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();
	}

	public function load() {
		$this->_registerAjaxActions();
		$this->_registerWebPageAssets();
	}

	private function _registerAjaxActions() {
		$this->_saveSettingsAjaxAction
			->register();
	}

	public function getMenuItems() {
		return array(
			array(
				'slug' => ABP01_MAIN_MENU_SLUG,
				'pageTitle' => esc_html__('Trip Summary Settings', 'abp01-trip-summary'),
				'menuTitle' => esc_html__('Trip Summary', 'abp01-trip-summary'),
				'capability' => Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY,
				'callback' => array($this, 'displayAdminSettingsPage'),
				'iconUrl' => self::PLUGIN_TOP_LEVEL_MENU_ENTRY_ICON,
				'position' => self::PLUGIN_TOP_LEVEL_MENU_ENTRY_POSITION,
				'reRegisterAsChildWithMenuTitle' => esc_html__('Settings', 'abp01-trip-summary')
			)
		);
	}

	public function displayAdminSettingsPage() {
		if (!$this->_currentUserCanManagePluginSettings()) {
			die;
		}

		//init data and populate execution context
		$data = new stdClass();
		$data->ajaxSaveAction = ABP01_ACTION_SAVE_SETTINGS;
		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->nonce = $this->_saveSettingsAjaxAction
			->generateNonce();

		//fetch and process tile layer information
		$data->settings = $this->_settings
			->asPlainObject();
		$data->optionsLimits = $this->_settings
			->getOptionsLimits();

		echo $this->_view->renderAdminSettingsPage($data);
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'), 
			self::ADMIN_ENQUEUE_STYLES_HOOK_PRIORITY);

		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'), 
			self::ADMIN_ENQUEUE_SCRIPTS_HOOK_PRIORITY);
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeStyleAdminSettings();
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		return $this->_isViewingSettingsPage() 
			&& $this->_currentUserCanManagePluginSettings();
	}

	private function _isViewingSettingsPage() {
		return $this->_env->isAdminPage(ABP01_MAIN_MENU_SLUG);
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminSettings($this->_getAdminSettingsScriptTranslations());
		}
	}

	private function _getAdminSettingsScriptTranslations() {
		return Abp01_TranslatedScriptMessages::getAdminSettingsScriptTranslations();
	}

	public function saveSettings() {
		$response = abp01_get_ajax_response();

		$unitSystem = Abp01_InputFiltering::getFilteredPOSTValue('unitSystem');
		$initialViewerTab = Abp01_InputFiltering::getFilteredPOSTValue('initialViewerTab');
		$viewerItemLayout = Abp01_InputFiltering::getFilteredPOSTValue('viewerItemLayout');
		$trackLineColour = Abp01_InputFiltering::getFilteredPOSTValue('trackLineColour');
		$tileLayer = $this->_readTileLayerFromHttpPost();

		$validationChain = new Abp01_Validation_Chain();
		$validationChain->addInputValidationRule($unitSystem, 
			$this->_createUnitSystemValidationRule());
		$validationChain->addInputValidationRule($initialViewerTab, 
			$this->_createInitialViewerTabValidationRule());
		$validationChain->addInputValidationRule($viewerItemLayout,
			$this->_createViewerItemLayoutValidationRule());
		$validationChain->addInputValidationRule($tileLayer, 
			$this->_createTileLayerValidationRule($tileLayer));
		$validationChain->addInputValidationRule($trackLineColour, 
			$this->_createTrackLineColurValidationRule());

		if (!$validationChain->isInputValid()) {
			$response->message = $validationChain->getLastValidationMessage();
			return $response;
		}

		$viewerItemDisplayCount = $this->_readBoundedViewerItemValueDisplayCountFromHttpPost();
		$trackLineWeight = $this->_readBoundedTrackLineWeightFromHttpPost();
		$mapHeight = $this->_readBoundedMapHeightFromHttpPost();

		//fill in and save settings
		$this->_settings->setUnitSystem($unitSystem);

		$this->_settings->setShowTeaser(Abp01_InputFiltering::getPOSTValueAsBoolean('showTeaser'));
		$this->_settings->setTopTeaserText(Abp01_InputFiltering::getFilteredPOSTValue('topTeaserText'));
		$this->_settings->setBottomTeaserText(Abp01_InputFiltering::getFilteredPOSTValue('bottomTeaserText'));
		$this->_settings->setInitialViewerTab($initialViewerTab);
		$this->_settings->setViewerItemLayout($viewerItemLayout);
		$this->_settings->setViewerItemValueDisplayCount($viewerItemDisplayCount);
		$this->_settings->setEnableJsonLdFrontendData(Abp01_InputFiltering::getPOSTValueAsBoolean('jsonLdEnabled'));

		$this->_settings->setTileLayers($tileLayer);
		$this->_settings->setShowFullScreen(Abp01_InputFiltering::getPOSTValueAsBoolean('showFullScreen'));
		$this->_settings->setShowMagnifyingGlass(Abp01_InputFiltering::getPOSTValueAsBoolean('showMagnifyingGlass'));
		$this->_settings->setShowMapScale(Abp01_InputFiltering::getPOSTValueAsBoolean('showMapScale'));
		$this->_settings->setAllowTrackDownload(Abp01_InputFiltering::getPOSTValueAsBoolean('allowTrackDownload'));
		$this->_settings->setTrackLineColour($trackLineColour);
		$this->_settings->setTrackLineWeight($trackLineWeight);
		$this->_settings->setShowMinMaxAltitude(Abp01_InputFiltering::getPOSTValueAsBoolean('showMinMaxAltitude'));
		$this->_settings->setShowAltitudeProfile(Abp01_InputFiltering::getPOSTValueAsBoolean('showAltitudeProfile'));
		$this->_settings->setMapHeight($mapHeight);

		if ($this->_settings->saveSettings()) {
			$response->success = true;
		} else {
			$response->message = esc_html__('The settings could not be saved. Please try again.', 'abp01-trip-summary');
		}

		return $response;
	}

	private function _readTileLayerFromHttpPost() {
		$tileLayer = new stdClass();
		$tileLayer->url = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerUrl');
		$tileLayer->attributionUrl = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerAttributionUrl');
		$tileLayer->attributionTxt = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerAttributionTxt');
		$tileLayer->apiKey = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerApiKey');
		return $tileLayer;
	}

	private function _createUnitSystemValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_UnitSystem(), 
			esc_html__('Unsupported unit system', 'abp01-trip-summary')
		);
	}

	private function _createInitialViewerTabValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_InitialViewerTab(),
			esc_html__('Unsupported viewer tab', 'abp01-trip-summary')
		);
	}

	private function _createViewerItemLayoutValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_ViewerItemLayout(),
			esc_html__('Unsupported viewer item layout', 'abp01-trip-summary')
		);
	}

	private function _createTrackLineColurValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_HexColourCode(true),
			esc_html__('The track line colour is not a valid HEX colour code', 'abp01-trip-summary')
		);
	}

	private function _createTileLayerValidationRule(stdClass $tileLayer) {
		$rules = array(
			'url' => array(
				new Abp01_Validation_Rule_Simple(
					new Abp01_Validate_NotEmpty(false),
					esc_html__('Tile layer URL is required', 'abp01-trip-summary')
				),
				new Abp01_Validation_Rule_Simple(
					new Abp01_Validate_TileLayerUrl(),
					esc_html__('Tile layer URL does not have a valid format', 'abp01-trip-summary')
				)
			),
			'attributionUrl' => array(
				new Abp01_Validation_Rule_Simple(
					new Abp01_Validate_Url(true),
					esc_html__('Tile layer attribution URL does not have a valid format', 'abp01-trip-summary')
				)
			)
		);

		if ($this->_tileLayerRequiesApiKey($tileLayer)) {
			$rules['apiKey'] = new Abp01_Validation_Rule_Simple(
				new Abp01_Validate_NotEmpty(false),
				esc_html__('Tile layer API key is required', 'abp01-trip-summary')
			);
		}

		return new Abp01_Validation_Rule_Composite($rules);
	}

	private function _tileLayerRequiesApiKey(stdClass $tileLayer) {
		return $tileLayer->url != null 
			&& stripos($tileLayer->url, '{apiKey}') !== false;
	}

	private function _readBoundedTrackLineWeightFromHttpPost() {
		$minAllowedTrackLineWeight = $this->_settings
			->getMinimumAllowedTrackLineWeight();

		$trackLineWeight = max(Abp01_InputFiltering::getPOSTValueAsInteger('trackLineWeight', self::DEFAULT_TRACK_LINE_WEIGHT), 
			$minAllowedTrackLineWeight);

		return $trackLineWeight;
	}

	private function _readBoundedMapHeightFromHttpPost() {
		$minAllowedMapHeight = $this->_settings
			->getMinimumAllowedMapHeight();

		$mapHeight = max(Abp01_InputFiltering::getPOSTValueAsInteger('mapHeight', $minAllowedMapHeight),
			$minAllowedMapHeight);

		return $mapHeight;
	}

	private function _readBoundedViewerItemValueDisplayCountFromHttpPost() {
		$minViewerItemDisplayCount = $this->_settings
			->getMinimumViewerItemValueDisplayCount();

		$viewerItemDisplayCount = max(Abp01_InputFiltering::getPOSTValueAsInteger('viewerItemValueDisplayCount', $minViewerItemDisplayCount), 
			$minViewerItemDisplayCount);

		return $viewerItemDisplayCount;
	}
}