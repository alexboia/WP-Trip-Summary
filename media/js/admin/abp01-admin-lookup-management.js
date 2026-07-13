/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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
/// <reference types="lodash" />
(function ($) {
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
    var context = null;
    var genericActionResult = null;
    var editorActionResult = null;
    var pageToggleBusy = null;
    var editorToggleBusy = null;
    var lookupDataItemEditorModal = null;
    var $ctlTypeSelector = null;
    var $ctlLangSelector = null;
    var $ctlLookupListing = null;
    var tplLookupListing = null;
    var currentItems = {};
    var editingItem = null;
    var confirmDeleteModal = null;
    var lookupDelete = {
        stage: null,
        nonce: null
    };
    function escapeHtml(value) {
        if (!value) {
            return '';
        }
        return window.lodash.escape(value);
    }
    function getContext() {
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
    function createPageBusyToggler() {
        return $.wpTripSummary.createBusyToggler('#wpwrap', window.abp01LookupMgmtL10n.msgWorking);
    }
    function createEditorBusyToggler() {
        return $.wpTripSummary.createBusyToggler('#abp01-edit-lookup-window-content', window.abp01LookupMgmtL10n.msgWorking);
    }
    function _checkContextOrThrow() {
        if (!context) {
            throw new Error('Invalid context');
        }
        return context;
    }
    function _baseUrlOrThrow() {
        return URI(_checkContextOrThrow().ajaxBaseUrl || "");
    }
    function getLoadLookupDataUrl() {
        return _baseUrlOrThrow()
            .addSearch('action', context?.ajaxGetLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context?.getLookupNonce)
            .toString();
    }
    function getAddLookupUrl() {
        return _baseUrlOrThrow()
            .addSearch('action', context?.ajaxAddLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context?.addLookupNonce)
            .toString();
    }
    function getEditLookupUrl() {
        return _baseUrlOrThrow()
            .addSearch('action', context?.ajaxEditLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context?.editLookupNonce)
            .toString();
    }
    function getDeleteLookupUrl() {
        const uri = _baseUrlOrThrow()
            .addSearch('action', context?.ajaxDeleteLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context?.deleteLookupNonce);
        if (isLookupDeleteInuUseConfirmationStage()) {
            uri.addSearch('abp01_nonce_lookup_force_remove', getLookupDeleteInUseConfirmationNonce());
        }
        return uri.toString();
    }
    function hideGenericActionResult() {
        genericActionResult?.hide(false);
    }
    function displaySuccesfulGenericActionResult(message) {
        genericActionResult?.success(message, false);
    }
    function displayFailedGenericActionResult(message) {
        genericActionResult?.danger(message, false);
    }
    function hideEditorActionResult() {
        editorActionResult?.hide(false);
    }
    function displaySuccesfulEditorActionResult(message) {
        editorActionResult?.success(message, false);
    }
    function displayFailedEditorActionResult(message) {
        editorActionResult?.danger(message, false);
    }
    function beginEditingItem(itemId) {
        editingItem = !!currentItems && currentItems.hasOwnProperty(itemId)
            ? currentItems[itemId]
            : null;
        return editingItem;
    }
    function isCurrentlyEditingItem() {
        return !!editingItem;
    }
    function clearCurrentlyEditedItem() {
        editingItem = null;
    }
    function setLookupDeleteInitialRequest() {
        lookupDelete = {
            stage: LOOKUP_DELETE_INITIAL_REQUEST,
            nonce: null
        };
    }
    function setLookupDeleteInUseConfirmation(nonce) {
        lookupDelete = {
            stage: LOOKUP_DELETE_INUSE_CONFIRMATION,
            nonce: nonce
        };
    }
    function isLookupDeleteInuUseConfirmationStage() {
        return lookupDelete.stage === LOOKUP_DELETE_INUSE_CONFIRMATION;
    }
    function getLookupDeleteInUseConfirmationNonce() {
        return lookupDelete.nonce;
    }
    function setCurrentItemDeleted() {
        if (isCurrentlyEditingItem()) {
            if (!!currentItems && !!editingItem) {
                delete currentItems[editingItem.id];
            }
            clearCurrentlyEditedItem();
        }
    }
    function clearLookupDeleteState() {
        lookupDelete = {
            stage: null,
            nonce: null
        };
    }
    function clearAllLocalData() {
        clearCurrentlyEditedItem();
        clearLookupDeleteState();
        currentItems = {};
        editingItem = null;
    }
    function cleanupLookupItems() {
        clearAllLocalData();
        $ctlLookupListing?.find('tbody')
            .html('');
    }
    function updateLocalItemData(item) {
        if (!currentItems) {
            currentItems = {};
        }
        currentItems[item.id] = item;
    }
    function getLookupListingTemplate() {
        if (!tplLookupListing) {
            tplLookupListing = $.abp01.kiteTemplate('#tpl-abp01-lookupDataRow');
        }
        return tplLookupListing;
    }
    function renderLookupItems(items, append) {
        const data = {
            lookupItems: items
        };
        const content = getLookupListingTemplate()(data);
        const $container = $ctlLookupListing?.find('tbody');
        if (!$container || $container.length == 0) {
            return;
        }
        if (!append) {
            $container.html(content);
        }
        else {
            $container.append(content);
        }
    }
    function getCurrentLookupDataItemSelection() {
        const typeCode = $ctlTypeSelector?.singleVal() || null;
        const languageCode = $ctlLangSelector?.singleVal() || null;
        if (!typeCode || !languageCode) {
            return null;
        }
        return {
            type: typeCode,
            language: languageCode,
            typeName: $ctlTypeSelector?.optionTextByValue(typeCode) || "",
            languageName: $ctlLangSelector?.optionTextByValue(languageCode) || "",
            isDefaultLanguage: languageCode === DEFAULT_LANG
        };
    }
    function reloadLookupItems() {
        pageToggleBusy?.(true);
        hideGenericActionResult();
        const lookupSelection = getCurrentLookupDataItemSelection();
        if (!lookupSelection) {
            console.error('Failed to get lookup selection.');
            return;
        }
        $.ajax(getLoadLookupDataUrl(), {
            cache: false,
            dataType: 'json',
            type: 'GET',
            data: {
                type: lookupSelection.type,
                lang: lookupSelection.language
            }
        }).done(function (response) {
            pageToggleBusy?.(false);
            if (!!response && response.success && response.items) {
                cleanupLookupItems();
                renderLookupItems(response.items, false);
                $.each(response.items, function (idx, item) {
                    updateLocalItemData(item);
                });
            }
            else {
                displayFailedGenericActionResult(window.abp01LookupMgmtL10n.errListingFailGeneric);
            }
        }).fail(function () {
            pageToggleBusy?.(false);
            displayFailedGenericActionResult(window.abp01LookupMgmtL10n.errListingFailNetwork);
        });
    }
    function openLookupDataEditorWindow(id) {
        if (!id || isNaN(id) || id <= 0) {
            id = 0;
        }
        if (id > 0) {
            beginEditingItem(id);
        }
        const lookupSelection = getCurrentLookupDataItemSelection();
        if (!lookupSelection) {
            console.error('Failed to get lookup selection.');
            return;
        }
        const isEditingNonDefaultLang = !lookupSelection
            .isDefaultLanguage;
        lookupDataItemEditorModal?.findAnd(EDIT_FORM_SELECTORS.TITLE_EXTRA, function ($titleExtra) {
            $titleExtra.text(" - " + lookupSelection.typeName);
        });
        lookupDataItemEditorModal?.findAnd(EDIT_FORM_SELECTORS.ITEM_ID_FIELD, function ($id) {
            $id.val(id.toString());
        });
        lookupDataItemEditorModal?.findAnd(EDIT_FORM_SELECTORS.DEFAULT_LABEL_FIELD, function ($defaultLabel) {
            if (isCurrentlyEditingItem()) {
                $defaultLabel.val(editingItem?.defaultLabel || "");
            }
            else {
                $defaultLabel.val('');
            }
        });
        lookupDataItemEditorModal?.findAnd(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_CONTAINER, function ($translatedLabelContainer) {
            var $langDetails = $translatedLabelContainer.find('.abp01-languageDetails');
            if (isEditingNonDefaultLang) {
                $translatedLabelContainer.show();
                $langDetails.html('(' + lookupSelection.languageName + ')');
            }
            else {
                $translatedLabelContainer.hide();
                $langDetails.html('');
            }
        });
        lookupDataItemEditorModal?.findAnd(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_FIELD, function ($translatedLabel) {
            if (isCurrentlyEditingItem() && isEditingNonDefaultLang) {
                $translatedLabel.val(editingItem?.label || "");
            }
            else {
                $translatedLabel.val('');
            }
        });
        editorActionResult?.hide(false);
        lookupDataItemEditorModal?.show();
    }
    function saveLookupDataItem() {
        const lookupSelection = getCurrentLookupDataItemSelection();
        if (!lookupSelection) {
            console.error('Failed to get lookup selection.');
            return;
        }
        const formDataItem = collectLookupDataItemFormData(lookupSelection);
        const isEdit = formDataItem.id > 0;
        const url = isEdit
            ? getEditLookupUrl()
            : getAddLookupUrl();
        if (!isValidLookupDataItem(formDataItem, lookupSelection)) {
            editorActionResult?.danger(window.abp01LookupMgmtL10n.errSaveFailInvalidData, false);
            return;
        }
        editorToggleBusy?.(true);
        hideEditorActionResult();
        const sendData = {
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
        }).done(function (response) {
            editorToggleBusy?.(false);
            if (!!response && !!response.success) {
                if (isCurrentlyEditingItem()) {
                    updateLocalItemData(formDataItem);
                    refreshLookupItem(formDataItem);
                }
                else {
                    updateLocalItemData(response.item || formDataItem);
                    renderLookupItems([response.item || formDataItem], true);
                    clearLookupDataItemEditForm();
                }
                displaySuccesfulEditorActionResult(window.abp01LookupMgmtL10n.msgSaveOk);
            }
            else {
                displayFailedEditorActionResult(response.message || window.abp01LookupMgmtL10n.errFailGeneric);
            }
        }).fail(function () {
            editorToggleBusy?.(false);
            displayFailedEditorActionResult(window.abp01LookupMgmtL10n.errFailNetwork);
        });
    }
    function clearLookupDataItemEditForm() {
        var $form = $(EDIT_FORM_SELECTORS.FORM);
        $form.find(EDIT_FORM_SELECTORS.TRANSLATED_LABEL_FIELD).val('');
        $form.find(EDIT_FORM_SELECTORS.DEFAULT_LABEL_FIELD).val('');
    }
    function refreshLookupItem(item) {
        var $oldRow = $('#lookupItemRow-' + item.id);
        if ($oldRow.length > 0) {
            var data = {
                lookupItems: [item]
            };
            $oldRow.replaceWith(getLookupListingTemplate()(data));
        }
    }
    function collectLookupDataItemFormData(lookupSelection) {
        const $form = $(EDIT_FORM_SELECTORS.FORM);
        const id = $form.find(EDIT_FORM_SELECTORS.ITEM_ID_FIELD)
            .singleValNumeric();
        const defaultLabel = $form.find(EDIT_FORM_SELECTORS.DEFAULT_LABEL_FIELD)
            .singleVal()
            .trim();
        const label = !lookupSelection.isDefaultLanguage
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
    function isValidLookupDataItem(item, lookupSelection) {
        if ($.abp01.isNullOrWhiteSpace(item.defaultLabel)) {
            return false;
        }
        return lookupSelection.isDefaultLanguage
            || !$.abp01.isNullOrWhiteSpace(item.label);
    }
    function confirmDeleteLookupDataItem(itemId) {
        beginEditingItem(itemId);
        setLookupDeleteInitialRequest();
        _showConfrmDeleteModal(itemId, window.abp01LookupMgmtL10n.ttlConfirmDelete);
    }
    function _showConfrmDeleteModal(itemId, message) {
        if (!confirmDeleteModal) {
            confirmDeleteModal = $.abp01ConfirmDialogModal();
        }
        confirmDeleteModal.show(message, function (confirmed) {
            if (confirmed) {
                queueMicrotask(() => deleteLookupDataItem(itemId));
            }
        });
    }
    function deleteLookupDataItem(itemId) {
        pageToggleBusy?.(true);
        hideGenericActionResult();
        const lookupSelection = getCurrentLookupDataItemSelection();
        if (!lookupSelection) {
            console.error('Failed to get lookup selection.');
            return;
        }
        $.ajax(getDeleteLookupUrl(), {
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: {
                id: itemId,
                lang: lookupSelection.language,
                deleteOnlyLang: false
            }
        })
            .done(function (response) {
            pageToggleBusy?.(false);
            //Successful removal, move on with cleaning everything up
            if (!!response.success) {
                deleteLookupItemRow(editingItem);
                setCurrentItemDeleted();
                displaySuccesfulGenericActionResult(window.abp01LookupMgmtL10n.msgDeleteOk);
            }
            else if (!!response.requiresConfirmation && !!response.confirmationNonce) {
                setLookupDeleteInUseConfirmation(response.confirmationNonce);
                queueMicrotask(() => _showConfrmDeleteModal(itemId, response.message));
            }
            else {
                displayFailedGenericActionResult(response.message
                    || window.abp01LookupMgmtL10n.errDeleteFailedGeneric);
            }
        })
            .fail(function () {
            pageToggleBusy?.(false);
            displayFailedGenericActionResult(window.abp01LookupMgmtL10n.errDeleteFailedNetwork);
        });
    }
    function deleteLookupItemRow(item) {
        if (!item) {
            return;
        }
        const $row = $('#lookupItemRow-' + item.id);
        $row.remove();
    }
    function initContext() {
        context = getContext();
    }
    function setupKiteFormatters() {
        window.kite.formatters['esc-html'] = function (v, obj) {
            return escapeHtml(v);
        };
    }
    function createGenericActionResultAlert() {
        return $('#abp01-generic-action-result').abp01AlertInline({
            dismissible: false
        });
    }
    function createEditorActionResultAlert() {
        return $('#abp01-editor-action-result').abp01AlertInline({
            dismissible: false
        });
    }
    function createLookupDataItemEditorModal() {
        return $('#abp01-edit-lookup-window').abp01Modal({
            trigger: null,
            onHide: function () {
                clearCurrentlyEditedItem();
            }
        });
    }
    function initControls() {
        pageToggleBusy = createPageBusyToggler();
        editorToggleBusy = createEditorBusyToggler();
        $ctlTypeSelector = $('#abp01-lookupTypeSelect');
        $ctlLangSelector = $('#abp01-lookupLangSelect');
        $ctlLookupListing = $('#abp01-admin-lookup-listing');
        genericActionResult = createGenericActionResultAlert();
        editorActionResult = createEditorActionResultAlert();
        lookupDataItemEditorModal = createLookupDataItemEditorModal();
    }
    function initListeners() {
        $ctlTypeSelector?.on('change', reloadLookupItems);
        $ctlLangSelector?.on('change', reloadLookupItems);
        $('#abp01-reload-list-top').on('click', reloadLookupItems);
        $("#abp01-add-lookup-top").on('click', function () {
            openLookupDataEditorWindow(null);
        });
        $(document).on("click", 'a[rel="item-edit"]', function () {
            const $me = $(this);
            const id = getLookupItemId($me);
            openLookupDataEditorWindow(id);
        });
        $(document).on("click", 'a[rel="item-delete"]', function () {
            const $me = $(this);
            const id = getLookupItemId($me);
            confirmDeleteLookupDataItem(id);
        });
        $("#abp01-btn-save-lookup-data-item").on('click', saveLookupDataItem);
    }
    function getLookupItemId($target) {
        return parseInt($target.attr("data-lookupId") || "0");
    }
    $(function () {
        initContext();
        setupKiteFormatters();
        initControls();
        initListeners();
        reloadLookupItems();
    });
})(jQuery);
