(function ($) {
    "use strict";

	var $ctrlSettingsForm = null;
	var $ctrlSettingsFormBeacon = null;
	var $ctrlSettingsSaveResult = null;
    var progressBar = null;
    var context = null;

	function getContext() {
		return {
			nonce: window['abp01_nonce'] || null,
			ajaxSaveAction: window['abp01_ajaxSaveAction'] || null,
			ajaxBaseUrl: window['abp01_ajaxBaseUrl'] || null
		};
	}

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
    
    function getFormSaveUrl() {
    	return URI(context.ajaxBaseUrl)
    		.addSearch('action', context.ajaxSaveAction)
    		.addSearch('abp01_nonce_settings', context.nonce)
    		.toString();
    }
    
    function displaySaveResult(success, message) {
    	$ctrlSettingsSaveResult
    		.removeClass('notice')
    		.removeClass('error')
    		.html('');

    	if (success) {
    		$ctrlSettingsSaveResult.addClass('notice');
    	} else {
    		$ctrlSettingsSaveResult.addClass('error');
    	}
    	
    	$ctrlSettingsSaveResult
    		.html('<p>' + message + '</p>')
    		.show();
    		
		$('body,html').animate({
            scrollTop: $ctrlSettingsFormBeacon.offset().top
        }, 500);
    }
    
    function hideSaveResult() {
    	$ctrlSettingsSaveResult.hide()
    		.html('');
    }

    function saveSettings(onReady) {
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
    
    function initControls() {
    	$ctrlSettingsForm = $('#abp01-settings-form');
    	$ctrlSettingsFormBeacon = $('#abp01-settings-form-beacon');
    	$ctrlSettingsSaveResult = $('#abp01-settings-save-result');
    }

    function initBlockUIDefaultStyles() {
        $.blockUI.defaults.css = {
            width: '100%',
            height: '100%'
        };
    }

    function initSaveListener() {
        $('#abp01-submit-settings').click(saveSettings);
    }
    
    function initFormState() {
    	context = getContext();
    }

    $(document).ready(function() {
        initFormState();
        initControls();
        initBlockUIDefaultStyles();
        initSaveListener();
    });
})(jQuery);