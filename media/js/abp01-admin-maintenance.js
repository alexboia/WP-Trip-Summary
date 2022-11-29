/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

	var progressBar = null;

	var context = null;

	function getContext() {
		return {
			nonce: window['abp01_nonce'] || null,
			ajaxExecuteToolAction: window['abp01_ajaxExecuteToolAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null
		};
	}

	function displaySuccessfulOperationMessage(message) {
		$('#abp01-tool-execution-result')
			.abp01OperationMessage('success', message);
	}

	function displayFailedOperationMessage(message) {
		$('#abp01-tool-execution-result')
			.abp01OperationMessage('error', message);
	}

	function hideOperationMessage() {
		$('#abp01-tool-execution-result')
			.abp01OperationMessage('hide');
	}

	function toggleBusy(show) {
		if (show) {
			if (progressBar == null) {
				progressBar = $('#tpl-abp01-progress-container').progressOverlay({
					$target: $('#wpwrap'),
					message: 'Working...'
				});
			}
		} else {
			if (progressBar != null) {
				progressBar.destroy();
				progressBar = null;
			}
		}
	}

	function getExecuteToolUrl(toolId) {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxExecuteToolAction)
			.addSearch('abp01_nonce_execute_tool', context.nonce)
			.addSearch('abp01_tool_id', toolId)
			.toString();
	}

	function handleMaintenanceToolSelected() {
		var $me = $(this);
		var selection = $me.val();

		if (!!selection) {
			$('#abp01-execute-maintenance-tool').removeAttr('disabled');
		} else {
			$('#abp01-execute-maintenance-tool').attr('disabled', 'disabled');
		}
	}

	function handleExecuteButtonClicked() {
		if (confirm('Are you sure you want to execute the selected tool?')) {
			executeSelectedTool();
		}
	}

	function executeSelectedTool() {
		var toolId = getSelectedToolId();
		executeTool(toolId);
	}

	function getSelectedToolId() {
		return $('#abp01-maintenance-tool-select').val();
	}

	function executeTool(toolId) {
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
				displaySuccessfulOperationMessage('The selected maintenance tool successfully executed.');
				if (!!data.content) {
					showExecutionResultContent(data.content);
				}
			} else {
				displayFailedOperationMessage(data.message || 'The selected maintenance tool could not be executed.');
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			displayFailedOperationMessage('The selected maintenance tool could not be executed due to a possible network issue. Please check your internet connection and try again.');
		});
	}

	function hideExecutionResultContent() {
		$('#abp01-admin-maintenance-result-container-inner')
			.html('');
		$('#abp01-admin-maintenance-result-container')
			.hide();
	}

	function showExecutionResultContent(content) {
		$('#abp01-admin-maintenance-result-container-inner')
			.html(content);
		$('#abp01-admin-maintenance-result-container')
			.show();
	}

	function initContext() {
		context = getContext();
	}

	function initBlockUIDefaultStyles() {
		$.blockUI.defaults.css = {
			width: '100%',
			height: '100%'
		};
	}

	function initListeners() {
		$('#abp01-maintenance-tool-select')
			.change(handleMaintenanceToolSelected);
		$('#abp01-execute-maintenance-tool')
			.click(handleExecuteButtonClicked);
	}

	$(document).ready(function() {
		initContext();
		initBlockUIDefaultStyles();
		initListeners();
	});
})(jQuery);