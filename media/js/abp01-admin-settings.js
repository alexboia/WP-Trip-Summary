/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

    function scrollToTop() {
        $('body,html').scrollTop(0);
    }

    /**
     * Show or hide the progress indicator
     * @param show boolean Whether to show or hide the progress indicator
     * @return void
     *  */
    function toggleBusy(show) {
        if (show) {
            if (progressBar == null) {
                progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                    $target: $('#wpwrap'),
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
     * @param success boolean The success status
     * @param message string The message to display
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
        hideSaveResult();
        scrollToTop();
    	toggleBusy(true);
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

    function initColoPickers() {
        $('#abp01-trackLineColour').wpColorPicker();
    }

    function initTrackLineWeightStepper() {
        $('#abp01-trackLineWeight').abp01NumericStepper({
            maxValue: 10,
            defaultValue: 3
        });
    }

    $(document).ready(function() {
        initFormState();
        initControls();
        initBlockUIDefaultStyles();
        initColoPickers();
        initTrackLineWeightStepper();
        initListeners();
    });
})(jQuery);