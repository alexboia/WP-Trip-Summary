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

(function ($) {
	"use strict";

	var progressBar = null;
	var $ctrlSettingsForm = null;
	var $ctrlSettingsSaveResult = null;
	var $ctrlTileLayerApiKeyNag = null;

	var tplPredefinedTileLayerSelector = null;
	var contentPredefinedTileLayersSelector = null;

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

	function predefinedTileLayersToArray(predefinedTileLayersObj) {
		var predefinedTileLayers = [];
		for (var layerId in predefinedTileLayersObj) {
			if (predefinedTileLayersObj.hasOwnProperty(layerId)) {
				predefinedTileLayers.push(predefinedTileLayersObj[layerId]);
			}
		}
		return predefinedTileLayers;
	}

	function scrollToTop() {
		window.abp01.scrollToTop();
	}

	function disableWindowScroll() {
		window.abp01.disableWindowScroll();
	}

	function enableWindowScroll() {
		window.abp01.enableWindowScroll();
	}

	function displaySuccessfulOperationMessage(message) {
		$ctrlSettingsSaveResult.abp01OperationMessage('success', message);
	}

	function displayFailedOperationMessage(message) {
		$ctrlSettingsSaveResult.abp01OperationMessage('error', message);
	}

	function hideOperationMessage() {
		$ctrlSettingsSaveResult.abp01OperationMessage('hide');
	}

	function toggleBusy(show) {
		if (show) {
			if (progressBar == null) {
				progressBar = $('#tpl-abp01-progress-container').progressOverlay({
					$target: $('#wpwrap'),
					message: abp01SettingsL10n.msgSaveWorking
				});
			}
		} else {
			if (progressBar != null) {
				progressBar.destroy();
				progressBar = null;
			}
		}
	}

	function getPredefinedTileLayersSelectorTemplate() {
		if (tplPredefinedTileLayerSelector === null) {
			tplPredefinedTileLayerSelector = kite('#tpl-abp01-predefined-tile-layers-container');
		}
		return tplPredefinedTileLayerSelector;
	}

	function getPredefinedTileLayersSelectorContent() {
		if (contentPredefinedTileLayersSelector === null) {
			var template = getPredefinedTileLayersSelectorTemplate();
			var predefinedTileLayers = predefinedTileLayersToArray(context.predefinedTileLayers);

			contentPredefinedTileLayersSelector = template({
				predefinedTileLayers: predefinedTileLayers
			});
		}

		return contentPredefinedTileLayersSelector;
	}

	function getPreviouslySavedTileLayer() {
		return context.previouslySavedTileLayer;
	}

	function updatePreviouslySavedTileLayerFromInputFields() {
		context.previouslySavedTileLayer = getCurrentTileLayerInfoFromInputFields();
	}

	function getCurrentTileLayerInfoFromInputFields() {
		return {
			url: $('#abp01-tileLayerUrl')
				.val(),
			attributionTxt: $('#abp01-tileLayerAttributionTxt')
				.val(),
			attributionUrl: $('#abp01-tileLayerAttributionUrl')
				.val(),
			apiKey: $('#abp01-tileLayerApiKey')
				.val()
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

		$('#abp01-tileLayerUrl').change();
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
				displaySuccessfulOperationMessage(abp01SettingsL10n.msgSaveOk);
			} else {
				displayFailedOperationMessage(data.message || abp01SettingsL10n.errSaveFailGeneric);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			displayFailedOperationMessage(abp01SettingsL10n.errSaveFailNetwork);
		});
	}

	function openPredefinedTileLayerSelector() {
		$.blockUI({
			message: $(getPredefinedTileLayersSelectorContent()),
			css: {
				width: '620px',
				height: '300px',
				top: 'calc(50% - 250px)',
				left: 'calc(50% - 320px)',
				padding: '10px',
				borderRadius: '5px',
				backgroundColor: '#fff',
				boxShadow: '0 5px 15px rgba(0, 0, 0, 0.7)'
			},
			onBlock: function() {
				disableWindowScroll();
			},
			onUnblock: function() {
				enableWindowScroll();
			}
		});
	}

	function closePredefinedTileLayerSelector() {
		$.unblockUI();
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
			Tipped.create('#abp01-tileLayer-apiKey-nag', $ctrlTileLayerApiKeyNag.attr('data-nag-text'), {
				position: 'right'
			});
		}
	}

	function updateApiKeyNag() {
		var tileLayer = getCurrentTileLayerInfoFromInputFields();
		if (needsApiKey(tileLayer)) {
			showApiKeyNag();
		} else {
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
		$ctrlSettingsSaveResult = $('#abp01-settings-save-result');
		$ctrlTileLayerApiKeyNag = $('#abp01-tileLayer-apiKey-nag');
	}

	function initBlockUIDefaultStyles() {
		$.blockUI.defaults.css = {
			width: '100%',
			height: '100%'
		};
	}

	function initListeners() {
		$('.apb01-settings-save-btn')
			.click(saveSettings);
		$('#abp01-predefined-tile-layer-selector')
			.click(openPredefinedTileLayerSelector);

		$('#abp01-tileLayerUrl')
			.change(updateApiKeyNag);
		$('#abp01-tileLayerApiKey')
			.change(updateApiKeyNag);

		$(document).on('click', '.abp01-close-tile-layer-selector', 
			closePredefinedTileLayerSelector);
		$(document).on('click', '.abp01-use-tile-layer', 
			selectPreDefinedTileLayer);
	}

	function initColoPickers() {
		$('#abp01-trackLineColour').wpColorPicker();
	}

	function initTrackLineWeightStepper() {
		var $ctrlTrackLineWeight = $('#abp01-trackLineWeight');
		var minLineWeight = parseInt($ctrlTrackLineWeight.attr('data-min-line-weight'));

		$ctrlTrackLineWeight.abp01NumericStepper({
			minValue: minLineWeight,
			maxValue: 10,
			defaultValue: 3
		});
	}

	function initMapHeightStepper() {
		var $ctrlMapHeight = $('#abp01-mapHeight');
		var minMapHeight = parseInt($ctrlMapHeight.attr('data-min-map-height'));

		$ctrlMapHeight.abp01NumericStepper({
			minValue: minMapHeight,
			maxValue: 1000,
			defaultValue: minMapHeight,
			increment: 10
		});
	}

	function initViewerItemValueDisplayCountStepper() {
		var $ctrlCiewerItemValueDisplayCount = $('#abp01-viewerItemValueDisplayCount');
		var minViewerItemValueDisplayCount = parseInt($ctrlCiewerItemValueDisplayCount.attr('data-min-viewer-item-value-display-count'));

		$ctrlCiewerItemValueDisplayCount.abp01NumericStepper({
			minValue: minViewerItemValueDisplayCount,
			maxValue: 100,
			defaultValue: minViewerItemValueDisplayCount,
			increment: 1
		});
	}

	$(document).ready(function() {
		initFormState();
		initControls();
		initBlockUIDefaultStyles();
		initColoPickers();
		initTrackLineWeightStepper();
		initMapHeightStepper();
		initViewerItemValueDisplayCountStepper();
		initListeners();
		updateApiKeyNag();
	});
})(jQuery);