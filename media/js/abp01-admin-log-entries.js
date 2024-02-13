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
	var logEntryFormLastValues = null;
	var logEntryFormState = {
		isOpen: false
	};

	var progressBar = null;

	var tplLogEntryRow = null;

	function escapeHtml(value) {
		return (window.lodash || window._)['escape'](value);
	}

	function getLogEntryRowTemplate() {
		if (!tplLogEntryRow) {
			tplLogEntryRow = kite('#tpl-abp01-logEntryRow');
		}
		return tplLogEntryRow;
	}

	function renderLogEntryRowAndAppendToTable(logEntry, formattedLogEntry) {
		var templateData = $.extend(logEntry, formattedLogEntry || {});
		var newRow = getLogEntryRowTemplate()(templateData);
		var $listingTable =  $('#abp01-trip-summary-log-listingTable');
		var $container = $listingTable.find('tbody');
		
		$container.append(newRow);
		checkLogEntriesTableRowCountAndUpdateLayout($listingTable);
	}

	function updateLogEntryRow(logEntryId, logEntry, formattedLogEntry) {
		var $row = $('#abp01-trip-summary-log-listingRow-' + logEntryId);
		var $auxRow = $('#abp01-trip-summary-log-listingRowAux-' + logEntryId);

		console.log($row);

		if ($row.length) {
			console.log($row.find('td.wpts-cell-rider'));
			$row.find('td.wpts-cell-rider').text(logEntry.rider);
			$row.find('td.wpts-cell-date').text(formattedLogEntry.date);
			$row.find('td.wpts-cell-timeInHours').text(formattedLogEntry.timeInHours);
			$row.find('td.wpts-cell-vehicle').text(logEntry.vehicle);
			$row.find('td.wpts-cell-gear').text(logEntry.gear);
			$row.find('td.wpts-cell-isPublic').text(formattedLogEntry.isPublic);
		}

		if ($auxRow.length) {
			$auxRow.find('td.wpts-cell-notes').text(!!logEntry.notes && !!logEntry.notes.length 
				? logEntry.notes 
				: '-');
		}
	}

	function removeLogEntryRow(logEntryId) {
		var $listingTable = $('#abp01-trip-summary-log-listingTable');
		var $row = $('#abp01-trip-summary-log-listingRow-' + logEntryId);
		var $auxRow = $('#abp01-trip-summary-log-listingRowAux-' + logEntryId);

		if ($auxRow.length) {
			$auxRow.remove();	
		}

		if ($row.length) {
			$row.remove();
			checkLogEntriesTableRowCountAndUpdateLayout($listingTable);
		}
	}

	function removeAllLogEntriesRows() {
		var $listingTable = $('#abp01-trip-summary-log-listingTable');
		var $rows = $listingTable.find('tbody tr');
		$rows.remove();

		checkLogEntriesTableRowCountAndUpdateLayout($listingTable);
	}

	function checkLogEntriesTableRowCountAndUpdateLayout($listingTable)  {
		var $rows = $listingTable.find('tbody tr');
		if ($rows.size() > 0) {
			$listingTable.show();
			$('#abp01-tripSummaryLog-noLogEntries').hide();
			$('#abp01-clearTripSummary-log').show();
			updateTripSummaryLogLauncherStatusItem(true);
		} else {
			$listingTable.hide();
			$('#abp01-tripSummaryLog-noLogEntries').show();
			$('#abp01-clearTripSummary-log').hide();
			updateTripSummaryLogLauncherStatusItem(false);
		}
	}

	function getContext() {
		return {
			postId: window['abp01_postId'] || 0,
			saveNonce: window['abp01_saveRouteLogEntryNonce'] || null,
			deleteLogEntryNonce: window['abp01_deleteRouteLogEntryNonce'] || null,
			deleteAllRouteLogEntriesNonce: window['abp01_deleteAllRouteLogEntriesNonce'] || null,
			getAdminLogEntryByIdNonce: window['abp01_getAdminLogEntryByIdNonce'] || null,

			ajaxSaveAction: window['abp01_ajaxSaveRouteLogEntryAction'] || null,
			ajaxDeleteLogEntryAction: window['abp01_ajaxDeleteRouteLogEntryAction'] || null,
			ajaxDeleteAllLogEntriesAction: window['abp01_deleteAllRouteLogEntriesAction'] || null,
			ajaxGetAdminlogEntryByIdAction: window['abp01_getAdminlogEntryByIdAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxUrl'] || null,
		};
	}

	function toggleBusy(show) {
		var $target = logEntryFormState.isOpen 
			? $('#abp01-tripSummaryLog-formContainer')
			: $('#abp01-tripSummaryLog-adminRoot');

		if (show) {
			if (progressBar == null) {
				progressBar = $('#tpl-abp01-progress-container').progressOverlay({
					$target: $target,
					message: arguments.length == 2 ? arguments[1] : 'Please wait...',
					style: {
						top: !logEntryFormState.isOpen 
							? ($('#abp01-tripSummaryLog-adminRoot').height() - 20) / 2
							: 280
					}
				});
			}
		} else {
			if (progressBar != null) {
				progressBar.destroy();
				progressBar = null;
			}
		}
	}

	function toastMessage(success, message) {
		window.abp01.toastMessage(success, message, logEntryFormState.isOpen 
			? '#abp01-tripSummaryLog-formContainer-inner'
			: '#abp01-enhanced-editor-log-metabox');
	}

	function disableWindowScroll() {
		window.abp01.disableWindowScroll();
	}

	function enableWindowScroll() {
		window.abp01.enableWindowScroll();
	}

	function getLogEntryFormSaveUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxSaveAction)
			.addSearch('abp01_postId', context.postId)
			.addSearch('abp01_nonce_log_entry', context.saveNonce)
			.toString();
	}

	function getLogEntryDeleteUrl(logEntryId) {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxDeleteLogEntryAction)
			.addSearch('abp01_postId', context.postId)
			.addSearch('abp01_route_log_entry_id', logEntryId)
			.addSearch('abp01_nonce_log_entry', context.deleteLogEntryNonce)
			.toString();
	}

	function getLogEntriesDeleteUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxDeleteAllLogEntriesAction)
			.addSearch('abp01_postId', context.postId)
			.addSearch('abp01_nonce_log_entry', context.deleteAllRouteLogEntriesNonce)
			.toString();
	}

	function getGetLogEntryByIdUrl(logEntryId) {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxGetAdminlogEntryByIdAction)
			.addSearch('abp01_postId', context.postId)
			.addSearch('abp01_route_log_entry_id', logEntryId)
			.addSearch('abp01_nonce_log_entry', context.getAdminLogEntryByIdNonce)
			.toString();
	}

	function collectLogEntryFormData() {
		return {
			abp01_route_log_entry_id: $('#abp01-route-log-entry-id').val(),
			abp01_log_rider: $('#abp01-log-rider').val(),
			abp01_log_date: $('#abp01-log-date').val(),
			abp01_log_time: $('#abp01-log-time').val(),
			abp01_log_vehicle: $('#abp01-log-vehicle').val(),
			abp01_log_gear: $('#abp01-log-gear').val(),
			abp01_log_notes: $('#abp01-log-notes').val(),
			abp01_log_ispublic: $('#abp01-log-is-public').is(':checked') 
				? $('#abp01-log-is-public').val() 
				: ''
		};
	}

	function initToastMessages() {
		window.abp01.initToastMessages('#abp01-tripSummaryLog-formContainer-inner');
	}

	function saveLogEntry() {
		var formValues = collectLogEntryFormData();
		var logEntryId = parseInt(formValues.abp01_route_log_entry_id);
		var isEditing = !isNaN(logEntryId) && logEntryId > 0;

		toggleBusy(true, abp01AdminLogEntriesL10n.msgSaveWorking);
		
		$.ajax(getLogEntryFormSaveUrl(), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: formValues
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success && !!data.logEntry) {
				closeAddLogEntryForm();
				updateTripSummaryLogLauncherStatusItem(true);

				if (!isEditing) {
					renderLogEntryRowAndAppendToTable(data.logEntry, 
						data.formattedLogEntry);
				} else {
					updateLogEntryRow(logEntryId, 
						data.logEntry, 
						data.formattedLogEntry);
				}
				
				logEntryFormLastValues = formValues;
			} else {
				toastMessage(false, (data || {}).message || abp01AdminLogEntriesL10n.errCouldNotSaveLogEntry);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			toastMessage(false, abp01AdminLogEntriesL10n.errCouldNotSaveLogEntry);
		});
	}

	function openAddLogEntryForm(onShow) {
		initBlockUIDefaultStyles();
		$.blockUI({
			message: $('#abp01-tripSummaryLog-formContainer'),
			css: {
				width: '640px',
				height: '480px',
				top: 'calc(50% - 240px)',
				left: 'calc(50% - 320px)',
				padding: '10px',
				borderRadius: '5px',
				backgroundColor: '#fff',
				boxShadow: '0 5px 15px rgba(0, 0, 0, 0.7)'
			},
			onBlock: function() {
				setLogEntryFormOpen();
				disableWindowScroll();
				initToastMessages();

				if (!!onShow && $.isFunction(onShow)) {
					onShow();
				}
			},
			onUnblock: function() {
				setLogEntryFormClosed();
				enableWindowScroll();
			}
		});
	}

	function setLogEntryFormOpen() {
		logEntryFormState.isOpen = true;
	}

	function setLogEntryFormClosed() {
		logEntryFormState.isOpen = false;
	}

	function closeAddLogEntryForm() {
		$.unblockUI();
	}

	function initBlockUIDefaultStyles() {
		$.blockUI.defaults.css = {
			width: '100%',
			height: '100%'
		};
	}

	function deleteLogEntry() {
		var $me = $(this);
		var logEntryId = $me.data('log-entry-id');

		if (!logEntryId) {
			return;
		}
		
		if (!confirm(abp01AdminLogEntriesL10n.msgConfirmLogEntryRemoval)) {
			return;
		}

		toggleBusy(true, abp01AdminLogEntriesL10n.msgDeleteLogEntryWorking);

		$.ajax(getLogEntryDeleteUrl(logEntryId), {
			type: 'POST',
			dataType: 'json',
			cache: false
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success) {
				window.setTimeout(function() {
					toastMessage(true, data.message || abp01AdminLogEntriesL10n.msgLogEntryDeleted);
					removeLogEntryRow(logEntryId);
				}, 500);
			} else {
				toastMessage(false, (data || {}).message || abp01AdminLogEntriesL10n.errCouldNotDeleteLogEntry);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			toastMessage(false, abp01AdminLogEntriesL10n.errCouldNotDeleteLogEntry);
		});
	}

	function setLogEntryFormValues(values) {
		if (!values) {
			return;
		}

		setLogEntryFormTitle(values);

		$('#abp01-route-log-entry-id').val(values.abp01_route_log_entry_id || 0);
		$('#abp01-log-rider').val(values.abp01_log_rider || '');
		$('#abp01-log-gear').val(values.abp01_log_gear || '');
		$('#abp01-log-date').val(values.abp01_log_date || '');
		$('#abp01-log-time').val(values.abp01_log_time || '');
		$('#abp01-log-vehicle').val(values.abp01_log_vehicle || '');
		$('#abp01-log-notes').val(values.abp01_log_notes || '');
		$('#abp01-log-is-public').prop('checked', values.abp01_log_ispublic === 'yes');
	}

	function setLogEntryFormTitle(values) {
		var $formTitle = $('#abp01-tripSummaryLog-formTitle');
		if (parseInt(values.abp01_route_log_entry_id) <= 0) {
			$formTitle.text(abp01AdminLogEntriesL10n.lblLogEntryAddFormTitle);
		} else {
			$formTitle.text(abp01AdminLogEntriesL10n.lblLogEntryEditFormTitle);
		}
	}

	function deleteAllLogEntries() {
		if (!confirm(abp01AdminLogEntriesL10n.msgConfirmLogAllEntriesRemoval)) {
			return;
		}

		toggleBusy(true, abp01AdminLogEntriesL10n.msgDeleteAllLogEntriesWorking);

		$.ajax(getLogEntriesDeleteUrl(), {
			type: 'POST',
			dataType: 'json',
			cache: false
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success) {
				toastMessage(true, data.message || abp01AdminLogEntriesL10n.msgLogAllEntriesDeleted);
				removeAllLogEntriesRows();
				updateTripSummaryLogLauncherStatusItem(false);
			} else {
				toastMessage(false, (data || {}).message || abp01AdminLogEntriesL10n.errCouldNotDeleteAllLogEntries);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			toastMessage(false, abp01AdminLogEntriesL10n.errCouldNotDeleteAllLogEntries);
		});
	}

	function openEditLogEntry() {
		var $me = $(this);
		var logEntryId = $me.data('log-entry-id');

		if (!logEntryId) {
			return;
		}

		toggleBusy(true, abp01AdminLogEntriesL10n.msgLoadAdminLogEntryByIdForEditing);

		$.ajax(getGetLogEntryByIdUrl(logEntryId), {
			type: 'GET',
			dataType: 'json',
			cache: false
		}).done(function (data, status, xhr) {
			toggleBusy(false);
			if (!!data && !!data.success) {
				window.setTimeout(function() {
					openAddLogEntryForm(function() {
						var newFormValues = $.extend(logEntryFormLastValues, {
							abp01_route_log_entry_id: logEntryId,
							abp01_log_rider: data.logEntry.rider,
							abp01_log_date: data.logEntry.date,
							abp01_log_time: data.logEntry.timeInHours,
							abp01_log_vehicle: data.logEntry.vehicle,
							abp01_log_gear: data.logEntry.gear,
							abp01_log_notes: data.logEntry.notes,
							abp01_log_ispublic: data.logEntry.isPublic ? 'yes' : 'no'
						});
	
						setLogEntryFormValues(newFormValues);
					});
				}, 500);
			} else {
				toastMessage(false, (data || {}).message || abp01AdminLogEntriesL10n.errCouldNotLoadAdminLogEntryDataById);
			}
		}).fail(function (xhr, status, error) {
			toggleBusy(false);
			toastMessage(false, abp01AdminLogEntriesL10n.errCouldNotLoadAdminLogEntryDataById);
		});
	}

	function initEvents() {
		$(document).on('click', 
			'#abp01-addTripSummary-logEntry', 
			function() {		
				openAddLogEntryForm(function() {
					var newFormValues = $.extend(logEntryFormLastValues, {
						abp01_route_log_entry_id: '0',
						abp01_log_notes: '',
						abp01_log_time: '1'
					});

					setLogEntryFormValues(newFormValues);
				});
			});

		$(document).on('click', 
			'.abp01-close-tripSummaryLog-form', 
			closeAddLogEntryForm);
		$(document).on('click', 
			'#abp01-cancel-logEntry', 
			closeAddLogEntryForm);

		$(document).on('click', 
			'#abp01-save-logEntry', 
			saveLogEntry);

		$(document).on('click', 
			'a[rel="logentry-item-delete"]', 
			deleteLogEntry);

		$(document).on('click', 
			'a[rel="logentry-item-edit"]', 
			openEditLogEntry);

		$(document).on('click', 
			'#abp01-clearTripSummary-log', 
			deleteAllLogEntries);

		$(document)
			.on('click', 'a[data-action=abp01-goToLogEntries]', {}, function() {
				$([document.documentElement, document.body]).animate({
					scrollTop: $("#abp01-tripSummaryLog-adminRoot").offset().top
				}, 1000);
			});
	}

	function initForm() {
		$('#abp01-log-date').datepicker({
            dateFormat : 'yy-mm-dd'
        });

		$('#abp01-log-time').abp01NumericStepper({
			minValue: 1,
			maxValue: 1000,
			defaultValue: 1
		})
	}

	function setupKiteFormatters() {
		kite.formatters['esc-html'] = function(v, obj) {
			return escapeHtml(v);
		};
	}

	function initFormState() {
		context = getContext();
		logEntryFormLastValues = collectLogEntryFormData();
	}

	function updateTripSummaryLogLauncherStatusItem(hasLogEntries) {
		var $statusItem = $('#abp01-editor-launcher-status-trip-summary-logs');

		if (!!window.abp01 && !!window.abp01.toggleEnhancedEditorStatusItemIcon) {
			window.abp01.toggleEnhancedEditorStatusItemIcon($statusItem, hasLogEntries);
		}

		if (!!window.abp01 && !!window.abp01.toggleEnhancedEditorStatusItemText) {
			window.abp01.toggleEnhancedEditorStatusItemText($statusItem, 
				hasLogEntries, 
				abp01AdminLogEntriesL10n.lblTripSummaryLogPresent, 
				abp01AdminLogEntriesL10n.lblTripSummaryLogNotPresent);
		}

		refreshTooltips();
	}

	function initTooltips() {
		if (!!window.abp01 && !!window.abp01.addSimpleTooltip) {
			window.abp01.addSimpleTooltip('abp01-editor-launcher-status-trip-summary-logs a');
		}
	}

	function cleanupTooltips() {
		if (!!window.abp01 && !!window.abp01.cleanupTooltip) {
			window.abp01.cleanupTooltip('abp01-editor-launcher-status-trip-summary-logs a');
		}
	}

	function refreshTooltips() {
		cleanupTooltips();
		initTooltips();
	}

	$(document).ready(function() {
		initFormState();
		setupKiteFormatters();
		initForm();
		initEvents();

		window.setTimeout(function() {
			initTooltips();
		}, 1000);
	});
})(jQuery);