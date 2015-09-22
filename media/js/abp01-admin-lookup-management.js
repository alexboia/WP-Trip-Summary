(function($) {
    "use strict";

    /**
     * Current form controls
     * */

    var progressBar = null;
    var $ctlTypeSelector = null;
    var $ctlLangSelector = null;
    var $ctlLookupListing = null;

    /**
     * Cached template references
     * */
    var tplLookupListing = null;

    /**
     * Current form state
     * */

    var context = null;

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
            }
        }).fail(function() {
            toggleBusy(false);
        });
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

    function initControls() {
        $ctlTypeSelector = $('#abp01-lookupTypeSelect')
            .change(reloadLookupItems);
        $ctlLangSelector = $('#abp01-lookupLangSelect')
            .change(reloadLookupItems);
        $ctlLookupListing = $('#abp01-admin-lookup-listing');
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