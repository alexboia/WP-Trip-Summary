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

    /**
     * Cached template references
     * */
    var tplLookupListing = null;

    /**
     * Current form state
     * */

    var context = null;
    var currentItems = {};

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

    function toggleBusy(show) {
        if (show) {
            if (!progressBar) {
                var message = arguments.length == 2 
                    ? arguments[1] || abp01LookupMgmL10n.msgWorking 
                    : abp01LookupMgmL10n.msgWorking;

                progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                    $target: $('#wpwrap'),
                    message: message
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
     * Removes all the lookup items that are currently displayed in the listing table
     * @return void
     * */
    function cleanupLookupItems() {
        currentItems = {};
        $ctlLookupListing.find('tbody').html('');
    }

    /**
     * Renders the given lookup items and updates the listing table content
     * @param Array items The lookup items
     * @return void
     * */
    function renderLookupItems(items) {
        if (!tplLookupListing) {
            tplLookupListing = kite('#tpl-abp01-lookupDataRow');
        }
        var content = tplLookupListing({
            lookupItems: items
        });
        $ctlLookupListing.find('tbody').html(content);
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
                renderLookupItems(data.items);
                $.each(data.items, function(idx, item) {
                    currentItems[item.id] = item;
                });
            }
        }).fail(function() {
            toggleBusy(false);
        });
    }

    /**
     * Show the lookup item editor
     * @param String currentItemId The identifier of the item being edited, or null if we are adding a new item
     * @return void
     * */
    function showEditor(currentItemId) {
        var type = $ctlTypeSelector.val();
        var lang = $ctlLangSelector.val();
        var langLabel = $ctlLangSelector.find('option:selected').text();
        var title = !!currentItem 
            ? abp01LookupMgmL10n.editItemTitle 
            : abp01LookupMgmL10n.addItemTitle;

        var height = 147;
        var $translatedLabelFieldLine = $ctlLookupItemDefaultLabel.closest('div.abp01-form-line');

        //if the selected language is other than the default one
        //also show the translated label field
        //this way we allow setting the translated label 
        //without the user having to take an extra action
        if (lang !== '_default') {
            height = 200;
            $translatedLabelFieldLine.show();
            $translatedLabelFieldLine.find('span[rel=abp01-languageDetails]')
                .html('(' + langLabel + ')');
        } else {
            $translatedLabelFieldLine.hide();
        }

        //set the initial values, if given
        if (currentItemId) {
            var currentItem = currentItems[currentItemId] || null;
            if (currentItem) {
                $ctlLookupItemDefaultLabel.val(currentItem.defaultLabel);
                $ctlLookupItemTranslatedLabel.val(currentItem.label);
            }
        } else {
            $ctlLookupItemDefaultLabel.val('');
            $ctlLookupItemTranslatedLabel.val('');
        }

        //show the editor
        tb_show(title, '#TB_inline?width=' + 450 + '&height=' + height + '&inlineId=abp01-lookup-item-form');
    }

    /**
     * Closes the currently open lookup item editor.
     * Upon doing so, it will also reset the field values
     * @return void
     * */
    function closeEditor() {
        //reset field values
        $ctlLookupItemDefaultLabel.val('');
        $ctlLookupItemTranslatedLabel.val('');
        //close the window
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