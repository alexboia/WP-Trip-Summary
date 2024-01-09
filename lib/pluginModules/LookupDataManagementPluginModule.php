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
class Abp01_PluginModules_LookupDataManagementPluginModule extends Abp01_PluginModules_PluginModule {
	const ADMIN_ENQUEUE_STYLES_HOOK_PRIORITY = 10;

	const ADMIN_ENQUEUE_SCRIPTS_HOOK_PRIORITY = 10;

	const LOOKUP_MGMT_NONCE_URL_PARAM_NAME = 'abp01_nonce_lookup_mgmt';

	const LOOKUP_INUSE_ITEM_NONCE_URL_PARAM_NAME = 'abp01_nonce_lookup_force_remove';

	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_getLookupDataItemsAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_addLookupDataItemAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_editLookupDataItemAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_deleteLookupDataItemAjaxAction;

	/**
	 * @var array
	 */
	private $_availableLookupCategories = array();

	/**
	 * @var array
	 */
	private $_availableLookupLanguages = array();

	/**
	 * @var Abp01_NonceProvider_Default
	 */
	private $_inUseLookupItemRemovalNonceProvider;

	public function __construct(Abp01_View $view, Abp01_Env $env, Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_view = $view;
		
		$this->_availableLookupCategories = 
			$this->_getAvailableLookupCategories();
		$this->_availableLookupLanguages = 
			$this->_getAvailableLookupLanguages();

		$this->_initNonceProviders();
		$this->_initAjaxActions();
	}

	private function _getAvailableLookupCategories() {
		$availableCategories = array();
		foreach (Abp01_Lookup::getSupportedCategories() as $category) {
			$availableCategories[$category] = abp01_get_lookup_type_label($category);
		}
		return $availableCategories;
	}

	private function _getAvailableLookupLanguages() {
		return Abp01_Lookup::getSupportedLanguages();
	}

	private function _initNonceProviders() {
		$this->_inUseLookupItemRemovalNonceProvider = new Abp01_NonceProvider_Default(
			ABP01_ACTION_DELETE_LOOKUP, 
			self::LOOKUP_INUSE_ITEM_NONCE_URL_PARAM_NAME
		);
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createManagePluginSettingsAuthCallback();

		$this->_getLookupDataItemsAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_GET_LOOKUP,array($this, 'getLookupDataItems'))
				->useDefaultNonceProvider(self::LOOKUP_MGMT_NONCE_URL_PARAM_NAME)
				->authorizeByCallback($authCallback)
				->onlyForHttpGet();

		$this->_addLookupDataItemAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_ADD_LOOKUP, array($this, 'addLookupDataItem'))
				->useDefaultNonceProvider(self::LOOKUP_MGMT_NONCE_URL_PARAM_NAME)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_editLookupDataItemAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_EDIT_LOOKUP, array($this, 'editLookupDataItem'))
				->useDefaultNonceProvider(self::LOOKUP_MGMT_NONCE_URL_PARAM_NAME)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_deleteLookupDataItemAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_DELETE_LOOKUP, array($this, 'deleteLookupDataItem'))
				->useDefaultNonceProvider(self::LOOKUP_MGMT_NONCE_URL_PARAM_NAME)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();
	}

	public function load() {
		$this->_registerAjaxActions();
		$this->_registerWebPageAssets();
	}

	private function _registerAjaxActions() {
		$this->_getLookupDataItemsAjaxAction
			->register();
		$this->_addLookupDataItemAjaxAction
			->register();
		$this->_editLookupDataItemAjaxAction
			->register();
		$this->_deleteLookupDataItemAjaxAction
			->register();
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
			Abp01_Includes::includeStyleAdminLookupManagement();
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		return $this->_isViewingLookupDataManagementPage() 
			&& $this->_currentUserCanManagePluginSettings();
	}

	private function _isViewingLookupDataManagementPage() {
		return $this->_env->isAdminPage(ABP01_LOOKUP_SUBMENU_SLUG);
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminLookupMgmt($this->_getAdminLookupScriptTranslations());
		}
	}

	private function _getAdminLookupScriptTranslations() {
		return Abp01_TranslatedScriptMessages::getAdminLookupScriptTranslations();
	}

	public function getMenuItems() {
        return array(
			array(
				'slug' => ABP01_LOOKUP_SUBMENU_SLUG,
				'parent' => ABP01_MAIN_MENU_SLUG,
				'pageTitle' => esc_html__('Lookup data management', 'abp01-trip-summary'),
				'menuTitle' => esc_html__('Lookup data management', 'abp01-trip-summary'),
				'capability' => Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY,
				'callback' => array($this, 'displayAdminLookupDataPage')
			)
		);
    }

	public function displayAdminLookupDataPage() {
		if (!$this->_currentUserCanManagePluginSettings()) {
			die;
		}

		//set available lookup categories/types
		$data = new stdClass();

		$data->controls = new stdClass();
		$data->controls->availableCategories = 
			$this->_getDisplayableAvailableLookupCategories();
		$data->controls->selectedCategory = 
			$this->_determineInitialSelectedCategory();

		$data->controls->availableLanguages = 
			$this->_getDisplayableAvailableLookupLangauges();
		$data->controls->selectedLanguage = 
			$this->_determineIntialSelectedLanguageCode();

		//set current context
		$data->context = new stdClass();
		$data->context->ajaxBaseUrl = $this->_getAjaxBaseUrl();

		$data->context->getLookupAction = ABP01_ACTION_GET_LOOKUP;
		$data->context->getLookupNonce = $this->_getLookupDataItemsAjaxAction
			->generateNonce();
		
		$data->context->addLookupAction = ABP01_ACTION_ADD_LOOKUP;
		$data->context->addLookupNonce = $this->_addLookupDataItemAjaxAction
			->generateNonce();

		$data->context->editLookupAction = ABP01_ACTION_EDIT_LOOKUP;
		$data->context->editLookupNonce = $this->_editLookupDataItemAjaxAction
		->generateNonce();

		$data->context->deleteLookupAction = ABP01_ACTION_DELETE_LOOKUP;
		$data->context->deleteLookupNonce = $this->_deleteLookupDataItemAjaxAction
			->generateNonce();

		echo $this->_view->renderAdminLookupPage($data);
	}

	private function _getDisplayableAvailableLookupCategories() {
		return $this->_availableLookupCategories;
	}

	private function _getDisplayableAvailableLookupLangauges() {
		return $this->_availableLookupLanguages;
	}

	private function _determineIntialSelectedLanguageCode() {
		$langCode = $this->_readInitialSelectedLanguageCodeFromUrl();

		if (!$this->_isLanguageCodeSupported($langCode)) {
			$langCode = Abp01_Lookup::DEFAULT_LANGUAGE_CODE;
		}

		return $langCode;
	}

	private function _readInitialSelectedLanguageCodeFromUrl() {
		return isset($_GET['abp01_lang']) 
			? $_GET['abp01_lang'] 
			: null;
	}

	private function _isLanguageCodeSupported($langCode) {
		return !empty($langCode) && array_key_exists($langCode, $this->_availableLookupLanguages);
	}

	private function _determineInitialSelectedCategory() {
		$category = $this->_readInitialSelectedCategoryCodeFromUrl();

		if (!$this->_isLookupCategorySupported($category)) {
			$category = $this->_getDefaultSelectedLookupCategory();
		}

		return $category;
	}

	private function _readInitialSelectedCategoryCodeFromUrl() {
		return isset($_GET['abp01_type']) 
			? $_GET['abp01_type'] 
			: null;
	}

	private function _isLookupCategorySupported($category) {
		return array_key_exists($category, $this->_availableLookupCategories);
	}

	private function _getDefaultSelectedLookupCategory() {
		return current($this->_availableLookupCategories);
	}

	public function getLookupDataItems() {
		$forLang = Abp01_InputFiltering::getGETvalueOrDie('lang', 
			array('Abp01_Lookup', 'isLanguageSupported'));

		$forCategory = Abp01_InputFiltering::getGETvalueOrDie('type', 
			array('Abp01_Lookup', 'isTypeSupported'));

		$lookup = $this->_createLookup($forLang);
		$items = $lookup->getLookupOptions($forCategory);

		$response = abp01_get_ajax_response(array(
			'lang' => $forLang,
			'type' => $forCategory,
			'items' => $items,
			'success' => true
		));

		return $response;
	}

	private function _createLookup($forLang) {
		return new Abp01_Lookup($forLang);
	}

	public function addLookupDataItem() {
		$response = abp01_get_ajax_response(array(
			'item' => null
		));

		$forLang = $this->_getLookupLanguageFromHttpPostOrDie();
		$forCategory = $this->_getLookupCategoryFromHttpPostOrDie();

		$defaultLabel = $this->_getDefaultLabelFromHttpPost();
		$translatedLabel = $this->_getTranslatedLabelFromHttpPost();

		$validationChain = new Abp01_Validation_Chain();
		$validationChain->addInputValidationRule($defaultLabel, 
			$this->_createDefaultLabelValidationRule());

		if (!$validationChain->isInputValid()) {
			$response->message = $validationChain->getLastValidationMessage();
			return $response;
		}

		$lookup = $this->_createLookup($forLang);
		$item = $lookup->createLookupItem($forCategory, 
			$defaultLabel);

		if ($item == null) {
			$response->message = esc_html__('The lookup item could not be created', 'abp01-trip-summary');
			return $response;
		}

		if ($this->_shouldCreateLookupItemTranslation($forLang, $translatedLabel)) {
			$response->success = $lookup->addLookupItemTranslation($item->id, 
				$translatedLabel);

			if ($response->success) {
				$item->label = $translatedLabel;
				$item->hasTranslation = true;
			} else {
				$response->message = esc_html__('The lookup item has been created, but the translation could not be saved', 'abp01-trip-summary');
			}
		} else {
			$response->success = true;
		}

		$response->item = $item;
		return $response;
	}

	private function _getLookupLanguageFromHttpPostOrDie() {
		return Abp01_InputFiltering::getPOSTValueOrDie('lang', 
			array('Abp01_Lookup', 'isLanguageSupported'));
	}

	private function _getLookupCategoryFromHttpPostOrDie() {
		return Abp01_InputFiltering::getPOSTValueOrDie('type', 
			array('Abp01_Lookup', 'isTypeSupported'));
	}

	private function _getDefaultLabelFromHttpPost() {
		return Abp01_InputFiltering::getFilteredPOSTValue('defaultLabel');
	}

	private function _getTranslatedLabelFromHttpPost() {
		return Abp01_InputFiltering::getFilteredPOSTValue('translatedLabel');
	}

	private function _createDefaultLabelValidationRule() {
		return new Abp01_Validation_Rule_Simple(
			new Abp01_Validate_NotEmpty(false),
			esc_html__('The default label is mandatory', 'abp01-trip-summary')
		);
	}

	private function _shouldCreateLookupItemTranslation($lang, $translatedLabel) {
		return !Abp01_Lookup::isDefaultLanguage($lang) 
			&& !empty($translatedLabel);
	}

	public function editLookupDataItem() {
		$response = abp01_get_ajax_response();

		$id = $this->_getLookupItemIdFromHttpPostOrDie();
		$forLang = $this->_getLookupLanguageFromHttpPostOrDie();

		$defaultLabel = $this->_getDefaultLabelFromHttpPost();
		$translatedLabel = $this->_getTranslatedLabelFromHttpPost();

		$validationChain = new Abp01_Validation_Chain();
		$validationChain->addInputValidationRule($defaultLabel, 
			$this->_createDefaultLabelValidationRule());

		if (!$validationChain->isInputValid()) {
			$response->message = $validationChain->getLastValidationMessage();
			return $response;
		}

		$lookup = $this->_createLookup($forLang);
		$modifyItemOk = $lookup->modifyLookupItem($id, 
			$defaultLabel);
		
		$modifyItemTranslationOk = false;
		$translatedLabelSpecified = !empty($translatedLabel);

		if ($lookup->hasLookupItemTranslation($id)) {
			$modifyItemTranslationOk = !$translatedLabelSpecified
				? $lookup->deleteLookupItemTranslation($id)
				: $lookup->modifyLookupItemTranslation($id, $translatedLabel);
		} else {
			$modifyItemTranslationOk = $translatedLabelSpecified 
				? $lookup->addLookupItemTranslation($id, $translatedLabel) 
				: true;
		}

		if (!$modifyItemOk || !$modifyItemTranslationOk) {
			$response->message = esc_html__('The lookup item could not be modified', 'abp01-trip-summary');
		} else {
			$response->success = true;
		}

		return $response;
	}

	private function _getLookupItemIdFromHttpPostOrDie() {
		return Abp01_InputFiltering::getPOSTValueOrDie('id', 
			'is_numeric');
	}

	public function deleteLookupDataItem() {
		$id = $this->_getLookupItemIdFromHttpPostOrDie();
		$forLang = $this->_getLookupLanguageFromHttpPostOrDie();

		if ($this->_shouldOnlyDeleteLookupItemTranslation($forLang)) {
			$response = $this->_deleteLookupItemTranslation($id, $forLang);
		} else {
			$response = $this->_deleteEntireLookupItem($id, $forLang);
		}

		return $response;
	}

	private function _shouldOnlyDeleteLookupItemTranslation($forLang) {
		$onlyDeleteTranslation = Abp01_InputFiltering::getPOSTValueOrDie('deleteOnlyLang') 
			=== 'true';
		return $onlyDeleteTranslation 
			&& !Abp01_Lookup::isDefaultLanguage($forLang);
	}

	private function _deleteLookupItemTranslation($id, $forLang) {
		$response = abp01_get_ajax_response();
		$lookup = $this->_createLookup($forLang);

		if ($lookup->deleteLookupItemTranslation($id)) {
			$response->success = true;
		} else {
			$response->message = esc_html__('The item translation could not be deleted.', 'abp01-trip-summary');
		}

		return $response;
	}

	private function _deleteEntireLookupItem($id, $forLang) {
		$response = abp01_get_ajax_response(array(
			'requiresConfirmation' => false,
			'confirmationNonce' => null
		));

		$lookup = $this->_createLookup($forLang);
		$usageCount = $lookup->getLookupUsageCount($id);
		
		$proceedWithRemoval = false;
		$confirmationNonce = null;

		if ($usageCount > 0) {
			if ($this->_requestHasInUseLookupRemovalNonce()) {
				$proceedWithRemoval = $this->_verifyInuseLookupRemovalNonce($id);
			} else {
				$confirmationNonce = $this->_generateInuseLookupRemovalNonce($id);
			}
		} else {
			$proceedWithRemoval = true;
		}
	
		if ($proceedWithRemoval) {
			if ($lookup->deleteLookup($id)) {
				$response->success = true;
			} else {
				$response->message = esc_html__('The item could not be deleted', 'abp01-trip-summary');
			}
		} else if (!empty($confirmationNonce)) {
			$response->requiresConfirmation = true;
			$response->confirmationNonce = $confirmationNonce;
			$response->message = sprintf(
				esc_html__('The item is still associated with %d post(s). Do you wish to proceed?', 'abp01-trip-summary'), 
				$usageCount
			);
		} else {
			$response->message = esc_html__('The item could not be deleted', 'abp01-trip-summary');
		}

		return $response;
	}

	private function _requestHasInUseLookupRemovalNonce() {
		return $this->_inUseLookupItemRemovalNonceProvider
			->hasNonceInCurrentContext();
	}

	private function _verifyInuseLookupRemovalNonce($lookupId) {
		return $this->_inUseLookupItemRemovalNonceProvider
			->validateNonce($lookupId);
	}

	function _generateInuseLookupRemovalNonce($lookupId) {
		return $this->_inUseLookupItemRemovalNonceProvider
			->generateNonce($lookupId);
	}
}