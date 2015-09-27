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
     * @param Array items The lookup items
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

    function refreshLookupItem(item) {
        var $oldRow = $('#lookupItemRow-' + item.id);
        $oldRow.replaceWith(getLookupListingTemplate()({
            lookupItems: [item]
        }));
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
                
            }
        }).fail(function() {
            toggleBusy(false);
        });
    }

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
                editingItem.label = !!translatedLabel ? translatedLabel : defaultLabel;
                editingItem.defaultLabel = defaultLabel;

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

    function saveLookupItem() {
        if (!editingItem) {
            createLookupItem();
        } else {
            modifyLookupItem();
        }
    }

    /**
     * Show the lookup item editor
     * @param String currentItemId The identifier of the item being edited, or null if we are adding a new item
     * @return void
     * */
    function showEditor(currentItemId) {
        var lang = $ctlLangSelector.val();
        var langLabel = $ctlLangSelector.find('option:selected').text();
        var title = !!currentItemId 
            ? abp01LookupMgmtL10n.editItemTitle 
            : abp01LookupMgmtL10n.addItemTitle;

        var height = 157;
        var $translatedLabelFieldLine = $ctlLookupItemDefaultLabel.closest('div.abp01-form-line');

        //if the selected language is other than the default one
        //also show the translated label field
        //this way we allow setting the translated label 
        //without the user having to take an extra action
        if (lang !== '_default') {
            height = 210;
            $translatedLabelFieldLine.show();
            $translatedLabelFieldLine.find('span[rel=abp01-languageDetails]')
                .html('(' + langLabel + ')');
        } else {
            $translatedLabelFieldLine.hide();
        }

        //set the initial values, if given
        if (currentItemId) {
            editingItem = currentItems[currentItemId] || null;
            if (editingItem) {
                $ctlLookupItemDefaultLabel.val(editingItem.defaultLabel);
                $ctlLookupItemTranslatedLabel.val(editingItem.label);
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
        $ctlListingResultContainer = $('#abp01-lookup-listing-result');
        $ctlOperationResultContainer = $('#abp01-lookup-operation-result');

        $ctlTypeSelector = $('#abp01-lookupTypeSelect')
            .change(reloadLookupItems);
        $ctlLangSelector = $('#abp01-lookupLangSelect')
            .change(reloadLookupItems);
        $ctlLookupListing = $('#abp01-admin-lookup-listing');

        $ctlLookupItemDefaultLabel = $('#abp01-lookup-item-defaultLabel');
        $ctlLookupItemTranslatedLabel = $('#abp01-lookup-item-translatedLabel');        

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

        //bind actions for form controls
        $('#abp01-cancel-lookup-item').click(closeEditor);
        $('#abp01-save-lookup-item').click(saveLookupItem);
    }

    function initFormState() {
        context = getContext();
    }

    $(document).ready(function() {
        initFormState();
        initControls();
        initBlockUIDefaultStyles();
        reloadLookupItems();
    });
})(jQuery);