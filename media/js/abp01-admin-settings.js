(function ($) {
    "use strict";
    
    /**
     * Current form controls
     *  */

	var $ctrlSettingsForm = null;
	var $ctrlSettingsFormBeacon = null;
	var $ctrlSettingsSaveResult = null;
    var progressBar = null;
    
    /**
     * Current form state
     *  */
    
    var context = null;

	/**
	 * Reads and returns the current form context/state:
     * - nonce - the nonce used to authenticate the AJAX calls made when saving the settings;
     * - ajaxSaveAction - AJAX admin action used to save the settings;
     * - ajaxBaseUrl - AJAX base URL used when saving the settings.
     * @return object The context object comprised of the above-mentioned properties
	 *  */
	function getContext() {
		return {
			nonce: window['abp01_nonce'] || null,
			ajaxSaveAction: window['abp01_ajaxSaveAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null
		};
	}

	/**
	 * Show or hide the progress indicator
	 * @param boolean show Whether to show or hide the progress indicator
	 * @return void
	 *  */
    function toggleBusy(show) {
        if (show) {
            if (progressBar == null) {
                progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                    $target: $('body'),
                    message: abp01SettingsL10n.msgSaveWorking
                });
            }
        } else {
            if (progressBar != null) {
                progressBar.destroy();
                progressBar = null;
            }
        }
    }
    
    /**
     * Construct the URL that will handle the form save action
     * @return string The constructed URL
     *  */
    function getFormSaveUrl() {
    	return URI(context.ajaxBaseUrl)
    		.addSearch('action', context.ajaxSaveAction)
    		.addSearch('abp01_nonce_settings', context.nonce)
    		.toString();
    }
    
    /**
     * Display the operation result
     * @param boolean success The success status
     * @param string message The message to display
     * @return void
     *  */
    function displaySaveResult(success, message) {
    	//first clear the result container
    	$ctrlSettingsSaveResult
    		.removeClass('notice')
    		.removeClass('error')
    		.html('');

		//style the message box according to success/error status
		//and show the message
		$ctrlSettingsSaveResult
			.addClass(success ? 'notice' : 'error')
			.html('<p>' + message + '</p>')
    		.show();

    	//scroll back to the top of the page
		$('body,html').scrollTop(0);
    }
    
    /**
     * Clears and hides the operation result
     * @return void
     *  */
    function hideSaveResult() {
    	$ctrlSettingsSaveResult.hide()
    		.html('');
    }

	/**
	 * Saves the settings form and displays the result. 
	 * The operation is async and a progress indicator is displayed while it's underway
	 * @return void
	 *  */
    function saveSettings() {
    	toggleBusy(true);
    	hideSaveResult();
    	$.ajax(getFormSaveUrl(), {
    		type: 'POST',
    		dataType: 'json',
    		cache: false,
    		data: $ctrlSettingsForm.serialize()
    	}).done(function (data, status, xhr) {
    		toggleBusy(false);
    		if (data && data.success) {
    			displaySaveResult(true, abp01SettingsL10n.msgSaveOk);
    		} else {
    			displaySaveResult(false, data.message || abp01SettingsL10n.errSaveFailGeneric);
    		}
    	}).fail(function (xhr, status, error) {
    		toggleBusy(false);
    		displaySaveResult(false, abp01SettingsL10n.errSaveFailNetwork);
    	});
    }
    
    /**
     * Initializes and caches controls references for future use
     * @return void
     *  */
    function initControls() {
    	$ctrlSettingsForm = $('#abp01-settings-form');
    	$ctrlSettingsFormBeacon = $('#abp01-settings-form-beacon');
    	$ctrlSettingsSaveResult = $('#abp01-settings-save-result');
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
	 * Initialize event listeners
	 * @return void
	 *  */
    function initListeners() {
        $('#abp01-submit-settings').click(saveSettings);
    }
    
    /**
     * Reads and saves the current form state. See getContext() for more information.
     * @return void
     *  */
    function initFormState() {
    	context = getContext();
    }

    $(document).ready(function() {
        initFormState();
        initControls();
        initBlockUIDefaultStyles();
        initListeners();
    });
})(jQuery);