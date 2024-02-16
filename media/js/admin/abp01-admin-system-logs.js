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
	var context = null;
	var progressBar = null;
	var currentLogFileId = null;
	var confirmDeleteModal = null;
	var logResultAlert = null;

	function getSelectedLogFileItem() {
		return $('#abp01-log-file-lists-container').find('.list-group-item.active');
	}

	function getLogFileId($logFileItem) {
		return $logFileItem.data('file-id');
	}

	function getContext() {
		return {
			getLogFileNonce: window['abp01_getLogFileNonce'] || null,
			downloadLogFileNonce: window['abp01_downloadLogFileNonce'] || null,
			deleteLogFileNonce: window['abp01_deleteLogFileNonce'] || null,

			ajaxGetLogFileAction: window['abp01_ajaxGetLogFileAction'] || null,
			ajaxDownloadLogFileAction: window['abp01_ajaxDownloadLogFileAction'] || null,
			ajaxDeleteLogFileAction: window['abp01_ajaxDeleteLogFileAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null,
		};
	}

	function getGetLogFileUrl(logFileId) {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxGetLogFileAction)
			.addSearch('abp01_fileId', logFileId)
			.addSearch('abp01_nonce_get_log_file_contents', context.getLogFileNonce)
			.toString();
	}

	function getDownloadLogFileUrl(logFileId) {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxDownloadLogFileAction)
			.addSearch('abp01_fileId', logFileId)
			.addSearch('abp01_nonce_download_log_file', context.downloadLogFileNonce)
			.toString();
	}

	function getDeleteLogFileUrl(logFileId) {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxDeleteLogFileAction)
			.addSearch('abp01_fileId', logFileId)
			.addSearch('abp01_nonce_delete_log_file', context.deleteLogFileNonce)
			.toString();
	}

	function toggleBusy(show) {
		if (show) {
			var message = arguments.length == 2 ? arguments[1] : null;
			progressBar.show(message || 'Please wait');
		} else {
			progressBar.hide();
		}
	}

	function showSuccessResult(message) {
		logResultAlert.success(message);
	}

	function showErrorResult(message) {
		logResultAlert.danger(message);
	}

	function hideResult() {
		logResultAlert.hide();
	}

	function loadLogFile(logFileId) {
		toggleBusy(true);
		hideResult();

		$.ajax(getGetLogFileUrl(logFileId), {
			type: 'GET',
			dataType: 'json',
			cache: false
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success) {
				if (!!data.found) {
					currentLogFileId = logFileId;
					$('#abp01-log-file-contents').text(data.contents);
				} else {
					showErrorResult(abp01AdminSystemLogL10n.errCouldNotFindLogFile);
					$('#abp01-log-file-contents').text('');
				}

				if (!!data.trimmed) {
					$('#abp01-log-file-too-large-warning').show();
				} else {
					$('#abp01-log-file-too-large-warning').hide();
				}
			} else {
				showErrorResult(data.message || abp01AdminSystemLogL10n.errCouldNotLoadLogFile);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			showErrorResult(abp01AdminSystemLogL10n.errCouldNotLoadLogFile);
		});
	}

	function downloadLogFile(logFileId) {
		window.open(getDownloadLogFileUrl(logFileId), '_blank');
	}

	function deleteLogFile(logFileId) {
		toggleBusy(true);

		$.ajax(getDeleteLogFileUrl(logFileId), {
			type: 'POST',
			dataType: 'json',
			cache: false
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success) {
				showSuccessResult(abp01AdminSystemLogL10n.msgLogFileRemovalSuccess);
				processLogFileItemRemoval(logFileId);
			} else {
				showErrorResult(data.message || abp01AdminSystemLogL10n.errCouldNotRemoveLogFile);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			showErrorResult(abp01AdminSystemLogL10n.errCouldNotRemoveLogFile);
		});
	}

	function processLogFileItemRemoval(logFileId) {
		var logType = null;
		var $item = getLogFileItem(logFileId);
		var $itemParent = $item.parent();

		clearCurrentLogInfo();

		if ($item.length > 0) {
			logType = $item.data('file-type');
			$item.remove();
		}

		var $remainingItemsOfSameType = $itemParent.find('.list-group-item-action');
		if ($remainingItemsOfSameType.length > 0)  {
			pickNewSelectedLogFile($remainingItemsOfSameType);
			loadInitialLogFile();
		} else {
			tryPickNewLogFileFromAllRemainingItems();
			showNoLogsMessage(logType);
		}
	}

	function clearCurrentLogInfo() {
		currentLogFileId = null;
		$('#abp01-log-file-contents').text('');
		$('#abp01-log-file-too-large-warning').hide();
	}

	function tryPickNewLogFileFromAllRemainingItems() {
		var $allRemainingItems = $('#abp01-log-file-lists-container .list-group-item-action');
		if ($allRemainingItems.length > 0) {
			pickNewSelectedLogFile($allRemainingItems);
			loadInitialLogFile();
		} else {
			$('#abp01-page-workspace-toolbar').hide();
		}
	}

	function getLogFileItem(logFileId) {
		return $('#abp01-log-file-lists-container').find('a[data-file-id="' + logFileId + '"]');
	}

	function pickNewSelectedLogFile($remainingItems) {
		var $first = $($remainingItems.get(0));
		setActiveLogFileItem($first);
	}

	function showNoLogsMessage(logType) {
		var $alertMessage = null;
		if (logType === 'debug-log') {
			$alertMessage = $('#abp01-no-debug-log-files-found');
		} else if (logType === 'error-log') {
			$alertMessage = $('#abp01-no-error-log-files-found');
		}

		if ($alertMessage != null) {
			$alertMessage.show();
		}
	}

	function reloadCurrentLogFileById() {
		if (!!currentLogFileId) {
			loadLogFile(currentLogFileId);
		}
	}

	function downloadCurrentLogFileById() {
		if (!!currentLogFileId) {
			downloadLogFile(currentLogFileId);
		}
	}

	function deleteCurrentLogFileById() {
		if (!!currentLogFileId) {
			confirmDelete(function(actionConfirmed, userData) {
				if (actionConfirmed) {
					deleteLogFile(currentLogFileId);
				}
			});
		}
	}

	function confirmDelete(callback) {
		if (confirmDeleteModal == null) {
			confirmDeleteModal = $.abp01ConfirmDialogModal();
		}

		confirmDeleteModal.show(abp01AdminSystemLogL10n.msgConfirmLogFileRemoval, 
			callback);
	}

	function loadInitialLogFile(delayed = false) {
		var load = function() {
			var $selectedLogFileItem = getSelectedLogFileItem();
			var logFileId = $selectedLogFileItem.length > 0 
				? getLogFileId($selectedLogFileItem) 
				: null;

			if (!!logFileId) {
				loadLogFile(logFileId);
			}
		};

		if (delayed) {
			window.setTimeout(load, 250);
		} else {
			load();
		}
	}

	function setActiveLogFileItem($logFileItem) {
		$('#abp01-log-file-lists-container .list-group-item-action').removeClass('active');
		$logFileItem.addClass('active');
	}

	function initEvents() {
		$(document).on('click', '#abp01-refresh-current-log', function() {
			reloadCurrentLogFileById();
		});

		$(document).on('click', '#abp01-download-current-log', function() {
			downloadCurrentLogFileById();
		});

		$(document).on('click', '#abp01-delete-current-log', function() {
			deleteCurrentLogFileById();
		});

		$(document).on('click', '#abp01-log-file-lists-container .list-group-item-action', function() {
			var $me = $(this);
			var logFileId = getLogFileId($me);
			if (!!logFileId) {
				setActiveLogFileItem($me);
				loadLogFile(logFileId);
			}
		});
	}

	function initControls() {
		progressBar = $('#wpwrap').abp01ProgressModal();
		logResultAlert = $('#abp01-log-action-result').abp01AlertInline();
	}

	function initState() {
		context = getContext();
	}

	$(function() {
		initState();
		initControls();
		initEvents();
		loadInitialLogFile();
	});
 })(jQuery);