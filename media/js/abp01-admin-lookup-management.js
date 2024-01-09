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
	
	/**
	 * Constants
	 * */
	var DEFAULT_LANG = '_default';
	var LOOKUP_DELETE_INITIAL_REQUEST = '_lookup_delete_initial_request';
	var LOOKUP_DELETE_INUSE_CONFIRMATION = '_lookup_delete_inuse_confirmation';

	/**
	 * Current form controls
	 * */

	var progressBar = null;
	var $ctlTypeSelector = null;
	var $ctlLangSelector = null;
	var $ctlLookupListing = null;
	var $ctlLookupItemDefaultLabel = null;
	var $ctlLookupItemTranslatedLabel = null;
	var $ctlEditorOperationResultContainer = null;
	var $ctlListingResultContainer = null;
	var $ctlDeleteOnlyLangTranslation = null;
	var $ctlDeleteOnlyLangTranslationContainer = null;
	var $ctlDeleteOperationResultContainer = null;

	/**
	 * Cached template references
	 * */
	var tplLookupListing = null;

	/**
	 * Current state
	 * */

	var context = null;
	var currentItems = {};
	var editingItem = null;
	var lookupDelete = {
		stage: null,
		nonce: null
	};

	function escapeHtml(value) {
		return (window.lodash || window._)['escape'](value);
	}

	function clearLookupDeleteState() {
		lookupDelete = {
			stage: null,
			nonce: null
		};
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
		if (isCurrentlyEditingEditingItem()) {
			delete currentItems[editingItem.id];
			clearCurrentlyEditedItem();
		}
	}

	function beginEditingItem(itemId) {
		editingItem = currentItems.hasOwnProperty(itemId) 
			? currentItems[itemId] 
			: null;
	}

	function isCurrentlyEditingEditingItem() {
		return !!editingItem;
	}

	function updateLocalItemData(item) {
		currentItems[item.id] = item;
	}

	function clearCurrentlyEditedItem() {
		editingItem = null;
	}

	function clearAllLocalData() {
		clearCurrentlyEditedItem();
		clearLookupDeleteState();
		currentItems = {};
	}

	function getLookupListingTemplate() {
		if (!tplLookupListing) {
			tplLookupListing = kite('#tpl-abp01-lookupDataRow');
		}
		return tplLookupListing;
	}

	function displaySuccessfulListingOperationMessage(message) {
		$ctlListingResultContainer.abp01OperationMessage('success', message);
	}

	function displayFailedListingOperationMessage(message) {
		$ctlListingResultContainer.abp01OperationMessage('error', message);
	}

	function hideListingOperationMessage() {
		$ctlListingResultContainer.abp01OperationMessage('hide');
	}

	function displaySuccessfulRemovalOperationMessage(message) {
		$ctlDeleteOperationResultContainer.abp01OperationMessage('success', message);
	}

	function displayWarningRemovalOperationMessage(message) {
		$ctlDeleteOperationResultContainer.abp01OperationMessage('warning', message);
	}

	function displayFailedRemovalOperationMessage(message) {
		$ctlDeleteOperationResultContainer.abp01OperationMessage('error', message);
	}

	function hideRemovalOperationMessage() {
		$ctlDeleteOperationResultContainer.abp01OperationMessage('hide');
	}

	function displaySuccessfulEditorOperationMessage(message) {
		$ctlEditorOperationResultContainer.abp01OperationMessage('success', message);
	}

	function displayFailedEditorOperationMessage(message) {
		$ctlEditorOperationResultContainer.abp01OperationMessage('error', message);
	}

	function hideEditorOperationMessage() {
		$ctlEditorOperationResultContainer.abp01OperationMessage('hide');
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

	function showBusy($target) {
		var centerY = true;

		if (!$target) {
			$target = $('#wpwrap');
			centerY = false;
		}

		if (progressBar == null) {
			progressBar = $('#tpl-abp01-progress-container').progressOverlay({
				$target: $target,
				message: abp01LookupMgmtL10n.msgWorking,
				centerY: centerY
			});
		}
	}

	function hideBusy(onRemove) {
		if (progressBar != null) {
			progressBar.destroy(onRemove);
			progressBar = null;
		} else if (!!onRemove && $.isFunction(onRemove)) {
			onRemove();
		}
	}

	function getLoadLookupDataUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxGetLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.getLookupNonce)
			.toString();
	}

	function getAddLookupUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxAddLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.addLookupNonce)
			.toString();
	}

	function getEditLookupUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxEditLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.editLookupNonce)
			.toString();
	}

	function getDeleteLookupUrl() {
		var uri = URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxDeleteLookupAction)
			.addSearch('abp01_nonce_lookup_mgmt', context.deleteLookupNonce)

		if (isLookupDeleteInuUseConfirmationStage()) {
			uri.addSearch('abp01_nonce_lookup_force_remove', getLookupDeleteInUseConfirmationNonce());
		}

		return uri.toString();
	}

	function cleanupLookupItems() {
		clearAllLocalData();
		$ctlLookupListing
			.find('tbody')
			.html('');
	}

	function clearForm() {
		$ctlLookupItemTranslatedLabel.val('');
		$ctlLookupItemDefaultLabel.val('');
	}

	function renderLookupItems(items, append) {
		var content = getLookupListingTemplate()({
			lookupItems: items
		});
		var $container = $ctlLookupListing.find('tbody');
		if (!append) {
			$container.html(content);
		} else {
			$container.append(content);
		}
	}

	function refreshLookupItem(item) {
		var $oldRow = $('#lookupItemRow-' + item.id);
		$oldRow.replaceWith(getLookupListingTemplate()({
			lookupItems: [item]
		}));
	}

	function deleteLookupItemRow(item, onlyUpdateTranslationCell) {
		var $row = $('#lookupItemRow-' + item.id);
		if (onlyUpdateTranslationCell) {
			$row.find('td[rel=translatedLabelCell]').html('-');
		} else {
			$row.remove();
		}
	}

	function reloadLookupItems() {
		var lookupType = $ctlTypeSelector.val();
		var lookupLang = $ctlLangSelector.val();

		showBusy(null);
		hideListingOperationMessage();

		$.ajax(getLoadLookupDataUrl(), {
			cache: false,
			dataType: 'json',
			type: 'GET',
			data: {
				type: lookupType,
				lang: lookupLang
			}
		}).done(function(data) {
			hideBusy(null);
			if (data && data.success && data.items) {
				cleanupLookupItems();
				renderLookupItems(data.items, false);
				$.each(data.items, function(idx, item) {
					updateLocalItemData(item);
				});
			} else {
				displayFailedListingOperationMessage(abp01LookupMgmtL10n.errListingFailGeneric);
			}
		}).fail(function() {
			hideBusy(function() {
				displayFailedListingOperationMessage(abp01LookupMgmtL10n.errListingFailNetwork);
			});
		});
	}

	function createLookupItem() {        
		showBusy($('#TB_window'));
		hideEditorOperationMessage();

		$.ajax(getAddLookupUrl(), {
			cache: false,
			dataType: 'json',
			type: 'POST',
			data: {
				type: $ctlTypeSelector.val(),
				lang: $ctlLangSelector.val(),
				defaultLabel: $ctlLookupItemDefaultLabel.val(),
				translatedLabel: $ctlLookupItemTranslatedLabel.val()
			}
		}).done(function(data) {
			hideBusy(null);
			if (data && data.success) {
				updateLocalItemData(data.item);
				renderLookupItems([data.item], true);
				clearForm();
				displaySuccessfulEditorOperationMessage(abp01LookupMgmtL10n.msgSaveOk);
			} else {
				displayFailedEditorOperationMessage(data.message || abp01LookupMgmtL10n.errFailGeneric);
			}
		}).fail(function() {
			hideBusy(function() {
				displayFailedEditorOperationMessage(abp01LookupMgmtL10n.errFailNetwork);
			});
		});
	}

	function modifyLookupItem() {
		var defaultLabel = $ctlLookupItemDefaultLabel.val();
		var translatedLabel = $ctlLookupItemTranslatedLabel.val();

		showBusy($('#TB_window'));
		hideEditorOperationMessage();

		$.ajax(getEditLookupUrl(), {
			cache: false,
			dataType: 'json',
			type: 'POST',
			data: {
				id: editingItem.id,
				lang: $ctlLangSelector.val(),
				defaultLabel: defaultLabel,
				translatedLabel: translatedLabel
			}
		}).done(function(data) {
			hideBusy(null);
			if (data && data.success) {
				editingItem.defaultLabel = defaultLabel;
				editingItem.hasTranslation = !!translatedLabel;
				editingItem.label = editingItem.hasTranslation 
					? translatedLabel 
					: defaultLabel;

				updateLocalItemData(editingItem);
				refreshLookupItem(editingItem);

				$ctlEditorOperationResultContainer.abp01OperationMessage('success', 
					abp01LookupMgmtL10n.msgSaveOk);
			} else {
				$ctlEditorOperationResultContainer.abp01OperationMessage('error', 
					data.message || abp01LookupMgmtL10n.errFailGeneric);
			}
		}).fail(function() {
			hideBusy(function() {
				$ctlEditorOperationResultContainer.abp01OperationMessage('error', 
					abp01LookupMgmtL10n.errFailNetwork);
			});
		});
	}

	function deleteLookupItem() {
		var lang = $ctlLangSelector.val();
		var deleteOnlyLang = $ctlDeleteOnlyLangTranslation.is(':checked');

		showBusy($('#TB_window'));

		hideListingOperationMessage();
		hideRemovalOperationMessage();

		$.ajax(getDeleteLookupUrl(), {
			cache: false,
			dataType: 'json',
			type: 'POST',
			data: {
				id: editingItem.id,
				lang: lang,
				deleteOnlyLang: deleteOnlyLang.toString()
			}
		}).done(function(data) {
			if (data) {
				//Successful removal, move on with cleaning everything up
				if (data.success) {
					hideBusy(function() {
						deleteLookupItemRow(editingItem, deleteOnlyLang && lang !== DEFAULT_LANG);
						setCurrentItemDeleted();
						closeDeleteDialog();
						displaySuccessfulListingOperationMessage(abp01LookupMgmtL10n.msgDeleteOk);
					});
				//The lookup item is in use, set removal stage and ask the user for confirmation
				} else if (!!data.requiresConfirmation && !!data.confirmationNonce) {
					hideBusy(function() {
						setLookupDeleteInUseConfirmation(data.confirmationNonce);
						displayWarningRemovalOperationMessage(data.message);
					});
				//Some kind of failure. Warn the user.
				} else {
					hideBusy(function() {
						displayFailedRemovalOperationMessage(data.message || abp01LookupMgmtL10n.errDeleteFailedGeneric);
					});
				}
			//Some kind of failure. Warn the user.
			} else {
				hideBusy(function() {
					displayFailedRemovalOperationMessage(data.message || abp01LookupMgmtL10n.errDeleteFailedGeneric);
				});
			}
		}).fail(function() {
			hideBusy(function() {
				displayFailedRemovalOperationMessage(abp01LookupMgmtL10n.errDeleteFailedNetwork);
			});
		});
	}

	function saveLookupItem() {
		if (!isCurrentlyEditingEditingItem()) {
			createLookupItem();
		} else {
			modifyLookupItem();
		}
	}

	function showEditor(currentItemId) {
		var lang = $ctlLangSelector.val();
		var langLabel = $ctlLangSelector.find('option:selected').text();
		var title = !!currentItemId 
			? abp01LookupMgmtL10n.editItemTitle 
			: abp01LookupMgmtL10n.addItemTitle;

		var height = 175;
		var $translatedLabelFieldLine = $ctlLookupItemTranslatedLabel
			.closest('div.abp01-form-line');

		//if the selected language is other than the default one
		//also show the translated label field
		//this way we allow setting the translated label 
		//without the user having to take an extra action        
		if (lang !== DEFAULT_LANG) {
			height = 230;
			$translatedLabelFieldLine.show();
			$translatedLabelFieldLine
				.find('span[rel="abp01-languageDetails"]')
				.html('(' + langLabel + ')');
		} else {
			$translatedLabelFieldLine.hide();
		}

		//set the initial values, if given
		if (currentItemId) {
			beginEditingItem(currentItemId);
			if (isCurrentlyEditingEditingItem()) {
				$ctlLookupItemDefaultLabel
					.val(editingItem.defaultLabel);
				$ctlLookupItemTranslatedLabel
					.val(editingItem.hasTranslation ? editingItem.label : '');
			}
		} else {
			$ctlLookupItemDefaultLabel.val('');
			$ctlLookupItemTranslatedLabel.val('');
			clearCurrentlyEditedItem();
		}

		//show the editor
		hideEditorOperationMessage();
		tb_show(title, '#TB_inline?width=' + 450 + '&height=' + height + '&inlineId=abp01-lookup-item-form');
	}

	function showDeleteDialog(currentItemId) {
		var lang = $ctlLangSelector.val();
		var height = 180;

		if (lang === DEFAULT_LANG) {
			$ctlDeleteOnlyLangTranslationContainer.hide();
			$ctlDeleteOnlyLangTranslation.prop('checked', false);
			height = 150;
		} else {
			$ctlDeleteOnlyLangTranslationContainer.show();
		}

		beginEditingItem(currentItemId);
		tb_show(abp01LookupMgmtL10n.ttlConfirmDelete, '#TB_inline?width=450&height=' + height + '&inlineId=abp01-lookup-item-delete-form');
		
		hideRemovalOperationMessage();
		setLookupDeleteInitialRequest();
	}

	function closeEditor() {
		//reset field values
		clearForm();
		clearCurrentlyEditedItem();
		//close the window
		hideEditorOperationMessage();
		tb_remove();
	}

	function closeDeleteDialog() {
		clearLookupDeleteState();
		$ctlDeleteOnlyLangTranslation.prop('checked', false);
		hideRemovalOperationMessage();
		tb_remove();
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

	function initControls() {
		//result containers - they serve as display containers for various operations results
		$ctlListingResultContainer = $('#abp01-lookup-listing-result');
		$ctlEditorOperationResultContainer = $('#abp01-lookup-operation-result');
		$ctlDeleteOperationResultContainer = $('#abp01-lookup-delete-operation-result');

		//selection controls
		$ctlTypeSelector = $('#abp01-lookupTypeSelect');
		$ctlLangSelector = $('#abp01-lookupLangSelect');
	
		//the listing
		$ctlLookupListing = $('#abp01-admin-lookup-listing');

		//form inputss
		$ctlLookupItemDefaultLabel = $('#abp01-lookup-item-defaultLabel');
		$ctlLookupItemTranslatedLabel = $('#abp01-lookup-item-translatedLabel');
		$ctlDeleteOnlyLangTranslation = $('#abp01-lookup-item-deleteOnlyLang');
		$ctlDeleteOnlyLangTranslationContainer = $ctlDeleteOnlyLangTranslation.closest('div.abp01-form-line');
	}

	function initListeners() {
		$ctlTypeSelector.change(reloadLookupItems);
		$ctlLangSelector.change(reloadLookupItems);

		//bind click action for the "Add new item" buttons
		$('#abp01-add-lookup-bottom, #abp01-add-lookup-top').click(function() {
			showEditor(null);
		});

		//bind action for edit buttons
		$(document).on('click', 'a[rel=item-edit]', function() {
			var $me = $(this);
			var id = $me.attr('data-lookupId');
			showEditor(id);
		});

		//bind action for delete buttons
		$(document).on('click', 'a[rel=item-delete]', function() {
			var $me = $(this);
			var id = $me.attr('data-lookupId');
			showDeleteDialog(id);
		});

		//bind actions for form controls
		$('#abp01-cancel-lookup-item').click(closeEditor);
		$('#abp01-save-lookup-item').click(saveLookupItem);

		//bind actions for delete window dialog
		$('#abp01-cancel-delete-lookup-item').click(closeDeleteDialog);
		$('#abp01-delete-lookup-item').click(deleteLookupItem);

		//bind actions for reload buttons
		$('#abp01-reload-list-top').click(reloadLookupItems);
		$('#abp01-reload-list-bottom').click(reloadLookupItems);
	}

	function setupKiteFormatters() {
		kite.formatters['esc-html'] = function(v, obj) {
			return escapeHtml(v);
		};
	}

	$(document).ready(function() {
		setupKiteFormatters();
		initContext();
		initControls();
		initListeners();
		initBlockUIDefaultStyles();
		reloadLookupItems();
	});
})(jQuery);