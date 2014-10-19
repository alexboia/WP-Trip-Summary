(function($) {
    "use strict";

    /**
     * Define available tour types
     * */

    var TOUR_TYPE_BIKE = 'bike';
    var TOUR_TYPE_HIKING = 'hiking';
    var TOUR_TYPE_TRAIN_RIDE = 'trainRide';

    /**
     * Define server-side track upload error codes
     * */

    var UPLOAD_OK = 0;
    var UPLOAD_INVALID_MIME_TYPE = 1;
    var UPLOAD_TOO_LARGE = 2;
    var UPLOAD_NO_FILE = 3;
    var UPLOAD_INTERNAL_ERROR = 4;
    var UPLOAD_STORE_FAILED = 5;
    var UPLOAD_NOT_VALID = 6;
    var UPLOAD_FAILED = 99;

    /**
     * Current state information
     * */

    var baseTitle = null;
    var progressBar = null;
    var currentTourType = null;
    var formInfoRendered = false;
    var formMapRendered = false;
    var firstTime = true;
    var uploader = null;
    var context = null;
    var map = null;

    /**
     * Configuration objects - they are initialized at startup
     * */

    var typeSelectRenderers = {};
    var typeTitles = {};
    var tabHandlers = {};
    var uploaderErrors = {
        server: {},
        client: {}
    };

    /**
     * Cache jQuery object references
     * */

    var $ctrlEditorTabs = null;
    var $ctrlResetTechBox = null;
    var $ctrlTitleContainer = null;
    var $ctrlFormInfoContainer = null;
    var $ctrlFormMapContainer = null;
    var $ctrlEditor = null;
    var $ctrlSave = null;

    /**
     * Cache rendered content
     * */

    var contentFormInfoBikeTour = null;
    var contentFormInfoHikingTour = null;
    var contentFormInfoTrainRide = null;
    var contentFormInfoUnselected = null;

    var contentFormMapUnselected = null;
    var contentFormMapUploaded = null;

    /**
     * Templates and rendering functions
     * */

    function renderFormMapUnselected() {
        if (contentFormMapUnselected == null) {
            contentFormMapUnselected = kite('#tpl-abp01-formMap-unselected')();
        }
        return contentFormMapUnselected;
    }

    function renderFormInfoBikeTour() {
        if (contentFormInfoBikeTour == null) {
            contentFormInfoBikeTour = kite('#tpl-abp01-formInfo-bikeTour')();
        }
        return contentFormInfoBikeTour;
    }

    function renderFormInfoHikingTour() {
        if (contentFormInfoHikingTour == null) {
            contentFormInfoHikingTour = kite('#tpl-abp01-formInfo-hikingTour')();
        }
        return contentFormInfoHikingTour;
    }

    function renderFormInfoTrainRideTour() {
        if (contentFormInfoTrainRide == null) {
            contentFormInfoTrainRide = kite('#tpl-abp01-formInfo-trainRide')();
        }
        return contentFormInfoTrainRide;
    }

    function renderFormMapUploaded() {
        if (contentFormMapUploaded == null) {
            contentFormMapUploaded = kite('#tpl-abp01-formMap-uploaded')();
        }
        return contentFormMapUploaded;
    }

    function renderFormInfoUnselected() {
        if (contentFormInfoUnselected == null) {
            contentFormInfoUnselected = kite('#tpl-abp01-formInfo-unselected')();
        }
        return contentFormInfoUnselected;
    }

    function updateTitle(title) {
        if (title) {
            $ctrlTitleContainer.html([baseTitle, title].join(' - '));
        } else {
            $ctrlTitleContainer.html(baseTitle);
        }
    }

    /**
     * Window state management
     * */

    function toastMessage(success, message) {
        if (success) {
            toastr.success(message);
        } else {
            toastr.error(message);
        }
    }

    function showProgress(progress, text) {
        if ($ctrlEditor.data('isBlocked')) {
            return;
        }

        var isDeterminate = progress !== false;
        if (progressBar != null) {
            progressBar.update({
                progress: progress,
                message: text
            });
        } else {
            progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                $target: $ctrlEditor,
                determinate: isDeterminate,
                progress: progress,
                message: text
            });
        }
    }

    function hideProgress() {
        if (progressBar) {
            progressBar.destroy();
            progressBar = null;
        }
    }

    /**
     * Reset button management functions
     * */

    function executeResetAction() {
        var handler = $ctrlResetTechBox.data('formInfoResetHandler');
        if (!handler || !$.isFunction(handler)) {
            handler = $ctrlResetTechBox.data('formMapResetHandler');
        }
        if (handler && $.isFunction(handler)) {
            handler();
        }
    }

    function toggleFormInfoReset(enable) {
        if (enable) {
            var resetHandler = arguments.length == 2 ? arguments[1] : null;
            $ctrlResetTechBox.text(abp01MainL10n.btnClearInfo).show();
            $ctrlResetTechBox.data('formInfoResetHandler', resetHandler);
        } else {
            $ctrlResetTechBox.hide();
            $ctrlResetTechBox.data('formInfoResetHandler', null);
        }
    }

    function toggleFormMapReset(enable) {
        if (enable) {
            var resetHandler = arguments.length == 2 ? arguments[1] : null;
            $ctrlResetTechBox.text(abp01MainL10n.btnClearTrack).show();
            $ctrlResetTechBox.data('formMapResetHandler', resetHandler);
        } else {
            $ctrlResetTechBox.hide();
            $ctrlResetTechBox.data('formMapResetHandler', null);
        }
    }

    function clearInputValues($container) {
        var $input = $container.find('input,select,textarea');
        $input.each(function(idx, el) {
            var $field = $(this);
            var tagName = $field.prop('tagName');

            if (!tagName) {
                return;
            }

            tagName = tagName.toLowerCase();
            if (tagName == 'input') {
                var inputType = $field.attr('type');
                inputType = inputType.toLowerCase();
                if (inputType == 'text') {
                    $field.val('');
                } else if (inputType == 'checkbox') {
                    $field.prop('checked', false);
                    if ($field.iCheck) {
                        $field.iCheck('update');
                    }
                }
            } else if (tagName == 'select') {
                $field.val('0');
            }
        });
    }

    /**
     * Checkbox management functions
     * */

    function prepareCheckboxes($container) {
        $container.find('input[type=checkbox]').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        });
    }

    function destroyCheckboxes($container) {
        $container.find('input[type=checkbox]')
            .iCheck('destroy');
    }

    function selectFormInfoTourType(type, clearForm) {
        currentTourType = type;
        $ctrlFormInfoContainer.empty();

        if (typeof typeSelectRenderers[type] == 'function') {
            $ctrlFormInfoContainer.html(typeSelectRenderers[type]());
            prepareCheckboxes($ctrlFormInfoContainer);
            toggleFormInfoReset(true, resetFormInfo);
            updateTitle(typeTitles[type]);

            if (clearForm) {
                clearInputValues($ctrlFormInfoContainer);
            }

            $ctrlSave.show();
        } else {
            updateTitle(null);
            toggleFormInfoReset(false);
            currentTourType = null;
            $ctrlSave.hide();
        }
    }

    /**
     * Tour info editor management functions
     * */

    function initFormInfo() {
        typeSelectRenderers[TOUR_TYPE_BIKE] = renderFormInfoBikeTour;
        typeSelectRenderers[TOUR_TYPE_HIKING] = renderFormInfoHikingTour;
        typeSelectRenderers[TOUR_TYPE_TRAIN_RIDE] = renderFormInfoTrainRideTour;

        typeTitles[TOUR_TYPE_BIKE] = abp01MainL10n.lblTypeBiking;
        typeTitles[TOUR_TYPE_HIKING] = abp01MainL10n.lblTypeHiking;
        typeTitles[TOUR_TYPE_TRAIN_RIDE] = abp01MainL10n.lblTypeTrainRide;
    }

    function resetFormInfo() {
        $ctrlSave.hide();
        clearInputValues($ctrlFormInfoContainer);
        clearInfo();
    }

    function switchToFormInfoSelection() {
        currentTourType = null;

        toggleFormInfoReset(false);
        destroyCheckboxes($ctrlFormInfoContainer);
        updateTitle(null);

        $ctrlFormInfoContainer.empty()
            .html(renderFormInfoUnselected());
    }

    function showFormInfo($container) {
        if (!formInfoRendered) {
            var crType = window.abp01_tourType || null;
            if (!crType) {
                $container.html(renderFormInfoUnselected());
            } else {
                selectFormInfoTourType(crType, false);
            }
            formInfoRendered = true;
        }

        toggleFormMapReset(false);
        if (!currentTourType) {
            toggleFormInfoReset(false);
        } else {
            toggleFormInfoReset(true, resetFormInfo);
        }
    }

    function getFormInfoValues($container) {
        var $input = $container.find('input,select,textarea');
        var values = {
            type: currentTourType
        };

        function isMultiCheckbox(inputName) {
            return inputName.indexOf('[]') == inputName.length - 2;
        }

        function getSimpleCheckboxName(inputName) {
            return inputName.substring(0, inputName.length - 2);
        }

        function addFormValue(name, value, isMultiple) {
            if (isMultiple) {
                if (typeof values[name] == 'undefined') {
                    values[name] = [];
                }
                if (value !== null) {
                    values[name].push(value);
                }
            } else {
                values[name] = value;
            }
        }

        $input.each(function(idx, el) {
            var $field = $(this);
            var tagName = $field.prop('tagName');
            var inputName = $field.attr('name');
            var inputType = $field.attr('type');

            if (!tagName || !inputName) {
                return;
            }

            tagName = tagName.toLowerCase();
            inputName = inputName.replace('ctrl_abp01_', '');

            if (tagName == 'input') {
                if (inputType == 'text') {
                    addFormValue(inputName, $field.val());
                } else if (inputType == 'checkbox') {
                    var checked = $field.is(':checked');
                    var isMultiple = isMultiCheckbox(inputName);
                    if (isMultiple) {
                        inputName = getSimpleCheckboxName(inputName);
                    }
                    addFormValue(inputName, (checked ? $field.val() : null), isMultiple);
                }
            } else if (tagName == 'select') {
                addFormValue(inputName, $field.val(), false);
            } else if (tagName == 'textarea') {
                addFormValue(inputName, $field.val(), false);
            }
        });

        return values;
    }

    function saveInfo() {
        showProgress(false, abp01MainL10n.lblSavingDataWait);
        $.ajax(getAjaxEditInfoUrl(), {
            type: 'POST',
            dataType: 'json',
            data: getFormInfoValues($ctrlFormInfoContainer)
        }).done(function(data, status, xhr) {
            hideProgress();
            if (data) {
                if (data.success) {
                    toastMessage(true, abp01MainL10n.lblDataSaveOk);
                } else {
                    toastMessage(false, data.message || abp01MainL10n.lblDataSaveFail);
                }
            } else {
                toastMessage(false, abp01MainL10n.lblDataSaveFail);
            }
        }).fail(function() {
            hideProgress();
            toastMessage(false, abp01MainL10n.lblDataSaveFailNetwork);
        });
    }

    function clearInfo() {
        showProgress(false, abp01MainL10n.lblClearingInfoWait);
        $.ajax(getAjaxClearInfoUrl(), {
            type: 'POST',
            dataType: 'json',
            data: {}
        }).done(function(data, status, xhr) {
            hideProgress();
            if (data) {
                if (data.success) {
                    switchToFormInfoSelection();
                    toastMessage(true, abp01MainL10n.lblClearInfoOk);
                } else {
                    toastMessage(false, data.message || abp01MainL10n.lblClearInfoFail);
                }
            } else {
                toastMessage(false, abp01MainL10n.lblClearInfoFail);
            }
        }).fail(function() {
            hideProgress();
            toastMessage(false, abp01MainL10n.lblClearInfoFailNetwork);
        });
    }

    /**
     * Tour map editor management functions
     * */

    function initFormMap() {
        uploaderErrors.client[plupload.FILE_SIZE_ERROR] = abp01MainL10n.errPluploadTooLarge;
        uploaderErrors.client[plupload.FILE_EXTENSION_ERROR] = abp01MainL10n.errPluploadFileType;
        uploaderErrors.client[plupload.IO_ERROR] = abp01MainL10n.errPluploadIoError;
        uploaderErrors.client[plupload.SECURITY_ERROR] = abp01MainL10n.errPluploadSecurityError;
        uploaderErrors.client[plupload.INIT_ERROR] = abp01MainL10n.errPluploadInitError;
        uploaderErrors.client[plupload.HTTP_ERROR] = abp01MainL10n.errPluploadHttp;

        uploaderErrors.server[UPLOAD_INVALID_MIME_TYPE] = abp01MainL10n.errServerUploadFileType;
        uploaderErrors.server[UPLOAD_TOO_LARGE] = abp01MainL10n.errServerUploadTooLarge;
        uploaderErrors.server[UPLOAD_NO_FILE] = abp01MainL10n.errServerUploadNoFile;
        uploaderErrors.server[UPLOAD_INTERNAL_ERROR] = abp01MainL10n.errServerUploadInternal;
        uploaderErrors.server[UPLOAD_FAILED] = abp01MainL10n.errServerUploadFail;
    }

    function resetFormMap() {
        map.destroyMap();
        map = null;

        context.hasTrack = false;
        $ctrlFormMapContainer.empty().html(renderFormMapUnselected());

        toggleFormMapReset(false);
        createTrackUploader();
    }

    function showFormMap($container) {
        if (!formMapRendered) {
            formMapRendered = true;
            if (!context.hasTrack) {
                $container.html(renderFormMapUnselected());
                createTrackUploader();
            } else {
                showMap();
            }
        } else {
            if (map != null) {
                map.forceRedraw();
            }
        }

        toggleFormInfoReset(false);
        if (!context.hasTrack) {
            toggleFormMapReset(false);
        } else {
            toggleFormMapReset(true, clearTrack);
        }
    }

    function clearTrack() {
        showProgress(false, abp01MainL10n.lblClearingTrackWait);
        $.ajax(getAjaxClearTrackUrl(), {
            type: 'POST',
            dataType: 'json',
            cache: false
        }).done(function(data, status, xhr) {
            hideProgress();
            if (data && data.success) {
                resetFormMap();
                toastMessage(true, abp01MainL10n.lblTrackClearOk);
            } else {
                toastMessage(false, data.message || abp01MainL10n.lblTrackClearFail);
            }
        }).fail(function(xhr, status, error) {
            hideProgress();
            toastMessage(false, abp01MainL10n.lblTrackClearFailNetwork);
        });
    }

    function createTrackUploader() {
        if (uploader != null) {
            return;
        }

        uploader = new plupload.Uploader({
            browse_button: 'abp01-track-selector',
            filters: {
                max_file_size: window.abp01_uploadMaxFileSize || 10485760,
                mime_types: [
                    { title: abp01MainL10n.lblPluploadFileTypeSelector,
                        extensions: 'gpx' }
                ]
            },
            runtimes: 'html5,flash,silverlight',
            flash_swf_url: window.abp01_flashUploaderUrl || '',
            silverlight_xap_url: window.abp01_xapUploaderUrl || '',
            multipart: true,
            multipart_params: {},
            chunk_size: window.abp01_uploadChunkSize || 102400,
            url: getAjaxUploadTrackUrl(),
            multi_selection: false,
            urlstream_upload: true,
            unique_names: false,
            file_data_name: window.abp01_uploadKey || 'file',
            init: {
                FilesAdded: handleUploaderFilesAdded,
                UploadProgress: handleUploaderProgress,
                UploadComplete: handleUploaderCompleted,
                ChunkUploaded: handleChunkCompleted,
                Error: handleUploaderError
            }
        });

        uploader.init();
    }

    function destroyTrackUploader() {
        if (uploader == null) {
            return;
        }

        uploader.unbindAll();
        uploader.destroy();
        uploader = null;
    }

    function handleUploaderError(upl, error) {
        console.log(error);
        uploader.disableBrowse(false);
        uploader.refresh();
        hideProgress();
        toastMessage(false, getTrackUploaderErrorMessage(error));
    }

    function handleUploaderFilesAdded(upl, files) {
        if (!files || !files.length) {
            return;
        }

        var file = files[0];
        if (file.size <= 102400) {
            uploader.setOption('chunk_size', Math.round(file.size / 2));
        } else {
            uploader.setOption('chunk_size', 102400);
        }

        uploader.disableBrowse(true);
        uploader.start();
    }

    function handleUploaderCompleted(upl) {
        context.hasTrack = true;
        uploader.disableBrowse(false);

        destroyTrackUploader();
        toggleFormMapReset(true, clearTrack);
        toastMessage(true, abp01MainL10n.lblTrackUploaded);
        showMap();
    }

    function handleChunkCompleted(upl, file, info) {
        var status = 0;
        var response = info.response || null;
        if (response != null) {
            try {
                response = JSON.parse(response);
                status = parseInt(response.status || 0);
            } catch (e) {
                status = UPLOAD_FAILED;
            }
        } else {
            status = UPLOAD_FAILED;
        }

        if (status != UPLOAD_OK) {
            hideProgress();
            uploader.stop();
            uploader.disableBrowse(false);
            toastMessage(false, getTrackUploaderErrorMessage({
                server: true,
                code: status
            }));
        }
    }

    function handleUploaderProgress(upl, file) {
        if (upl.state == plupload.STARTED) {
            showProgress(file.percent / 100, abp01MainL10n.lblTrackUploadingWait + ': ' + file.percent + '%');
        }
    }

    function getTrackUploaderErrorMessage(err) {
        var message = null;
        if (err.hasOwnProperty('server') && err.server === true) {
            message = uploaderErrors.server[err.code] || null;
        } else {
            message = uploaderErrors.client[err.code] || null;
        }
        if (!message) {
            message = uploaderErrors.server[UPLOAD_FAILED];
        }
        return message;
    }

    function showMap() {
        map = $ctrlFormMapContainer.empty()
            .html(renderFormMapUploaded())
            .find('#abp01-map')
            .mapTrack({
                iconBaseUrl: context.imgBase,
                trackDataUrl: getAjaxLoadTrackUrl(),
                handlePreLoad: function() {
                    showProgress(false, abp01MainL10n.lblGeneratingPreview);
                },
                handleLoad: function(success) {
                    hideProgress();
                }
            });
    }

    /**
     * Main editor management functions
     * */

    function getContext() {
        return {
            nonce: $('#abp01-nonce').val(),
            imgBase: window['abp01_imgBase'] || null,
            nonceGet: $('#abp01-nonce-get').val(),
            postId: window['abp01_postId'] || 0,
            hasTrack: window['abp01_hasTrack'] || 0,
            ajaxBaseUrl: window['abp01_ajaxUrl'] || null,
            ajaxLoadTrackAction: window['abp01_ajaxGetTrackAction'] || null,
            ajaxUploadTrackAction: window['abp01_ajaxUploadTrackAction'] || null,
            ajaxEditInfoAction: window['abp01_ajaxEditInfoAction'] || null,
            ajaxClearTrackAction: window['abp01_ajaxClearTrackAction'] || null,
            ajaxClearInfoAction: window['abp01_ajaxClearInfoAction'] || null
        };
    }

    function initEditorState() {
        context = getContext();
        baseTitle = window['abp01_baseTitle'] || '';
    }

    function initEditorControls() {
        $ctrlEditor = $('#abp01-techbox-editor');
        $ctrlTitleContainer = $('#ctrl_abp01_editorTitle');
        $ctrlFormInfoContainer = $('#abp01-form-info');
        $ctrlFormMapContainer = $('#abp01-form-map');
        $ctrlResetTechBox = $('#abp01-resetTechBox');
        $ctrlSave = $('#abp01-saveTechBox');
    }

    function initToastMessages() {
        $.extend(toastr.options, {
            iconClasses: {
                error: 'abp01-toast-error',
                info: 'abp01-toast-info',
                success: 'abp01-toast-success',
                warning: 'abp01-toast-warning'
            },
            target: '#abp01-editor-content',
            positionClass: 'toast-bottom-right',
            timeOut: 4000
        });
    }

    function initEventHandlers() {
        $ctrlResetTechBox .click(function() {
            executeResetAction();
        });

        $ctrlSave.click(function() {
            saveInfo();
        });

        $(document)
            .on('click', 'a[data-action=abp01-openTechBox]', {}, function() {
                openEditor();
            })
            .on('click', 'a[data-action=abp01-closeTechBox]', {}, function() {
                $.unblockUI();
            })

            .on('click', 'a[data-action=abp01-typeSelect]', {}, function() {
                selectFormInfoTourType($(this).attr('data-type'), true);
            });
    }

    function initEditor() {
        initEditorState();
        initToastMessages();
        initEditorControls();
        initEventHandlers();
    }

    function openEditor() {
        var $window = $(window);
        var blockUICss = $.blockUI.defaults.css;

        $.blockUI({
            message: $ctrlEditor,
            css: {
                top: ($window.height() - blockUICss.height) / 2,
                left: ($window.width() - blockUICss.width) / 2,
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.7)'
            },
            onBlock: function() {
                if (firstTime) {
                    initTabs();
                    firstTime = false;
                } else {
                    if (map != null && $ctrlFormMapContainer.is(':visible')) {
                        map.forceRedraw();
                    }
                }
            }
        });
    }

    function getAjaxEditInfoUrl() {
        var context = getContext();
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxEditInfoAction)
            .addSearch('abp01_nonce', context.nonce || '')
            .addSearch('abp01_postId', context.postId || 0)
            .toString();
    }

    function getAjaxClearInfoUrl() {
        var context = getContext();
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxClearInfoAction)
            .addSearch('abp01_nonce', context.nonce || '')
            .addSearch('abp01_postId', context.postId || 0)
            .toString();
    }

    function getAjaxUploadTrackUrl() {
        var context = getContext();
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxUploadTrackAction)
            .addSearch('abp01_nonce', context.nonce || '')
            .addSearch('abp01_postId', context.postId || 0)
            .toString();
    }

    function getAjaxLoadTrackUrl() {
        var context = getContext();
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxLoadTrackAction)
            .addSearch('abp01_nonce_get', context.nonceGet || '')
            .addSearch('abp01_postId', context.postId || '')
            .toString();
    }

    function getAjaxClearTrackUrl() {
        var context = getContext();
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxClearTrackAction)
            .addSearch('abp01_nonce', context.nonce || '')
            .addSearch('abp01_postId', context.postId || '')
            .toString();
    }

    /**
     * Editor tabs management functions
     * */

    function initTabs() {
        tabHandlers['abp01-form-info'] = showFormInfo;
        tabHandlers['abp01-form-map'] = showFormMap;

        $ctrlEditorTabs = $('#abp01-editor-content').easytabs({
            animate: false,
            tabActiveClass: 'abp01-tab-active',
            panelActiveClass: 'abp01-tabContentActive',
            defaultTab: '#abp01-tab-info',
            updateHash: false
        });

        $ctrlEditorTabs.bind('easytabs:after', function(e, $clicked, $target, settings) {
            selectTab($target);
        });

        selectTab($('#abp01-form-info'));
    }

    function selectTab($target) {
        var target = $target.attr('id');
        if (typeof tabHandlers[target] == 'function') {
            tabHandlers[target]($target);
        }
    }

    $(document).ready(function() {
        $.blockUI.defaults.css = {
            width: 720,
            height: 590
        };

        initEditor();
        initFormInfo();
        initFormMap();
    });
})(jQuery);