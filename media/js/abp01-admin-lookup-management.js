(function($) {
    "use strict";

    /**
     * Current form controls
     * */

    var progressBar = null;
    var $ctlTypeSelector = null;
    var $ctlLangSelector = null;
    var $ctlLookupListing = null;
    var $ctlLookupItemDefaultLabel = null;
    var $ctlLookupItemTranslatedLabel = null;
    var $ctlOperationResultContainer = null;
    var $ctlListingResultContainer = null;
    var $ctlDeleteOnlyLangTranslation = null;
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

    /**
     * Compiles and caches the template used for rendering lookup items data rows
     * @return Function The compiled template
     * */
    function getLookupListingTemplate() {
        if (!tplLookupListing) {
            tplLookupListing = kite('#tpl-abp01-lookupDataRow');
        }
        return tplLookupListing;
    }

    /**
     * Reads and returns the current form context/state:
     * - nonce - the nonce used to authenticate AJAX lookup data management calls
     * - ajaxBaseUrl - AJAX base URL used when saving the settings
     * - ajaxGetLookupAction - AJAX action used when retrieving the lookup items
     * - ajaxAddLookupAction - AJAX action used when creating a new lookup item/lookup item translation
     * - ajaxEditLookupAction - AJAX action used when editing a lookup item/lookup item translation
     * - ajaxDeleteLookupAction - AJAX action used when deleting a lookup item/lookup item translation
     * @return object The context object comprised of the above-mentioned properties
     * */
    function getContext() {
        return {
            nonce: window['abp01_nonce'] || null,
            ajaxBaseUrl: window['abp01_ajaxUrl'] || null,
            ajaxGetLookupAction: window['abp01_ajaxGetLookupAction'] || null,
            ajaxAddLookupAction: window['abp01_ajaxAddLookupAction'] || null,
            ajaxEditLookupAction: window['abp01_ajaxEditLookupAction'] || null,
            ajaxDeleteLookupAction: window['abp01_ajaxDeleteLookupAction'] || null
        };
    }

    /**
     * Shows or hides the progress indicator, optionally blocking only the given target.
     * If no target is given and the action is to show the indicator, then the entire screen is blocked.
     * @param {Boolean} show Whether to show the progress indicator or to hide it
     * @param {jQuery} $target The target element that should be blocked. Valid only when the indicator is shown (show == true)
     * @return void
     * */
    function toggleBusy(show, $target) {
        if (show) {
            if (!progressBar) {
                progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                    $target: $target || $('#wpwrap'),
                    message: abp01LookupMgmtL10n.msgWorking,
                    centerY: !!$target
                });
            }
        } else {
            if (progressBar) {
                progressBar.destroy();
                progressBar = null;
            }
        }
    }

    /**
     * Displays the given message of the given type (success/failure) in the given container
     * @param {jQuery} $container The container in which the message will be displayed
     * @param {Boolean} success Whether the message is a success message or a failure message
     * @param {String} message The message to be displayed
     * @return void
     * */
    function displayMessage($container, success, message) {
        //first clear the result container
        clearMessage($container);

        //style the message box according to success/error status
        //and show the message
        $container
            .addClass(success ? 'notice' : 'error')
            .html('<p>' + message + '</p>')
            .show();
    }

    /**
     * Clears the message from the given container:
     * - all the message-specific classes are removed;
     * - container content is cleared;
     * - container is hidden
     * @param {jQuery} $container The container for which the message is to be cleared
     * @return void
     * */
    function clearMessage($container) {        
    	$container
            .removeClass('notice')
            .removeClass('error')
            .html('')
            .hide();
    }

    /**
     * Builds the URL from wich the lookup items are loaded
     * @return String The URL
     * */
    function getLoadLookupDataUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxGetLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context.nonce)
            .toString();
    }

    /**
     * Builds the URL used for creating new lookup items
     * @return String The URL
     * */
    function getAddLookupUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxAddLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context.nonce)
            .toString();
    }

    /**
     * Builds the URL used for editing existing lookup items
     * @return String The URL
     * */
    function getEditLookupUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxEditLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context.nonce)
            .toString();
    }

    /**
     * Builds the URL used for deleting existing lookup items
     * @return String The URL
     * */
    function getDeleteLookupUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxDeleteLookupAction)
            .addSearch('abp01_nonce_lookup_mgmt', context.nonce)
            .toString();
    }

    /**
     * Removes all the lookup items that are currently displayed in the listing table
     * @return void
     * */
    function cleanupLookupItems() {
        currentItems = {};
        editingItem = null;
        $ctlLookupListing.find('tbody').html('');
    }

    /**
     * Clears the fields in the currently displayed form. The cleared fields are:
     * - the default label field;
     * - the translated label field.
     * @return void
     * */
    function clearForm() {
        $ctlLookupItemTranslatedLabel.val('');
        $ctlLookupItemDefaultLabel.val('');
    }

    /**
     * Renders the given lookup items and updates the listing table content
     * @param {Array} items The lookup items
     * @param {Boolean} append Whether to append the content to the listing or to replace it alltogether
     * @return void
     * */
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

    /**
     * Refreshes the table row that corresponds to the given item.
     * The entire row is re-rendered and the old row is replaced with the new one
     * @param {Object} item The look-up item for which the row should be refreshed
     * @return void
     * */
    function refreshLookupItem(item) {
        var $oldRow = $('#lookupItemRow-' + item.id);
        $oldRow.replaceWith(getLookupListingTemplate()({
            lookupItems: [item]
        }));
    }

    /**
     * Deletes the row that corresponds to the given lookup item 
     * or simply empties the contents of the cell that contains the translation for the current language
     * @param {Object} item The item for which the update should be carried out
     * @param {Boolean} onlyUpdateTranslationCell Whether to simply empty the translation cell, in lieu of deleting the entire row
     * @return void
     * */
    function deleteLookupItemRow(item, onlyUpdateTranslationCell) {
        var $row = $('#lookupItemRow-' + item.id);
        if (onlyUpdateTranslationCell) {
            $row.find('td[rel=translatedLabelCell]').html('-');
        } else {
            $row.remove();
        }
    }

    /**
     * Reloads the current lookup items list
     * The lookup item type and language are read from their respective selectors
     * @return void
     * */
    function reloadLookupItems() {
        var lookupType = $ctlTypeSelector.val();
        var lookupLang = $ctlLangSelector.val();

        toggleBusy(true);
        clearMessage($ctlListingResultContainer);

        $.ajax(getLoadLookupDataUrl(), {
            cache: false,
            dataType: 'json',
            type: 'GET',
            data: {
                type: lookupType,
                lang: lookupLang
            }
        }).done(function(data) {
            toggleBusy(false);
            if (data && data.success && data.items) {
                cleanupLookupItems();
                renderLookupItems(data.items, false);
                $.each(data.items, function(idx, item) {
                    currentItems[item.id] = item;
                });
            } else {
                displayMessage($ctlListingResultContainer, false, abp01LookupMgmtL10n.errListingFailGeneric);
            }
        }).fail(function() {
            toggleBusy(false);
            displayMessage($ctlListingResultContainer, false, abp01LookupMgmtL10n.errListingFailNetwork);
        });
    }

    /**
     * Manages the actual lookup item creation process:
     * - reads the required values;
     * - progress indicator lifecycle;
     * - displays operation result messages;
     * - updates the lookup item listing if required;
     * - fires the AJAX call.
     * @return void
     * */
    function createLookupItem() {        
        toggleBusy(true, $('#TB_window'));
        clearMessage($ctlOperationResultContainer);

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
            toggleBusy(false);
            if (data && data.success) {
                currentItems[data.item.id] = data.item;
                renderLookupItems([data.item], true);
                clearForm();
                displayMessage($ctlOperationResultContainer, true, abp01LookupMgmtL10n.msgSaveOk);
            } else {
                displayMessage($ctlOperationResultContainer, false, data.message || abp01LookupMgmtL10n.errFailGeneric);
            }
        }).fail(function() {
            toggleBusy(false);
            displayMessage($ctlOperationResultContainer, false, abp01LookupMgmtL10n.errFailNetwork);
        });
    }

    /**
     * Manages the actual lookup item modification process:
     * - reads the required values;
     * - progress indicator lifecycle;
     * - displays the operation result message;
     * - updates the lookup item listing if required;
     * - fires the AJAX call.
     * @return void
     * */
    function modifyLookupItem() {
        var defaultLabel = $ctlLookupItemDefaultLabel.val();
        var translatedLabel = $ctlLookupItemTranslatedLabel.val();

        toggleBusy(true, $('#TB_window'));
        clearMessage($ctlOperationResultContainer);

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
            toggleBusy(false);
            if (data && data.success) {
                editingItem.defaultLabel = defaultLabel;
                editingItem.hasTranslation = !!translatedLabel;
                editingItem.label = editingItem.hasTranslation ? translatedLabel : defaultLabel;

                currentItems[editingItem.id] = editingItem;
                refreshLookupItem(editingItem);

                displayMessage($ctlOperationResultContainer, true, abp01LookupMgmtL10n.msgSaveOk);
            } else {
                displayMessage($ctlOperationResultContainer, false, data.message || abp01LookupMgmtL10n.errFailGeneric);
            }
        }).fail(function() {
            toggleBusy(false);
            displayMessage($ctlOperationResultContainer, false, abp01LookupMgmtL10n.errFailNetwork);
        });
    }

    /**
     * Manages the actual lookup item deletion process:
     * - reads the required values;
     * - progress indicator lifecycle;
     * - displays the operation result message;
     * - updates the lookup listing if required;
     * - fires the AJAX call.
     * @return void
     * */
    function deleteLookupItem() {
        var lang = $ctlLangSelector.val();
        var deleteOnlyLang = $ctlDeleteOnlyLangTranslation.is(':checked');

        toggleBusy(true, $('#TB_window'));
        clearMessage($ctlDeleteOperationResultContainer);
        clearMessage($ctlListingResultContainer);

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
            toggleBusy(false);
            if (data && data.success) {
                deleteLookupItemRow(editingItem, deleteOnlyLang || lang === '_default');                

                delete currentItems[editingItem.id];
                editingItem = null;

                closeDeleteDialog();
                displayMessage($ctlListingResultContainer, true, abp01LookupMgmtL10n.msgDeleteOk);
            } else {
                displayMessage($ctlDeleteOperationResultContainer, false, data.message || abp01LookupMgmtL10n.errDeleteFailedGeneric);
            }
        }).fail(function() {
            toggleBusy(false);
            displayMessage($ctlDeleteOperationResultContainer, false, abp01LookupMgmtL10n.errDeleteFailedNetwork);
        });
    }

    /**
     * Saves the lookup item in the current form.
     * If we are editing an item, then modifyLookupItem() will be called.
     * if we are adding a new item, then createLookupItem() will be called.
     * @return void
     * */
    function saveLookupItem() {
        if (!editingItem) {
            createLookupItem();
        } else {
            modifyLookupItem();
        }
    }

    /**
     * Show the lookup item editor
     * @param {String} currentItemId The identifier of the item being edited, or null if we are adding a new item
     * @return void
     * */
    function showEditor(currentItemId) {
        var lang = $ctlLangSelector.val();
        var langLabel = $ctlLangSelector.find('option:selected').text();
        var title = !!currentItemId 
            ? abp01LookupMgmtL10n.editItemTitle 
            : abp01LookupMgmtL10n.addItemTitle;

        var height = 157;
        var $translatedLabelFieldLine = $ctlLookupItemTranslatedLabel.closest('div.abp01-form-line');

        //if the selected language is other than the default one
        //also show the translated label field
        //this way we allow setting the translated label 
        //without the user having to take an extra action        
        if (lang !== '_default') {
            height = 210;
            $translatedLabelFieldLine.show();
            $translatedLabelFieldLine.find('span[rel="abp01-languageDetails"]')
                .html('(' + langLabel + ')');
        } else {
            $translatedLabelFieldLine.hide();
        }

        //set the initial values, if given
        if (currentItemId) {
            editingItem = currentItems[currentItemId] || null;
            if (editingItem) {
                $ctlLookupItemDefaultLabel.val(editingItem.defaultLabel);
                $ctlLookupItemTranslatedLabel.val(editingItem.hasTranslation ? editingItem.label : '');
            }
        } else {
            $ctlLookupItemDefaultLabel.val('');
            $ctlLookupItemTranslatedLabel.val('');
            editingItem = null;
        }

        //show the editor
        clearMessage($ctlOperationResultContainer);
        tb_show(title, '#TB_inline?width=' + 450 + '&height=' + height + '&inlineId=abp01-lookup-item-form');
    }

    /**
     * Shows the lookup item deletion dialog for the given item id.
     * Also sets the currently edited item to the item that corresponds to the given ID.
     * @param {Integer} currentItemId The identifier of the item to be deleted
     * @return void
     * */
    function showDeleteDialog(currentItemId) {
        editingItem = currentItems[currentItemId];
        tb_show(abp01LookupMgmtL10n.ttlConfirmDelete, '#TB_inline?width=450&height=180&inlineId=abp01-lookup-item-delete-form');
    }

    /**
     * Closes the currently open lookup item editor.
     * Upon doing so, it will also reset the field values
     * @return void
     * */
    function closeEditor() {
        //reset field values
        clearForm();
        editingItem = null;
        //close the window
        clearMessage($ctlOperationResultContainer);
        tb_remove();
    }

    /**
     * Closes the currently open lookup item deletion dialog
     * @return void
     * */
    function closeDeleteDialog() {
        editingItem = null;
        $ctlDeleteOnlyLangTranslation.prop('checked', false);
        clearMessage($ctlDeleteOperationResultContainer);
        tb_remove();
    }

    /**
     * Set default styles for the blockUI overlay manager
     * @return void
     *  */
    function initBlockUIDefaultStyles() {
        $.blockUI.defaults.css = {
            width: '100%',
            height: '100%'
        };
    }

    /**
     * Initializes the current page controls:
     * - retain references to elements we are repeatedly using
     * - bind even listeners
     * @return void
     * */
    function initControls() {
        //result containers - they serve as display containers for various operations results
        $ctlListingResultContainer = $('#abp01-lookup-listing-result');
        $ctlOperationResultContainer = $('#abp01-lookup-operation-result');
        $ctlDeleteOperationResultContainer = $('#abp01-lookup-delete-operation-result');

        //selection controls
        $ctlTypeSelector = $('#abp01-lookupTypeSelect')
            .change(reloadLookupItems);
        $ctlLangSelector = $('#abp01-lookupLangSelect')
            .change(reloadLookupItems);
    
        //the listing
        $ctlLookupListing = $('#abp01-admin-lookup-listing');

        //form inputss
        $ctlLookupItemDefaultLabel = $('#abp01-lookup-item-defaultLabel');
        $ctlLookupItemTranslatedLabel = $('#abp01-lookup-item-translatedLabel');
        $ctlDeleteOnlyLangTranslation = $('#abp01-lookup-item-deleteOnlyLang');

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

    /**
     * Reads the global variables that represent the current state/context and stores them in the "context" variable.
     * @return void
     * */
    function initContext() {
        context = getContext();
    }

    /**
     * Bootstrap everything together
     * */
    $(document).ready(function() {
        initContext();
        initControls();
        initBlockUIDefaultStyles();
        reloadLookupItems();
    });
})(jQuery);