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
/// <reference path="./abp01-admin-maintenance.d.ts" />

(function($) {
	"use strict";

	var context: WpTripSummaryMaintenanceContext = null;
	var progressBar: WpTripSummaryProgressModal = null;
	var toolResult: WpTripSummaryAlertInline = null;
	var toolResultPlaceholder: string = null;
	var confirmExecutionModal: WpTripSummaryConfirmModal = null;

	function getContext(): WpTripSummaryMaintenanceContext {
		return {
			nonce: window['abp01_nonce'] || null,
			ajaxExecuteToolAction: window['abp01_ajaxExecuteToolAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null
		};
	}

	function hideExecutionResultContent(): void {
		$('#abp01-admin-maintenance-result-container-inner')
			.html(toolResultPlaceholder)
			.hide();
	}

	function showExecutionResultContent(content): void {
		$('#abp01-admin-maintenance-result-container-inner')
			.html(content)
			.show();
	}

	function toggleBusy(show: boolean): void {
		if (show) {
			if (progressBar == null) {
				progressBar = $('#wpwrap').abp01ProgressModal({});
			}

			progressBar.show(window.abp01MaintenanceL10n.msgWorking || 'Please wait');
		} else {
			if (progressBar != null) {
				progressBar.hide();
			}
		}
	}

	function displaySuccessfulOperationMessage(message: string): void {
		toolResult.success(message, false);
	}

	function displayFailedOperationMessage(message: string): void {
		toolResult.danger(message, false);
	}

	function hideOperationMessage(): void {
		toolResult.hide(false);
	}

	function getExecuteToolUrl(toolId: string): string {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxExecuteToolAction)
			.addSearch('abp01_nonce_execute_tool', context.nonce)
			.addSearch('abp01_tool_id', toolId)
			.toString();
	}

	function executeSelectedTool(): void {
		var toolId: string = getSelectedToolId();
		executeTool(toolId);
	}

	function getSelectedToolId(): string {
		return ($('#abp01-maintenance-tool-select').val() || '').toString();
	}

	function executeTool(toolId: string): void {
		hideOperationMessage();
		hideExecutionResultContent();
		toggleBusy(true);

		$.ajax(getExecuteToolUrl(toolId), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (data && data.success) {
				displaySuccessfulOperationMessage(window.abp01MaintenanceL10n.msgExecutedOk);
				if (!!data.content) {
					showExecutionResultContent(data.content);
				}
			} else {
				displayFailedOperationMessage(data.message || window.abp01MaintenanceL10n.msgExecutedFailGeneric);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			displayFailedOperationMessage(window.abp01MaintenanceL10n.msgExecutedFailNetwork);
		});
	}

	function handleExecuteButtonClicked(): void {
		if (confirmExecutionModal === null) {
			confirmExecutionModal = $.abp01ConfirmDialogModal();
		}

		confirmExecutionModal.show(window.abp01MaintenanceL10n.msgConfirmExecute, 
			function(actionConfirmed: boolean, userData: Function) {
				if (actionConfirmed) {
					executeSelectedTool();
				}
			});
	}

	function handleMaintenanceToolSelected(): void {
		var $me: JQuery = $(this);
		var selection = $me.val();
		var $btn: JQuery = $('#abp01-execute-maintenance-tool');

		if (!!selection) {
			$btn.removeAttr('disabled');
		} else {
			$btn.attr('disabled', 'disabled');
		}
	}

	function initContext(): void {
		context = getContext();
	}

	function initControls(): void {
		toolResultPlaceholder = $('#abp01-admin-maintenance-result-container-inner')
			.html();
		toolResult = $('#abp01-tool-action-result').abp01AlertInline({
			dismissible: false
		});
	}

	function initListeners(): void {
		$('#abp01-maintenance-tool-select').on('change', 
			handleMaintenanceToolSelected);
		$('#abp01-execute-maintenance-tool').on('click', 
			handleExecuteButtonClicked);
	}

	$(function() {
		initContext();
		initControls();
		initListeners();
	});
})(jQuery);