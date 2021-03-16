<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class ViewTests extends WP_UnitTestCase {
    use ViewerTestDataHelpers;
    use AdminTestDataHelpers;

    public function setUp() {
        parent::setUp();
        TestFrontendTheme::reset();
    }

    public function tearDown() {
        parent::tearDown();
        TestFrontendTheme::reset();
    }
    
    public function test_viewCorrectlyInitialized_withoutCustomThemeClass() {
        $view = $this->_getInitializedView();
        $this->assertTrue($view->isUsingTheme('Abp01_FrontendTheme_Decorator'));
    }

    public function test_viewCorrectlyInitialized_withCustomThemeClass() {
        $this->_registerTestThemeClass();
        $view = $this->_getInitializedView();
        $this->assertTrue($view->isUsingTheme('TestFrontendTheme'));
    }

    private function _registerTestThemeClass() {
        add_filter('abp01_get_frotend_theme_class', function($currentThemeClass) {
            return 'TestFrontendTheme';
        });
    }

    public function test_canEnqueueFrontendViewerScripts_withLocalization() {
        $view = $this->_getInitializedView();

        $view->includeFrontendViewerScripts(Abp01_TranslatedScriptMessages::getFrontendViewerScriptTranslations());
        $this->_assertScriptEnqueuedWithTranslations(Abp01_Includes::JS_ABP01_FRONTEND_MAIN, 
            'abp01Settings', 
            'abp01FrontendL10n');
    }

    private function _assertScriptEnqueuedWithTranslations($handle) {
        $this->assertTrue(wp_script_is($handle, 'enqueued'));
        if (func_num_args() > 1) {
            for ($i = 1; $i < func_num_args(); $i ++) {
                $this->assertTrue(wp_script_has_localization($handle, func_get_arg($i)));
            }
        }
    }

    public function test_canEnqueueFrontendViewerScripts_withoutLocalization() {
        $view = $this->_getInitializedView();

        $view->includeFrontendViewerScripts(array());

        $this->_assertScriptEnqueuedWithTranslations(Abp01_Includes::JS_ABP01_FRONTEND_MAIN, 
            'abp01Settings');
    }

    public function test_canEnqueueFrontendViewerStyles() {
        $this->_registerTestThemeClass();
        $view = $this->_getInitializedView();

        $view->includeFrontendViewerStyles();
        $this->assertTrue(TestFrontendTheme::areFrontendViewerStylesIncluded());
    }

    public function test_canRenderFrontendTeaser() {
        $this->_registerTestThemeClass();
        $view = $this->_getInitializedView();

        $data = $this->_getRandomFrontendTopTeaserData();
        $teaser = $view->renderFrontendTeaser($data);

        $this->assertEquals(TestFrontendTheme::GENERATED_TEASER_CODE, $teaser);
        $this->assertTrue(TestFrontendTheme::areFrontendViewerHelpersIncluded());
        $this->assertTrue(TestFrontendTheme::isTeaserRenderedWithData($data));
    }

    public function test_canRenderFrontendViewer() {
        $this->_registerTestThemeClass();
        $view = $this->_getInitializedView();

        $dataset = array(
            $this->_getRandomHikingTripData(),
            $this->_getRandomBikingTripData(),
            $this->_getRandomTrainRideTripData()
        );

        foreach ($dataset as $data) {
            $viewer = $view->renderFrontendViewer($data);

            $this->_assertCoreViewerCodeCorrect($viewer);
            $this->_assertViewerCodeHasViewerJsVars($viewer);

            $this->assertTrue(TestFrontendTheme::areFrontendViewerHelpersIncluded());
            $this->assertTrue(TestFrontendTheme::isViewerRenderedWithData($data));
            TestFrontendTheme::reset();
        }
    }

    private function _assertCoreViewerCodeCorrect($viewer) {
        $this->assertNotEmpty($viewer);
        $this->assertStringEndsWith(TestFrontendTheme::GENERATED_VIEWER_CODE, $viewer);
    }

    private function _assertViewerCodeHasViewerJsVars($viewer) {
        $this->assertContains('<script type="text/javascript">', $viewer);
        $this->assertContains('var abp01_imgBase', $viewer);
        $this->assertContains('var abp01_ajaxUrl', $viewer);
        $this->assertContains('var abp01_ajaxGetTrackAction', $viewer);
        $this->assertContains('var abp01_downloadTrackAction', $viewer);
        $this->assertContains('var abp01_hasInfo', $viewer);
        $this->assertContains('var abp01_hasTrack', $viewer);
        $this->assertContains('var abp01_postId', $viewer);
        $this->assertContains('var abp01_nonceGet', $viewer);
        $this->assertContains('var abp01_nonceDownload', $viewer);
        $this->assertContains('</script>', $viewer);
    }

    public function test_canRenderAdminSettingsPage() {
        $view = $this->_getInitializedView();
        $data = $this->_generateAdminSettingsData();

        $settingsPage = $view->renderAdminSettingsPage($data);
        
        $this->assertNotEmpty($settingsPage);
        $this->_assertAdminSettingsPageHasScriptVars($settingsPage);
        $this->_assertAdminSettingsPageBasicStructureCorrect($settingsPage);
    }

    private function _assertAdminSettingsPageHasScriptVars($settingsPage) {
        $this->assertContains('<script type="text/javascript">', $settingsPage);
        $this->assertContains('var abp01_nonce', $settingsPage);
        $this->assertContains('var abp01_ajaxSaveAction', $settingsPage);
        $this->assertContains('var abp01_ajaxBaseUrl', $settingsPage);
        $this->assertContains('</script>', $settingsPage);
    }

    private function _assertAdminSettingsPageBasicStructureCorrect($settingsPage) {
        $this->assertContains('<div id="abp01-settings-page">', $settingsPage);
        $this->assertContains('<form id="abp01-settings-form" method="post">', $settingsPage);
        $this->assertContains('<div class="abp01-settings-info description">', $settingsPage);
        $this->assertContains('<div class="apb01-settings-save">', $settingsPage);
        $this->assertContains('<script id="tpl-abp01-progress-container" type="text/x-kite">', $settingsPage);
    }

    public function test_canRenderAdminHelpPage_nonEmptyHelpContents() {
        $view = $this->_getInitializedView();
        $data = $this->_generateHelpPageData();

        $helpPage = $view->renderAdminHelpPage($data);
        
        $this->assertNotEmpty($helpPage);
        $this->assertContains($data->helpContents, $helpPage);
        $this->_assertAdminHelpPageBasicStructureCorrect($helpPage);
    }

    private function _assertAdminHelpPageBasicStructureCorrect($helpPage) {
        $this->assertContains('<div id="abp01-help-page">', $helpPage);
        $this->assertContains('<div id="abp01-help-contents">', $helpPage);
    }

    public function test_canRenderAdminHelpPage_emptyHelpContents() {
        $view = $this->_getInitializedView();
        $data = $this->_generateEmptyHelpPageData();

        $helpPage = $view->renderAdminHelpPage($data);
        
        $this->assertNotEmpty($helpPage);
        $this->_assertAdminHelpPageBasicStructureCorrect($helpPage);
    }

    private function _assertEmptyAdminHelpPageBasicStructureCorrect($helpPage) {
        $this->_assertAdminHelpPageBasicStructureCorrect($helpPage);
        $this->assertContains('<div id="abp01-help-result" class="error settings-error abp01-help-result">', $helpPage);
    }

    public function test_canRenderAdminLookupPage() {
        $view = $this->_getInitializedView();
        $data = $this->_generateAdminLookupPageData();

        $lookupPage = $view->renderAdminLookupPage($data);

        $this->assertNotEmpty($lookupPage);
        $this->_assertAdminLookupPageHasScriptVars($lookupPage);
        $this->_assertAdminLookupPageBasicStructureCorrect($lookupPage);
    }

    private function _assertAdminLookupPageHasScriptVars($lookupPage) {
        $this->assertContains('<script type="text/javascript">', $lookupPage);
        $this->assertContains('var abp01_getLookupNonce', $lookupPage);
        $this->assertContains('var abp01_addLookupNonce', $lookupPage);
        $this->assertContains('var abp01_editLookupNonce', $lookupPage);
        $this->assertContains('var abp01_deleteLookupNonce', $lookupPage);
        $this->assertContains('var abp01_ajaxUrl', $lookupPage);
        $this->assertContains('var abp01_ajaxGetLookupAction', $lookupPage);
        $this->assertContains('var abp01_ajaxAddLookupAction', $lookupPage);
        $this->assertContains('var abp01_ajaxEditLookupAction', $lookupPage);
        $this->assertContains('var abp01_ajaxDeleteLookupAction', $lookupPage);
        $this->assertContains('</script>', $lookupPage);
    }

    private function _assertAdminLookupPageBasicStructureCorrect($lookupPage) {
        $this->assertContains('<div id="abp01-admin-lookup-page">', $lookupPage);
        $this->assertContains('<div id="abp01-admin-lookup-container">', $lookupPage);
        $this->assertContains('<div id="abp01-admin-lookup-control-container">', $lookupPage);
        $this->assertContains('<div id="abp01-lookup-item-form" style="display: none;">', $lookupPage);
        $this->assertContains('<div id="abp01-lookup-item-form" style="display: none;">', $lookupPage); 
        $this->assertContains('<div id="abp01-lookup-item-delete-form" style="display: none;">', $lookupPage);
        $this->assertContains('<div id="abp01-admin-lookup-listing-container">', $lookupPage);
        $this->assertContains('<script id="tpl-abp01-progress-container" type="text/x-kite">', $lookupPage);
    }

    public function test_canRenderAdminTripSummaryEditor() {
        $view = $this->_getInitializedView();
        $data = $this->_generateAdminTripSummaryEditorData();

        $editor = $view->renderAdminTripSummaryEditor($data);

        $this->assertNotEmpty($editor);
        $this->_assertAdminViewHelperFunctionsExist();
        $this->_assertAdminEditorHasScriptVars($editor);
        $this->_assertAdminEditorBasicStructureCorrect($editor);
    }

    private function _assertAdminViewHelperFunctionsExist() {
        $expectedFunctions = array(
            'abp01_render_partial_view',
            'abp01_extract_value_from_data',
            'abp01_render_difficulty_level_options',
            'abp01_render_checkbox_option',
            'abp01_render_select_option',
            'abp01_render_checkbox_options',
            'abp01_render_select_options'
        );

        foreach ($expectedFunctions as $fn) {
            $this->assertTrue(function_exists($fn));
        }
    }

    private function _assertAdminEditorHasScriptVars($editor) {
        $this->assertContains('<script type="text/javascript">', $editor);
        $this->assertContains('var abp01_imgBase', $editor);
        $this->assertContains('var abp01_ajaxUrl', $editor);
        $this->assertContains('var abp01_ajaxEditInfoAction', $editor);
        $this->assertContains('var abp01_ajaxUploadTrackAction', $editor);
        $this->assertContains('var abp01_ajaxGetTrackAction', $editor);
        $this->assertContains('var abp01_ajaxClearTrackAction', $editor);
        $this->assertContains('var abp01_ajaxClearInfoAction', $editor);
        $this->assertContains('var abp01_tourType', $editor);
        $this->assertContains('var abp01_uploadMaxFileSize', $editor);
        $this->assertContains('var abp01_uploadChunkSize', $editor);
        $this->assertContains('var abp01_uploadKey', $editor);
        $this->assertContains('var abp01_postId', $editor);
        $this->assertContains('var abp01_hasTrack', $editor);
        $this->assertContains('var abp01_hasInfo', $editor);
        $this->assertContains('var abp01_baseTitle', $editor);
        $this->assertContains('</script>', $editor);
    }

    private function _assertAdminEditorBasicStructureCorrect($editor) {
        $this->assertContains('<div id="abp01-techbox-editor" style="display:none;">', $editor);
        $this->assertContains('<div id="abp01-editor-wrapper" class="abp01-editor-wrapper">', $editor);
        $this->assertContains('<script id="tpl-abp01-formInfo-unselected" type="text/x-kite">', $editor);
        $this->assertContains('<script id="tpl-abp01-formInfo-bikeTour" type="text/x-kite">', $editor);
        $this->assertContains('<script id="tpl-abp01-formInfo-hikingTour" type="text/x-kite">', $editor);
        $this->assertContains('<script id="tpl-abp01-formInfo-trainRide" type="text/x-kite">', $editor);
        $this->assertContains('<script id="tpl-abp01-formMap-unselected" type="text/x-kite">', $editor);
        $this->assertContains('<script id="tpl-abp01-progress-container" type="text/x-kite">', $editor);
        $this->assertContains('<script id="tpl-abp01-formMap-uploaded" type="text/x-kite">', $editor);
    }

    public function test_canRenderAdminTripSummaryEditorLauncherMetabox() {
        $view = $this->_getInitializedView();
        $data = $this->_getAdminEditorLauncherMetaboxData();

        $launcherMetabox = $view->renderAdminTripSummaryEditorLauncherMetabox($data);

        $this->assertNotEmpty($launcherMetabox);
        $this->_assertAdminViewHelperFunctionsExist();
        $this->_assertAdminEditorLauncherMetaboxBasicStructureCorrect($launcherMetabox);
    }

    private function _assertAdminEditorLauncherMetaboxBasicStructureCorrect($launcherMetabox) {
        $this->assertContains('<div id="abp01-editor-launcher-root">', $launcherMetabox);
        $this->assertContains('<div id="abp01-editor-launcher-status">', $launcherMetabox);
        $this->assertContains('<div id="abp01-editor-launcher-actions">', $launcherMetabox);
    }

    private function _getInitializedView() {
        $view = $this->_getView();
        $view->initView();
        return $view;
    }

    private function _getView() {
        return new Abp01_View();
    }
}