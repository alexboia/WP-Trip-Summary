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

	function toggleBusy(show) {
		if (progressBar === null) {
			progressBar = $('#wpwrap').abp01ProgressModal();
		}

		if (show) {
			var message = arguments.length == 2 ? arguments[1] : null;
			progressBar.show(message || 'Please wait');
		} else {
			progressBar.hide();
		}
	}

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

			ajaxGetLogFileAction: window['abp01_ajaxGetLogFileAction'] || null,
			ajaxDownloadLogFileAction: window['abp01_ajaxDownloadLogFileAction'] || null,
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

	function initState() {
		context = getContext();
	}

	function loadLogFile(logFileId) {
		toggleBusy(true);

		$.ajax(getGetLogFileUrl(logFileId), {
			type: 'GET',
			dataType: 'json',
			cache: false
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success) {
				//TODO: error if not found
				currentLogFileId = logFileId;

				if (!!data.found) {
					$('#abp01-log-file-contents').text(data.contents);
				} else {
					$('#abp01-log-file-contents').text('');
				}

				if (!!data.trimmed) {
					$('#abp01-log-file-too-large-warning').show();
				} else {
					$('#abp01-log-file-too-large-warning').hide();
				}
			} else {
				//TODO: error
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			//TODO: error
		});
	}

	function downloadLogFile(logFileId) {
		window.open(getDownloadLogFileUrl(logFileId), '_blank');
	}

	function reloadCurrentLogFileId() {
		if (!!currentLogFileId) {
			loadLogFile(currentLogFileId);
		}
	}

	function loadInitialLogFile() {
		var $selectedLogFileItem = getSelectedLogFileItem();
		var logFileId = $selectedLogFileItem.length > 0 
			? getLogFileId($selectedLogFileItem) 
			: null;

		if (!!logFileId) {
			loadLogFile(logFileId);
		}
	}

	function setActiveLogFileItem($logFileItem) {
		$('#abp01-log-file-lists-container .list-group-item-action').removeClass('active');
		$logFileItem.addClass('active');
	}

	function initEvents() {
		$(document).on('click', '#abp01-refresh-current-log', function() {
			reloadCurrentLogFileId();
		});

		$(document).on('click', '#abp01-download-current-log', function() {
			if (!!currentLogFileId) {
				downloadLogFile(currentLogFileId);
			}
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

	$(function() {
		initState();
		initEvents();
		loadInitialLogFile();
	});
 })(jQuery);