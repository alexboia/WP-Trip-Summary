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
    var settings = null;
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
    var $ctrlMapRetry = null;
    var $ctrlMapRetryContainer = null;

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
                }
            } else if (tagName == 'select') {
                if ($field[0].sumo) {
                    $field[0].sumo.unSelectAll();
                }
                $field.val('');
            }
        });
    }

    /**
     * Select boxes management functions
     * */

    function prepareSelectBoxes($container) {
        $container.find('select').each(function() {
            $(this).SumoSelect({
                csvDispCount: 4,
                placeholder: abp01MainL10n.selectBoxPlaceholder,
                captionFormat: abp01MainL10n.selectBoxCaptionFormat,
                okCancelInMulti: false,
                selectAll: !!$(this).attr('multiple'),
                selectAlltext: abp01MainL10n.selectBoxSelectAllText
            });
        });
    }

    function destroySelectBoxes($container) {
        $container.find('select').each(function() {
            this.sumo.unload();
        });
    }

    function selectFormInfoTourType(type, clearForm) {
        currentTourType = type;
        $ctrlFormInfoContainer.empty();

        if (typeof typeSelectRenderers[type] === 'function') {
            $ctrlFormInfoContainer.html(typeSelectRenderers[type]());
            prepareSelectBoxes($ctrlFormInfoContainer);
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

    function isFormInfoSaved() {
        return context.isFormInfoSaved;
    }

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
        if (isFormInfoSaved()) {
            clearInfo();
        } else {
            switchToFormInfoSelection();
        }
    }

    function switchToFormInfoSelection() {
        currentTourType = null;

        toggleFormInfoReset(false);
        destroySelectBoxes($ctrlFormInfoContainer);
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
                    if (value.push) {
                        values[name] = values[name].concat(value);
                    } else {
                        values[name].push(value);
                    }
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
                addFormValue(inputName, $field.val(), !!$field.attr('multiple'));
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
                    context.isFormInfoSaved = true;
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
                    context.isFormInfoSaved = false;
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

    function initMapRetry() {
        //if an instance of this control already exist - clear the listener
        //as this means is either stale reference to a DOM element that no longer exists
        //or map retry initialization is re-done by accident
        if ($ctrlMapRetry) {
            $ctrlMapRetry.unbind('click');
        }
        $ctrlMapRetry = $('#abp01-map-retry');
        $ctrlMapRetryContainer = $('#abp01-map-retry-container');
        $ctrlMapRetry.click(function() {
            if (map) {
                map.loadMap();
            }
        });
    }

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

    /**
     * Clears the map form returning it to its original state:
     * 1. removes the map;
     * 2. shows the initial upload controls;
     * 3. uploader is re-initialized
     * 4. updates the context so that we know that we don't have a map and a track anymore
     * @return void
     * */
    function resetFormMap() {
        map.destroyMap();
        map = null;

        context.hasTrack = false;
        $ctrlFormMapContainer.empty().html(renderFormMapUnselected());
        $ctrlMapRetryContainer.hide();

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

    function fileEndsWithExtension(file, extension) {
        var testFileName = file.name.toLowerCase();
        var testExtension = '.' + extension.toLowerCase();
        
        if ($.isFunction(testFileName.endsWith)) {
            return testFileName.endsWith(testExtension);
        } else {
            var indexOfDot = testFileName.indexOf('.');
            return indexOfDot >= 0 
                ? testFileName.substr(indexOfDot) == extension 
                : false;
        }
    }

    function fileMatchesAllowedTypes(allowedMimeTypes, file) {
        var isAllowed = false;
        if (allowedMimeTypes.length > 0) {
            for (var iAllowed = 0; iAllowed < allowedMimeTypes.length; iAllowed ++) {
                var extensions = allowedMimeTypes[iAllowed]
                    .extensions
                    .split(',');

                for (var iExt = 0; iExt < extensions.length; iExt ++) {
                    if (fileEndsWithExtension(file, extensions[iExt])) {
                        isAllowed = true;
                        break;
                    }
                }
            }
        } else {
            isAllowed = true;
        }
        return isAllowed;
    }

    function configureUploaderFilters() {
        plupload.addFileFilter('mime_types', function(allowedMimeTypes, file, readyFn) {
            var isAllowed = fileMatchesAllowedTypes(allowedMimeTypes, file);

            if (!isAllowed) {
                this.trigger('Error', {
                    code : plupload.FILE_EXTENSION_ERROR,
                    message : abp01MainL10n.errPluploadFileType,
                    file : file
                });
            }

            readyFn(isAllowed);
        });
    }

    function createTrackUploader() {
        if (!!uploader) {
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
            runtimes: 'html5,html4',
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

    /**
     * Destroys the track uploader:
     * - remove event listeners - all of them;
     * - destroy the uploader instance itself;
     * - set uploader reference to null.
     * If there is no uploader instance, the method exists whitout taking any action
     * @return void
     * */
    function destroyTrackUploader() {
        if (!uploader) {
            return;
        }

        uploader.splice();
        uploader.unbindAll();
        uploader.destroy();
        uploader = null;
    }

    /**
     * Handles uploader error events
     * @param {Object} upl The uploader that triggered the error
     * @param {Object} error The object that contains the error details
     * @return void
     * */
    function handleUploaderError(upl, error) {
        uploader.disableBrowse(false);
        uploader.splice();
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
            uploader.splice();
            uploader.refresh();
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
        $ctrlFormMapContainer.empty()
            .html(renderFormMapUploaded());

        //due to the way the map is rendered, 
        //  this needs to be initialized each time 
        //  map rendering is attempted
        initMapRetry();
        map = $('#abp01-map')
            .mapTrack({
            	showScale: settings.mapShowScale,
            	tileLayer: settings.mapTileLayer,
                iconBaseUrl: context.imgBase,
                trackDataUrl: getAjaxLoadTrackUrl(),
                trackLineColour: settings.trackLineColour,
                trackLineWeight: settings.trackLineWeight,
                handlePreLoad: function() {
                    showProgress(false, abp01MainL10n.lblGeneratingPreview);
                    $ctrlMapRetryContainer.hide();
                },
                handleLoad: function(success) {
                    hideProgress();
                    if (!success) {
                        $ctrlMapRetryContainer.show();
                    } else {
                        $ctrlMapRetryContainer.hide();
                    }
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
            ajaxClearInfoAction: window['abp01_ajaxClearInfoAction'] || null,
            isFormInfoSaved: !!window['abp01_tourType']
        };
    }
    
    function getSettings() {
    	return {
    		mapShowScale: abp01Settings.mapShowScale == 'true',
            mapTileLayer: abp01Settings.mapTileLayer || {},
            trackLineColour: abp01Settings.trackLineColour || '#0033ff',
            trackLineWeight: abp01Settings.trackLineWeight || 3
    	};
    }

    function initEditorState() {
        context = getContext();
        settings = getSettings();
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
                top: 'calc(50% - ' + blockUICss.height/2 + 'px)',
                left: 'calc(50% - ' + blockUICss.width/2 + 'px)',
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
            width: 682,
            height: 545
        };

        configureUploaderFilters();
        initEditor();
        initFormInfo();
        initFormMap();
    });
})(jQuery);