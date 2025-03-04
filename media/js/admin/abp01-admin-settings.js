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
/// <reference types="jquery" />
/// <reference types="urijs" />
/// <reference path="../abp01-common.d.ts" />
/// <reference path="../components/abp01-alert-inline.d.ts" />
/// <reference path="../components/abp01-progress-modal.d.ts" />
/// <reference path="./abp01-admin-settings.d.ts" />
(function ($) {
    "use strict";
    var $ctrlSettingsForm = null;
    var $ctrlTileLayerApiKeyNag = null;
    var progressBar = null;
    var settingsSaveResult = null;
    var predefinedTileLayersModal = null;
    var context = null;
    function getContext() {
        return {
            apiKeyNagSetup: false,
            nonce: window['abp01_nonce'] || null,
            ajaxSaveAction: window['abp01_ajaxSaveAction'] || null,
            ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null,
            predefinedTileLayers: window['abp01_predefinedTileLayers'] || {},
            previouslySavedTileLayer: null
        };
    }
    function scrollToTop() {
        window.abp01.scrollToTop();
    }
    function displaySuccessfulOperationMessage(message) {
        settingsSaveResult.success(message, false);
    }
    function displayFailedOperationMessage(message) {
        settingsSaveResult.danger(message, false);
    }
    function hideOperationMessage() {
        settingsSaveResult.hide(false);
    }
    function toggleBusy(show) {
        if (show) {
            if (progressBar == null) {
                progressBar = $('#wpwrap').abp01ProgressModal({});
            }
            progressBar.show(window.abp01SettingsL10n.msgSaveWorking || 'Please wait');
        }
        else {
            if (progressBar != null) {
                progressBar.hide();
            }
        }
    }
    function updatePreviouslySavedTileLayerFromInputFields() {
        context.previouslySavedTileLayer = getCurrentTileLayerInfoFromInputFields();
    }
    function getCurrentTileLayerInfoFromInputFields() {
        return {
            url: $('#abp01-tileLayerUrl').val().toString(),
            attributionTxt: $('#abp01-tileLayerAttributionTxt').val().toString(),
            attributionUrl: $('#abp01-tileLayerAttributionUrl').val().toString(),
            apiKey: $('#abp01-tileLayerApiKey').val().toString()
        };
    }
    function updateInputFieldsWithTileLayerInfo(tileLayer) {
        $('#abp01-tileLayerUrl')
            .val(tileLayer.url);
        $('#abp01-tileLayerAttributionTxt')
            .val(tileLayer.attributionTxt);
        $('#abp01-tileLayerAttributionUrl')
            .val(tileLayer.attributionUrl);
        $('#abp01-tileLayerApiKey')
            .val('');
        $('#abp01-tileLayerUrl').trigger('change');
    }
    function getFormSaveUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxSaveAction)
            .addSearch('abp01_nonce_settings', context.nonce)
            .toString();
    }
    function saveSettings() {
        scrollToTop();
        toggleBusy(true);
        hideOperationMessage();
        $.ajax(getFormSaveUrl(), {
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: $ctrlSettingsForm.serialize()
        }).done(function (data, status, xhr) {
            toggleBusy(false);
            if (data && data.success) {
                updatePreviouslySavedTileLayerFromInputFields();
                displaySuccessfulOperationMessage(window.abp01SettingsL10n.msgSaveOk);
            }
            else {
                displayFailedOperationMessage(data.message || window.abp01SettingsL10n.errSaveFailGeneric);
            }
        }).fail(function (xhr, status, error) {
            toggleBusy(false);
            displayFailedOperationMessage(window.abp01SettingsL10n.errSaveFailNetwork);
        });
    }
    function closePredefinedTileLayerSelector() {
        predefinedTileLayersModal.hide();
    }
    function selectPreDefinedTileLayer() {
        var $me = $(this);
        var layerId = $me.attr('data-predefined-tile-layer-id');
        var predefinedTileLayer = context.predefinedTileLayers[layerId] || null;
        if (predefinedTileLayer) {
            updatePreviouslySavedTileLayerFromInputFields();
            updateInputFieldsWithTileLayerInfo(predefinedTileLayer.tileLayerObject);
        }
        closePredefinedTileLayerSelector();
    }
    function initApiKeyNag() {
        if (!context.apiKeyNagSetup) {
            context.apiKeyNagSetup = true;
        }
    }
    function updateApiKeyNag() {
        var tileLayer = getCurrentTileLayerInfoFromInputFields();
        if (needsApiKey(tileLayer)) {
            showApiKeyNag();
        }
        else {
            hideApiKeyNag();
        }
    }
    function needsApiKey(tileLayer) {
        var needsApiKey = false;
        if (tileLayer.url.indexOf('{apiKey}') >= 0) {
            needsApiKey = !tileLayer.apiKey || tileLayer.apiKey.length <= 0;
        }
        return needsApiKey;
    }
    function showApiKeyNag() {
        initApiKeyNag();
        $ctrlTileLayerApiKeyNag.show();
    }
    function hideApiKeyNag() {
        $ctrlTileLayerApiKeyNag.hide();
    }
    function initFormState() {
        context = getContext();
    }
    function initControls() {
        $ctrlSettingsForm = $('#abp01-settings-form');
        $ctrlTileLayerApiKeyNag = $('#abp01-tileLayer-apiKey-nag');
        settingsSaveResult = $('#abp01-settings-action-result').abp01AlertInline({
            dismissible: false
        });
        predefinedTileLayersModal = $('#abp01-predefined-tile-layers-window').abp01Modal({
            trigger: '#abp01-predefined-tile-layer-selector'
        });
        $.abp01.initTooltipsOnPage('#abp01-settings-page');
        initColorPickers();
    }
    function initListeners() {
        $('.apb01-settings-save-btn')
            .on('click', saveSettings);
        $('#abp01-tileLayerUrl')
            .on('change', updateApiKeyNag);
        $('#abp01-tileLayerApiKey')
            .on('change', updateApiKeyNag);
        $(document).on('click', '.abp01-close-tile-layer-selector', closePredefinedTileLayerSelector);
        $(document).on('click', '.abp01-use-tile-layer', selectPreDefinedTileLayer);
    }
    function initColorPickers() {
        $('#abp01-trackLineColour').wpColorPicker();
    }
    $(function () {
        initFormState();
        initControls();
        initListeners();
        updateApiKeyNag();
    });
})(jQuery);
