(function ($) {
    "use strict";

    var progressBar = null;

    function toggleBusy(show) {
        if (show) {
            if (progressBar == null) {
                progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                    $target: $('body'),
                    message: 'Saving settings. Please wait...'
                });
            }
        } else {
            if (progressBar != null) {
                progressBar.destroy();
                progressBar = null;
            }
        }
    }

    function initBlockUIDefaultStyles() {
        $.blockUI.defaults.css = {
            width: '100%',
            height: '100%'
        };
    }

    function initSaveListener() {
        $('#abp01-submit-settings').click(function() {
            toggleBusy(true);
        });
    }

    $(document).ready(function() {
        initBlockUIDefaultStyles();
        initSaveListener();
    });
})(jQuery);