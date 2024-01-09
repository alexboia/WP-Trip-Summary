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
class Abp01_PluginModules_HelpPluginModule extends Abp01_PluginModules_PluginModule {
    const ADMIN_ENQUEUE_ASSETS_HOOK_PRIORITY = 10;
    
    /**
     * @var Abp01_View
     */
    private $_view;

    /**
     * @var Abp01_Help
     */
    private $_help;

    /**
     * @var Abp01_AdminAjaxAction
     */
    private $_getHelpForLocaleAjaxAction;

    public function __construct(Abp01_Help $help, Abp01_View $view, Abp01_Env $env, Abp01_Auth $auth) {
        parent::__construct($env, $auth);

        $this->_view = $view;
        $this->_help = $help;

        $this->_initAjaxActions();
    }

    private function _initAjaxActions() {
        $this->_getHelpForLocaleAjaxAction = 
            Abp01_AdminAjaxAction::create(ABP01_ACTION_GET_HELP_FOR_LOCALE, array($this, 'getHelpForLocale'))
                ->authorizeByCallback($this->_createManagePluginSettingsAuthCallback())
                ->setRequiresAuthentication(true)
                ->useDefaultNonceProvider('abp01_help_nonce')
                ->onlyForHttpGet();
    }

    public function load() {
        $this->_registerAjaxActions();
        $this->_registerWebPageAssets();
    }

    private function _registerAjaxActions() {
		$this->_getHelpForLocaleAjaxAction
			->register();
	}

    private function _registerWebPageAssets() {
        add_action('admin_enqueue_scripts', 
            array($this, 'onAdminEnqueueStyles'), 
            self::ADMIN_ENQUEUE_ASSETS_HOOK_PRIORITY);
        add_action('admin_enqueue_scripts', 
            array($this, 'onAdminEnqueueScripts'), 
            self::ADMIN_ENQUEUE_ASSETS_HOOK_PRIORITY);
    }

    public function onAdminEnqueueStyles() {
        if ($this->_shouldEnqueueWebPageAssets()) {
            Abp01_Includes::includeStyleAdminHelp();
        }
    }

    public function onAdminEnqueueScripts() {
        if ($this->_shouldEnqueueWebPageAssets()) {
            Abp01_Includes::includeScriptAdminHelp($this->_getAdminHelpScriptTranslations());
        }
    }

    private function _getAdminHelpScriptTranslations() {
        return Abp01_TranslatedScriptMessages::getAdminHelpScriptTranslations();
    }

    private function _shouldEnqueueWebPageAssets() {
        return $this->_isBrowsingHelp() 
            && $this->_currentUserCanManagePluginSettings();
    }

    private function _isBrowsingHelp() {
        return $this->_env->isAdminPage(ABP01_HELP_SUBMENU_SLUG);
    }

    public function getMenuItems() {
        return array(
            array(
                'slug' => ABP01_HELP_SUBMENU_SLUG,
                'parent' => ABP01_MAIN_MENU_SLUG,
                'pageTitle' => esc_html__('Help', 'abp01-trip-summary'),
                'menuTitle' => esc_html__('Help', 'abp01-trip-summary'),
                'capability' => Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY,
                'callback' => array($this, 'displayAdminHelpPage')
            )
        );
    }

    public function displayAdminHelpPage() {
        if (!$this->_currentUserCanManagePluginSettings()) {
            return;
        }
    
        $data = new stdClass();	
        $data->currentLocale = Abp01_Locale::getCurrentLocale();
        $data->localesWithHelpContents = $this->_help
            ->getLocalesWithHelpContents();
        $data->helpContents = $this->_help
            ->getHelpContentForCurrentLocale();

        $data->context = new stdClass();
        $data->context->ajaxBaseUrl = $this->_getAjaxBaseUrl();
        $data->context->getHelpAction = ABP01_ACTION_GET_HELP_FOR_LOCALE;
        $data->context->getHelpNonce = $this->_getHelpForLocaleAjaxAction
            ->generateNonce();

        echo $this->_view->renderAdminHelpPage($data);
    }

    public function getHelpForLocale() {
        $locale = $this->_readLocaleFromHttpGet();
        if (empty($locale) || !$this->_help->hasHelpFileForLocale($locale)) {
            return $locale;
        }

        $response = abp01_get_ajax_response(array(
            'htmlHelpContents' => null
        ));

        $htmlHelpContents = $this->_help->getHelpContentForLocale($locale);
        $response->htmlHelpContents = $htmlHelpContents;
        $response->success = true;

        return $response;
    }

    private function _readLocaleFromHttpGet() {
        return Abp01_InputFiltering::getFilteredGETValue('help_locale');
    }
}