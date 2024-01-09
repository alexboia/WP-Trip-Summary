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

 (function($) {
	"use strict";

	var context = null;
	var progressBar = null;

	var tplAuditLogWindowContent = null;

	function disableWindowScroll() {
		window.abp01.disableWindowScroll();
	}

	function enableWindowScroll() {
		window.abp01.enableWindowScroll();
	}

	function getContext() {
		return {
			auditLogAjaxBaseUrl: window['abp01_auditLogAjaxBaseUrl'] || null,
			auditLogNonce: window['abp01_auditLogNonce'] || null,
			auditLogAjaxAction: window['abp01_auditLogAjaxAction'] || null
		};
	}

	function getAuditLogForPostUrl(postId) {
		return URI(context.auditLogAjaxBaseUrl)
			.addSearch('action', context.auditLogAjaxAction)
			.addSearch('abp01_nonce', context.auditLogNonce)
			.addSearch('abp01_postId', postId)
			.toString();
	}

	function toggleBusy(show) {
		if (show) {
			if (progressBar == null) {
				progressBar = $('#tpl-abp01-progress-container').progressOverlay({
					$target: $('#wpwrap'),
					message: abp01ListingAuditLogL10n.msgWorking
				});
			}
		} else {
			if (progressBar != null) {
				progressBar.destroy();
				progressBar = null;
			}
		}
	}

	function getAuditLogWindowContentTemplate() {
		if (tplAuditLogWindowContent === null) {
			tplAuditLogWindowContent = kite('#tpl-abp01-audit-log-container');
		}
		return tplAuditLogWindowContent;
	}

	function renderAuditLogWindowContents(auditLogContent) {
		var template = getAuditLogWindowContentTemplate();
		return template({
			auditLogContent: auditLogContent
		});
	}

	function loadAuditLogContents(postId, onReady) {
		toggleBusy(true);
		$.ajax(getAuditLogForPostUrl(postId), {
			cache: false,
			dataType: 'html',
			type: 'GET'
		}).done(function(data) {
			toggleBusy(false);
			onReady(true, data);
		}).fail(function() {
			toggleBusy(false);
			onReady(false, null);
		});
	}

	function displayAuditLog(postId) {
		loadAuditLogContents(postId, function(success, contents) {
			if (success) {
				var windowContents = renderAuditLogWindowContents(contents);
				openAuditLogViewerOverlayDelayed(windowContents);
			} else {
				alert(abp01ListingAuditLogL10n.errFailedToLoadAuditLog);
			}
		});
	}

	function openAuditLogViewerOverlayDelayed(contents) {
		window.setTimeout(function() {
			openAuditLogViewerOverlay(contents);
		}, 250);
	}

	function openAuditLogViewerOverlay(contents) {
		$.blockUI({
			message: contents,
			css: {
				width: '620px',
				height: '370px',
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

	function closeAuditLogViewerOverlay() {
		$.unblockUI();
	}

	function handleAuditLogLinkClicked(event) {
		event.preventDefault();
		event.stopPropagation();

		var postId = parseInt($(this).attr('data-post'));
		if (!isNaN(postId)) {
			displayAuditLog(postId);
		}
	}

	function initBlockUIDefaultStyles() {
		$.blockUI.defaults.css = {
			width: '100%',
			height: '100%'
		};
	}

	function initContext() {
		context = getContext();
	}

	function initEvents() {
		$('a.abp01-admin-listing-audit-log-link').on('click', 
			handleAuditLogLinkClicked);
		$(document).on('click', '.abp01-close-window', 
			closeAuditLogViewerOverlay);
	}

	$(document).ready(function() {
		initBlockUIDefaultStyles();
		initContext();
		initEvents();
	});
 })(jQuery);