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
/// <reference path="../components/abp01-confirm-dialog-modal.d.ts" />
/// <reference path="./abp01-admin-lookup-management.d.ts" />

(function($) {
	"use strict";
	
	/**
	 * Constants
	 * */
	const DEFAULT_LANG = '_default';
	const LOOKUP_DELETE_INITIAL_REQUEST = '_lookup_delete_initial_request';
	const LOOKUP_DELETE_INUSE_CONFIRMATION = '_lookup_delete_inuse_confirmation';
	const EDIT_FORM_SELECTORS = {
		FORM: '#abp01-edit-lookup-form',
		TITLE_EXTRA: '#abp01-edit-lookup-window-title-extra',
		ITEM_ID_FIELD: '#abp01-lookup-item-id',
		DEFAULT_LABEL_FIELD: '#abp01-edit-item-default-label',
		TRANSLATED_LABEL_CONTAINER: '#abp01-edit-item-translated-label-container',
		TRANSLATED_LABEL_FIELD: '#abp01-edit-item-translated-label'
	};

	var context: WpTripSummaryLookupManagementContext = null;
	
	var genericActionResult: WpTripSummaryAlertInline = null;
	var editorActionResult: WpTripSummaryAlertInline = null;

	var pageToggleBusy: WpTripSummaryBusyToggler = null;
	var editorToggleBusy: WpTripSummaryBusyToggler = null;
	var lookupDataItemEditorModal: WpTripSummaryModal = null;

	var $ctlTypeSelector: JQuery = null;
	var $ctlLangSelector: JQuery = null;
	var $ctlLookupListing: JQuery = null;

	var tplLookupListing = null;

	var currentItems: WpTripSummaryCurrentLookupItemsMap = {};
	var editingItem: WpTripSummaryLookupDataItem = null;

	var confirmDeleteModal: WpTripSummaryConfirmModal = null;
	var lookupDelete = {
		stage: null,
		nonce: null
	};

	function escapeHtml(value): string {
		return (window['lodash'] || window['_'])['escape'](value);
	}

	function getContext(): WpTripSummaryLookupManagementContext {
		return {
			ajaxBaseUrl: window['abp01_ajaxUrl'] || null,

			getLookupNonce: window['abp01_getLookupNonce'] || null,
			addLookupNonce: window['abp01_addLookupNonce'] || null,
			editLookupNonce: window['abp01_editLookupNonce'] || null,
			deleteLookupNonce: window['abp01_deleteLookupNonce'] || null,

			ajaxGetLookupAction: window['abp01_ajaxGetLookupAction'] || null,
			ajaxAddLookupAction: window['abp01_ajaxAddLookupAction'] || null,
			ajaxEditLookupAction: window['abp01_ajaxEditLookupAction'] || null,
			ajaxDeleteLookupAction: window['abp01_ajaxDeleteLookupAction'] || null
		};
	}

	function createPageBusyToggler(): WpTripSummaryBusyToggler {
		return $.abp01.createBusyToggler('#wpwrap', 
			window.abp01LookupMgmtL10n.msgWorking);
	}

	function createEditorBusyToggler(): WpTripSummaryBusyToggler {
		return $.abp01.createBusyToggler('#abp01-edit-lookup-window-content', 
			window.abp01LookupMgmtL10n.msgWorking);
	}

	function getLoadLookupDataUrl(): string {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxGetLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.getLookupNonce)
			.toString();
	}

	function getAddLookupUrl(): string {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxAddLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.addLookupNonce)
			.toString();
	}

	function getEditLookupUrl(): string {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxEditLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.editLookupNonce)
			.toString();
	}

	function getDeleteLookupUrl(): string {	
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxDeleteLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.deleteLookupNonce)
			.toString();
	}

	function hideGenericActionResult(): void {
		genericActionResult.hide(false);
	}

	function displaySuccesfulGenericActionResult(message: string): void {
		genericActionResult.success(message, false);
	}

	function displayFailedGenericActionResult(message: string): void {
		genericActionResult.danger(message, false);
	}

	function hideEditorActionResult(): void {
		editorActionResult.hide(false);
	}

	function displaySuccesfulEditorActionResult(message: string): void {
		editorActionResult.success(message, false);
	}

	function displayFailedEditorActionResult(message: string): void {
		editorActionResult.danger(message, false);
	}

	function beginEditingItem(itemId: number): WpTripSummaryLookupDataItem {
		editingItem = currentItems.hasOwnProperty(itemId) 
			? currentItems[itemId] 
			: null;

		return editingItem;
	}

	function isCurrentlyEditingItem(): boolean {
		return !!editingItem;
	}

	function clearCurrentlyEditedItem(): void {
		editingItem = null;
	}

	function clearLookupDeleteState(): void {
		lookupDelete = {
			stage: null,
			nonce: null
		};
	}

	function clearAllLocalData(): void {
		clearCurrentlyEditedItem();
		clearLookupDeleteState();
		currentItems = {};
		editingItem = null;
	}

	function cleanupLookupItems(): void {
		clearAllLocalData();
		$ctlLookupListing
			.find('tbody')
			.html('');
	}

	function updateLocalItemData(item): void {
		currentItems[item.id] = item;
	}

	function getLookupListingTemplate(): any {
		if (!tplLookupListing) {
			tplLookupListing = $.abp01.kiteTemplate('#tpl-abp01-lookupDataRow');
		}
		return tplLookupListing;
	}

	function renderLookupItems(items, append): void {
		var data = {
			lookupItems: items
		};

		var content = getLookupListingTemplate()(data);

		var $container = $ctlLookupListing.find('tbody');

		if (!append) {
			$container.html(content);
		} else {
			$container.append(content);
		}
	}

	function getCurrentLookupDataItemSelection(): WpTripSummaryCurrentLookupDataItemSelection {
		var typeCode: string = $ctlTypeSelector.singleVal();
		var languageCode: string = $ctlLangSelector.singleVal();

		return {
			type: typeCode,
			language: languageCode,
			typeName: $ctlTypeSelector.optionTextByValue(typeCode),
			languageName: $ctlLangSelector.optionTextByValue(languageCode),
			isDefaultLanguage: languageCode === DEFAULT_LANG
		}
	}

	function reloadLookupItems(): void {
		pageToggleBusy(true);
		hideGenericActionResult();

		var lookupSelection: WpTripSummaryCurrentLookupDataItemSelection =
		 	getCurrentLookupDataItemSelection();

		$.ajax(getLoadLookupDataUrl(), {
			cache: false,
			dataType: 'json',
			type: 'GET',
			data: {
				type: lookupSelection.type,
				lang: lookupSelection.language
			}
		}).done(function(response: WpTripSummaryLookupListingResponse): void {
			pageToggleBusy(false);
			if (!!response && response.success && response.items) {
				cleanupLookupItems();
				renderLookupItems(response.items, false);
				$.each(response.items, function(idx, item) {
					updateLocalItemData(item);
				});
			} else {
				displayFailedGenericActionResult(window.abp01LookupMgmtL10n.errListingFailGeneric);
			}
		}).fail(function() {
			pageToggleBusy(false);
			displayFailedGenericActionResult(window.abp01LookupMgmtL10n.errListingFailNetwork);
		});
	}

	function openLookupDataEditorWindow(id?: number): void {
		if (!id || isNaN(id) || id <= 0) {
			id = 0;
		}

		if (id > 0) {
			beginEditingItem(id);
		}

		var lookupSelection: WpTripSummaryCurrentLookupDataItemSelection = 
			getCurrentLookupDataItemSelection();

		var isEditingNonDefaultLang: boolean = !lookupSelection
			.isDefaultLanguage;

		lookupDataItemEditorModal.findAnd(EDIT_FORM_SELECTORS.TITLE_EXTRA, 
			function($titleExtra: JQuery) {
				$titleExtra.text(" - " + lookupSelection.typeName);
			});

		lookupDataItemEditorModal.findAnd(EDIT_FORM_SELECTORS.ITEM_ID_FIELD, 
			function($id:JQuery) {
				$id.val(id.toString());
			});

		lookupDataItemEditorModal.findAnd(EDIT_FORM_SELECTORS.DEFAULT_LABEL_FIELD, 
			function($defaultLabel: JQuery) {
				if (isCurrentlyEditingItem()) {
					$defaultLabel.val(editingItem.defaultLabel);
				} else {
					$defaultLabel.val('');
				}
			});

		lookupDataItemEditorModal.findAnd(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_CONTAINER, 
			function($translatedLabelContainer: JQuery) {
				var $langDetails: JQuery = $translatedLabelContainer.find('.abp01-languageDetails');
				if (isEditingNonDefaultLang) {
					$translatedLabelContainer.show();
					$langDetails.html('(' + lookupSelection.languageName + ')');
				} else {
					$translatedLabelContainer.hide();
					$langDetails.html('');
				}
			});

		lookupDataItemEditorModal.findAnd(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_FIELD, 
			function($translatedLabel: JQuery) {
				if (isCurrentlyEditingItem() && isEditingNonDefaultLang) {
					$translatedLabel.val(editingItem.label);
				} else {
					$translatedLabel.val('');
				}
			});

		editorActionResult.hide(false);
		lookupDataItemEditorModal.show(); 
	}

	function saveLookupDataItem(): void {	
		var lookupSelection: WpTripSummaryCurrentLookupDataItemSelection = 
			getCurrentLookupDataItemSelection();
		var formDataItem: WpTripSummaryLookupDataItem = 
			collectLookupDataItemFormData(lookupSelection);

		var isEdit: boolean = formDataItem.id > 0;
		
		var url: string = isEdit 
			? getEditLookupUrl() 
			: getAddLookupUrl();

		if (!isValidLookupDataItem(formDataItem, lookupSelection)) {
			editorActionResult.danger(window.abp01LookupMgmtL10n.errSaveFailInvalidData, false);
			return;
		}

		editorToggleBusy(true);
		hideEditorActionResult();

		var sendData: any = {
			id: formDataItem.id,
			type: lookupSelection.type,
			lang: lookupSelection.language,
			defaultLabel: formDataItem.defaultLabel,
			translatedLabel: formDataItem.label
		};

		$.ajax(url, {
			cache: false,
			dataType: 'json',
			type: 'POST',
			data: sendData
		}).done(function(response: WpTripSummaryLookupDataItemSaveResponse): void {
			editorToggleBusy(false);
			if (!!response && !!response.success) {
				if (isCurrentlyEditingItem()) {
					updateLocalItemData(formDataItem);
					refreshLookupItem(formDataItem);
				} else {
					updateLocalItemData(response.item);
					renderLookupItems([response.item], true);
					clearLookupDataItemEditForm();
				}

				displaySuccesfulEditorActionResult(window.abp01LookupMgmtL10n.msgSaveOk);
			} else {
				displayFailedEditorActionResult(response.message || window.abp01LookupMgmtL10n.errFailGeneric);
			}
		}).fail(function() {
			editorToggleBusy(false);
			displayFailedEditorActionResult(window.abp01LookupMgmtL10n.errFailNetwork);
		});
	}

	function clearLookupDataItemEditForm(): void {
		var $form: JQuery = $(EDIT_FORM_SELECTORS.FORM);
		$form.find(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_FIELD).val('');
		$form.find(EDIT_FORM_SELECTORS.DEFAULT_LABEL_FIELD).val('');
	}

	function refreshLookupItem(item: WpTripSummaryLookupDataItem): void {
		var $oldRow: JQuery = $('#lookupItemRow-' + item.id);
		if  ($oldRow.length > 0) {
			var data: any = {
				lookupItems: [item]
			};
			$oldRow.replaceWith(getLookupListingTemplate()(data));
		}
	}

	function collectLookupDataItemFormData(lookupSelection: WpTripSummaryCurrentLookupDataItemSelection): WpTripSummaryLookupDataItem {
		var $form: JQuery = $(EDIT_FORM_SELECTORS.FORM);	
		
		var id: number = $form.find(EDIT_FORM_SELECTORS.ITEM_ID_FIELD)
			.singleValNumeric();

		var defaultLabel: string = $form.find(EDIT_FORM_SELECTORS.DEFAULT_LABEL_FIELD)
			.singleVal()
			.trim();

		var label: string = !lookupSelection.isDefaultLanguage 
			? $form.find(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_FIELD)
				.singleVal()
				.trim()
			: '';

		return {
			id: id,
			type: lookupSelection.type,
			label: label,
			defaultLabel: defaultLabel,
			hasTranslation: label.length > 0
		};
	}

	function isValidLookupDataItem(item: WpTripSummaryLookupDataItem, lookupSelection: WpTripSummaryCurrentLookupDataItemSelection): boolean {
        if ($.abp01.isNullOrWhiteSpace(item.defaultLabel)) {
            return false;
        }

		return lookupSelection.isDefaultLanguage 
			|| !$.abp01.isNullOrWhiteSpace(item.label)	;
    }

	function confirmDeleteLookupDataItem(id: number): void {
		if (!confirmDeleteModal) {
			confirmDeleteModal = $.abp01ConfirmDialogModal();
		}

		confirmDeleteModal.show(window.abp01LookupMgmtL10n.ttlConfirmDelete, function() {
			deleteLookupDataItem(id);
		});
	}

	function deleteLookupDataItem(id: number): void {

	}

	function initContext(): void {
		context = getContext();
	}

	function setupKiteFormatters(): void {
		window.kite.formatters['esc-html'] = function(v, obj) {
			return escapeHtml(v);
		};
	}

	function createGenericActionResultAlert(): WpTripSummaryAlertInline {
		return $('#abp01-generic-action-result').abp01AlertInline({
			dismissible: false
		});
	}

	function createEditorActionResultAlert(): WpTripSummaryAlertInline {
		return $('#abp01-editor-action-result').abp01AlertInline({
			dismissible: false
		});
	}

	function createLookupDataItemEditorModal(): WpTripSummaryModal {
		return $('#abp01-edit-lookup-window').abp01Modal({
			trigger: null,
			onHide: function() {
				clearCurrentlyEditedItem()
			}
		});
	}

	function initControls(): void {
		pageToggleBusy = createPageBusyToggler();
		editorToggleBusy = createEditorBusyToggler();

		$ctlTypeSelector = $('#abp01-lookupTypeSelect');
		$ctlLangSelector = $('#abp01-lookupLangSelect');
		$ctlLookupListing = $('#abp01-admin-lookup-listing');

		genericActionResult = createGenericActionResultAlert();
		editorActionResult = createEditorActionResultAlert();

		lookupDataItemEditorModal = createLookupDataItemEditorModal();
	}

	function initListeners(): void {
		$ctlTypeSelector.on('change', reloadLookupItems);
		$ctlLangSelector.on('change', reloadLookupItems);

		$('#abp01-reload-list-top').on('click', reloadLookupItems);

		$("#abp01-add-lookup-top").on('click', function() {
			openLookupDataEditorWindow(null);
		});

		$(document).on("click", 'a[rel="item-edit"]', function() {
			var $me: JQuery = $(this);
			var id: number = getLookupItemId($me);
			openLookupDataEditorWindow(id);
		});


		$(document).on("click", 'a[rel="item-delete"]', function() {
			var $me: JQuery = $(this);
			var id: number = getLookupItemId($me);
			confirmDeleteLookupDataItem(id);
		});

		$("#abp01-btn-save-lookup-data-item").on('click', saveLookupDataItem);
	}

	function getLookupItemId($target: JQuery): number {
		return parseInt($target.attr("data-lookupId"));
	}

	$(function() {
		initContext();
		setupKiteFormatters();
		initControls();
		initListeners();
		reloadLookupItems();
	});
})(jQuery);