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

	var context: WpTripSummarySettingsContext|null = null;

	var $ctrlSettingsForm: JQuery|null = null;
	var $ctrlTileLayerApiKeyNag: JQuery|null = null;

	var settingsSaveResult: WpTripSummaryAlertInline|null = null;
	var predefinedTileLayersModal: WpTripSummaryModal|null = null;

	var toggleBusy: WpTripSummaryBusyToggler|null = null;

	function getContext(): WpTripSummarySettingsContext {
		return {
			apiKeyNagSetup: false,
			nonce: window['abp01_nonce'] || null,
			ajaxSaveAction: window['abp01_ajaxSaveAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null,
			predefinedTileLayers: window['abp01_predefinedTileLayers'] || {},
			previouslySavedTileLayer: null
		};
	}

	function scrollToTop(): void {
		window.abp01.scrollToTop();
	}

	function displaySuccessfulOperationMessage(message: string): void {
		settingsSaveResult?.success(message, false);
	}

	function displayFailedOperationMessage(message: string): void {
		settingsSaveResult?.danger(message, false);
	}

	function hideOperationMessage(): void {
		settingsSaveResult?.hide(false);
	}

	function updatePreviouslySavedTileLayerFromInputFields(): void {
		if (!!context) {
			context.previouslySavedTileLayer = getCurrentTileLayerInfoFromInputFields();
		}
	}

	function getCurrentTileLayerInfoFromInputFields():WpTripSummaryTileLayer {
		return {
			url: ($('#abp01-tileLayerUrl').val() || '').toString(),
			attributionTxt: ($('#abp01-tileLayerAttributionTxt').val() || '').toString(),
			attributionUrl: ($('#abp01-tileLayerAttributionUrl').val() || '').toString(),
			apiKey: ($('#abp01-tileLayerApiKey').val() || '').toString()
		};
	}

	function updateInputFieldsWithTileLayerInfo(tileLayer: WpTripSummaryTileLayer): void {
		$('#abp01-tileLayerUrl').val(tileLayer.url);
		$('#abp01-tileLayerApiKey').val('');

		$('#abp01-tileLayerAttributionTxt').val(tileLayer.attributionTxt);
		$('#abp01-tileLayerAttributionUrl').val(tileLayer.attributionUrl);

		$('#abp01-tileLayerUrl').trigger('change');
	}

	function _checkContextOrThrow(): WpTripSummarySettingsContext {
		if (!context) {
			throw new Error('Invalid context');
		}
		return context;
	}

	function _baseUrlOrThrow(): URI {
		return URI(_checkContextOrThrow().ajaxBaseUrl || "");
	}

	function getFormSaveUrl(): string {
		return _baseUrlOrThrow()
			.addSearch('action', context?.ajaxSaveAction)
			.addSearch('abp01_nonce_settings', context?.nonce)
			.toString();
	}

	function saveSettings(): void {
		scrollToTop();
		toggleBusy?.(true);
		hideOperationMessage();
		
		$.ajax(getFormSaveUrl(), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: $ctrlSettingsForm?.serialize()
		}).done(function (data, status, xhr) {
			toggleBusy?.(false);
			if (data && data.success) {
				updatePreviouslySavedTileLayerFromInputFields();
				displaySuccessfulOperationMessage(window.abp01SettingsL10n.msgSaveOk);
			} else {
				displayFailedOperationMessage(data.message || window.abp01SettingsL10n.errSaveFailGeneric);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy?.(false);
			displayFailedOperationMessage(window.abp01SettingsL10n.errSaveFailNetwork);
		});
	}

	function closePredefinedTileLayerSelector(): void {
		predefinedTileLayersModal?.hide();
	}

	function selectPreDefinedTileLayer(this: HTMLElement): void {
		const $me: JQuery = $(this);
		const layerId: string|null = $me.attr('data-predefined-tile-layer-id') || null;

		const predefinedTileLayer: any = !!layerId 
			? context?.predefinedTileLayers[layerId] || null
			: null;

		if (predefinedTileLayer) {
			updatePreviouslySavedTileLayerFromInputFields();
			updateInputFieldsWithTileLayerInfo(predefinedTileLayer.tileLayerObject);
		}
		closePredefinedTileLayerSelector();
	}

	function initApiKeyNag(): void {
		if (!!context && !context.apiKeyNagSetup) {
			context.apiKeyNagSetup = true;
		}
	}

	function updateApiKeyNag(): void {
		const tileLayer: WpTripSummaryTileLayer = getCurrentTileLayerInfoFromInputFields();
		if (needsApiKey(tileLayer)) {
			showApiKeyNag();
		} else {
			hideApiKeyNag();
		}
	}

	function needsApiKey(tileLayer: WpTripSummaryTileLayer): boolean {
		let needsApiKey: boolean = false;
		if (tileLayer.url.indexOf('{apiKey}') >= 0) {
			needsApiKey = !tileLayer.apiKey || tileLayer.apiKey.length <= 0;
		}
		return needsApiKey;
	}

	function showApiKeyNag(): void {
		initApiKeyNag();
		$ctrlTileLayerApiKeyNag?.show();
	}

	function hideApiKeyNag(): void {
		$ctrlTileLayerApiKeyNag?.hide();
	}

	function initFormState(): void {
		context = getContext();
	}

	function initControls(): void {
		$ctrlSettingsForm = $('#abp01-settings-form');
		$ctrlTileLayerApiKeyNag = $('#abp01-tileLayer-apiKey-nag');

		toggleBusy = createBusyToggler();
		settingsSaveResult = createSaveResultAlert();
		predefinedTileLayersModal = createPredefinedTileLayersModal();

		initTooltips();
		initColorPickers();
	}

	function createBusyToggler(): WpTripSummaryBusyToggler {
		return $.abp01.createBusyToggler('#wpwrap', window.abp01SettingsL10n.msgSaveWorking);
	}

	function createSaveResultAlert(): WpTripSummaryAlertInline {
		return $('#abp01-settings-action-result').abp01AlertInline({
			dismissible: false
		});
	}

	function createPredefinedTileLayersModal(): WpTripSummaryModal {
		return $('#abp01-predefined-tile-layers-window').abp01Modal({
			trigger: '#abp01-predefined-tile-layer-selector'
		});
	}

	function initTooltips(): void {
		$.abp01.initTooltipsOnPage('#abp01-settings-page');
	}

	function initListeners(): void {
		$('.apb01-settings-save-btn')
			.on('click', saveSettings);
		$('#abp01-tileLayerUrl')
			.on('change', updateApiKeyNag);
		$('#abp01-tileLayerApiKey')
			.on('change', updateApiKeyNag);

		$(document).on('click', '.abp01-close-tile-layer-selector', 
			closePredefinedTileLayerSelector);
		$(document).on('click', '.abp01-use-tile-layer', 
			selectPreDefinedTileLayer);
	}

	function initColorPickers(): void {
		$('#abp01-trackLineColour').wpColorPicker();
	}

	$(function() {
		initFormState();
		initControls();
		initListeners();
		updateApiKeyNag();
	});
})(jQuery);