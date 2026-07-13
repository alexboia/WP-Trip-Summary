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
/// <reference path="./abp01-admin-listing-audit-log.d.ts" />
(function ($) {
    "use strict";
    const WINDOW_SELCTORS = {
        CONTENT_CONTAINER: "#abp01-audit-log-container-inner"
    };
    let context = null;
    let pageToggleBusy = null;
    let listingAuditLogModal = null;
    function getContext() {
        return {
            ajaxBaseUrl: window['abp01_auditLogAjaxBaseUrl'] || null,
            auditLogNonce: window['abp01_auditLogNonce'] || null,
            auditLogAjaxAction: window['abp01_auditLogAjaxAction'] || null
        };
    }
    function getAuditLogForPostUrl(postId) {
        return _baseUrlOrThrow()
            .addSearch('action', context?.auditLogAjaxAction)
            .addSearch('abp01_nonce', context?.auditLogNonce)
            .addSearch('abp01_postId', postId)
            .toString();
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
    function loadAuditLogContents(postId, onReady) {
        pageToggleBusy?.(true);
        $.ajax(getAuditLogForPostUrl(postId), {
            cache: false,
            dataType: 'html',
            type: 'GET'
        }).done(function (data) {
            pageToggleBusy?.(false);
            onReady(true, data);
        }).fail(function () {
            pageToggleBusy?.(false);
            onReady(false, null);
        });
    }
    function displayAuditLog(postId) {
        loadAuditLogContents(postId, function (success, contents) {
            if (success && !!contents) {
                openAuditLogViewerOverlayDelayed(contents);
            }
            else {
                alert(window.abp01ListingAuditLogL10n.errFailedToLoadAuditLog);
            }
        });
    }
    function openAuditLogViewerOverlayDelayed(contents) {
        window.setTimeout(function () {
            openAuditLogViewerOverlay(contents);
        }, 250);
    }
    function openAuditLogViewerOverlay(contents) {
        _updateDialogContents(contents);
        listingAuditLogModal?.show();
    }
    function handleAuditLogLinkClicked(event) {
        event.preventDefault();
        event.stopPropagation();
        const postId = parseInt($(this).attr('data-post') || '');
        if (!isNaN(postId)) {
            displayAuditLog(postId);
        }
    }
    function _updateDialogContents(content) {
        listingAuditLogModal?.findAnd(WINDOW_SELCTORS.CONTENT_CONTAINER, function ($container) {
            $container.html(content);
        });
    }
    function createPageBusyToggler() {
        return $.wpTripSummary.createBusyToggler('#wpwrap', window.abp01ListingAuditLogL10n.msgWorking);
    }
    function createListingAuditLogEditorModal() {
        return $('#abp01-listing-audit-log-window').abp01Modal({
            trigger: null,
            onHide: function () {
                _updateDialogContents("");
            }
        });
    }
    function initContext() {
        context = getContext();
    }
    function initControls() {
        pageToggleBusy = createPageBusyToggler();
        listingAuditLogModal = createListingAuditLogEditorModal();
    }
    function initEvents() {
        $(document).on('click', 'a.abp01-admin-listing-audit-log-link', handleAuditLogLinkClicked);
        $(document).on('click', '#abp01-btn-close-listing-audit-log-window', function () {
            listingAuditLogModal?.hide();
        });
    }
    $(function () {
        initContext();
        initControls();
        initEvents();
    });
})(jQuery);
