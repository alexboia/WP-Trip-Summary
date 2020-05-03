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
    var UPLOAD_STORE_INITIALIZATION_FAILED = 7;
    var UPLOAD_INVALID_UPLOAD_PARAMS = 8;
    var UPLOAD_DESTINATION_FILE_NOT_FOUND = 9;
    var UPLOAD_DESTINATION_FILE_CORRUPT = 10;
    var UPLOAD_FAILED = 99;

    /**
     * Current state information
     * */

    var baseTitle = null;
    var progressBar = null;
    var currentRouteInfoType = null;
    var uploader = null;
    var settings = null;
    var context = null;
    var map = null;

    var editorWindowState = {
        isOpen: false,
        firstTime: true,
        formInfoRendered: false,
        routeTrackFormRendered: false
    };

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
    var $tplQuickActionsTooltip = null;

    /**
     * Cache rendered content
     * */

    var contentFormInfoBikeTour = null;
    var contentFormInfoHikingTour = null;
    var contentFormInfoTrainRide = null;
    var contentFormInfoUnselected = null;

    var contentFormMapUnselected = null;
    var contentFormMapUploaded = null;

    function hasRouteInfo() {
        return context.hasRouteInfo;
    }

    function setHasRouteInfo(flag) {
        context.hasRouteInfo = flag;
    }

    function hasRouteTrack() {
        return context.hasRouteTrack;
    }

    function setHasRouteTrack(flag) {
        context.hasRouteTrack = flag;
    }

    function maybeTrace(text) {
        if (settings._env.WP_DEBUG 
            && window.console 
            && $.isFunction(console.log)) {
            console.log(text);
        }
    }

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

    function renderRouteInfoTypeSelector() {
        if (contentFormInfoUnselected == null) {
            contentFormInfoUnselected = kite('#tpl-abp01-formInfo-unselected')();
        }
        return contentFormInfoUnselected;
    }
    
    function getQuickActionsTooltipTemplate() {
        if ($tplQuickActionsTooltip == null) {
            $tplQuickActionsTooltip = kite('#tpl-abp01-quick-actions-tooltip');
        }
        return $tplQuickActionsTooltip;
    }

    function renderQuickActionsTooltip() {
        return getQuickActionsTooltipTemplate()({
            context: context
        });
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

    function scrollToTop() {
        $('body,html').scrollTop(0);
    }

    function disableWindowScroll() {
        $('html').addClass('abp01-stop-scrolling');
    }

    function enableWindowScroll() {
        $('html').removeClass('abp01-stop-scrolling');
    }

    function toastMessage(success, message) {
        var toastrTarget = editorWindowState.isOpen 
            ? '#abp01-editor-content' 
            : 'body';

        maybeTrace('Showing toast message with target: <' + toastrTarget + '>');

        if (success) {
            toastr.success(message, null, {
                target: toastrTarget
            });
        } else {
            toastr.error(message, null, {
                target: toastrTarget
            });
        }
    }

    function showProgress(progress, text) {
        var $target = editorWindowState.isOpen 
            ? $ctrlEditor 
            : $('#wpwrap');

        var isDeterminate = progress !== false;
        if (progressBar != null) {
            progressBar.update({
                progress: progress,
                message: text
            });
        } else {
            progressBar = $('#tpl-abp01-progress-container').progressOverlay({
                $target: $target,
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

    function toggleFormInfoResetBtn(enable) {
        if (enable) {
            var resetHandler = arguments.length == 2 ? arguments[1] : null;
            $ctrlResetTechBox.text(abp01MainL10n.btnClearInfo).show();
            $ctrlResetTechBox.data('formInfoResetHandler', resetHandler);
        } else {
            $ctrlResetTechBox.hide();
            $ctrlResetTechBox.data('formInfoResetHandler', null);
        }
    }

    function toggleRouteTrackMapResetBtn(enable) {
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
                $field.val(null).trigger('change');
            }
        });
    }

    /**
     * Select boxes management functions
     * */

    function initSelect2Adapters() {
        //Coutersy of https://bojanv91.github.io/posts/2017/10/extending-select2-with-adapters-and-decorators
        $.fn.select2.amd.define('abp01/selection/adapter', [
            'select2/utils',
            'select2/selection/multiple',
            'select2/selection/placeholder',
            'select2/selection/eventRelay',
            'select2/selection/single',
        ],
        function(Utils, MultipleSelection, Placeholder, EventRelay, SingleSelection) {
            var adapter = Utils.Decorate(MultipleSelection, 
                Placeholder);

            adapter = Utils.Decorate(adapter, 
                EventRelay);

            adapter.prototype.render = function() {
                //Use selection-box from SingleSelection adapter
                //This implementation overrides the default implementation
                var $selection = SingleSelection.prototype.render.call(this);
                return $selection;
            };

            adapter.prototype.update = function(data) {
                //Copy and modify SingleSelection adapter
                this.clear();
                data = data || [];
          
                var $rendered = this.$selection.find('.select2-selection__rendered');
                var noItemsSelected = data.length === 0;
                var formatted = '';
          
                if (noItemsSelected) {
                    formatted = this.options.get('placeholder') || '';
                } else if (data.length === 1) {
                    formatted = data[0].text;
                } else {
                    var $allOptions = this.$element.find("option") 
                        || [];

                    var itemsData = {
                        selected: data || [],
                        selectedCount: data.length,
                        all: $allOptions,
                        allCount: $allOptions.length
                    };

                    //Pass selected and all items to display method
                    //  which calls templateSelection
                    formatted = this.display(itemsData, $rendered);
                }
          
                $rendered.empty().append(formatted);
                $rendered.prop('title', formatted);
            };
          
            return adapter;
        });
    }

    function renderSelect2Selection(data) {
        var format = abp01MainL10n
            .selectBoxCaptionFormat;
            
        return format
            .replace('{0}', data.selectedCount)
            .replace('{1}', data.allCount);
    }

    function prepareSelectBoxesSelect2($container) {
        $container.find('select').each(function() {
            var $me = $(this);
            var isMultiple = $me.attr('multiple');

            var basicOptions = {
                width: '638px',
                closeOnSelect: !isMultiple,
                scrollAfterSelect: false,
                minimumResultsForSearch: Infinity,
                placeholder: abp01MainL10n.selectBoxPlaceholder,
                allowClear: isMultiple
            };

            if (isMultiple) {
                $me.select2($.extend(basicOptions, {
                    selectionAdapter: $.fn.select2.amd.require('abp01/selection/adapter'),
                    templateSelection: renderSelect2Selection
                }));
            } else {
                $me.select2(basicOptions);
            }
        });
    }

    function destroySelectBoxesSelect2($container) {
        $container.find('select').each(function() {
            $(this).select2('destroy');
        });
    }

    function destroySelectBoxes($container) {
        destroySelectBoxesSelect2($container);
    }

    function selectRouteInfoType(type, clearForm) {
        currentRouteInfoType = type;
        $ctrlFormInfoContainer.empty();

        if (typeof typeSelectRenderers[type] === 'function') {
            $ctrlFormInfoContainer.html(typeSelectRenderers[type]());
            prepareSelectBoxesSelect2($ctrlFormInfoContainer);
            toggleFormInfoResetBtn(true, resetRouteInfoForm);
            updateTitle(typeTitles[type]);

            if (clearForm) {
                clearInputValues($ctrlFormInfoContainer);
            }

            $ctrlSave.show();
        } else {
            updateTitle(null);
            toggleFormInfoResetBtn(false);
            currentRouteInfoType = null;
            $ctrlSave.hide();
        }
    }

    /**
     * Tour info editor management functions
     * */

    function initRouteInfoForm() {
        typeSelectRenderers[TOUR_TYPE_BIKE] = renderFormInfoBikeTour;
        typeSelectRenderers[TOUR_TYPE_HIKING] = renderFormInfoHikingTour;
        typeSelectRenderers[TOUR_TYPE_TRAIN_RIDE] = renderFormInfoTrainRideTour;

        typeTitles[TOUR_TYPE_BIKE] = abp01MainL10n.lblTypeBiking;
        typeTitles[TOUR_TYPE_HIKING] = abp01MainL10n.lblTypeHiking;
        typeTitles[TOUR_TYPE_TRAIN_RIDE] = abp01MainL10n.lblTypeTrainRide;
    }

    function resetRouteInfoForm() {
        $ctrlSave.hide();
        clearInputValues($ctrlFormInfoContainer);
        if (hasRouteInfo()) {
            maybeTrace('Route info present and persisted. Need to clear that first...');
            clearRouteInfo();
        } else {
            maybeTrace('Route info not present or not persisted. Switching to info  type selection...');
            switchToRouteInfoTypeSelection();
        }
    }

    function switchToRouteInfoTypeSelection() {
        if (editorWindowState.formInfoRendered) {
            maybeTrace('Route info form rendered. Clearing form and rendering route info type selector...');

            currentRouteInfoType = null;

            toggleFormInfoResetBtn(false);
            destroySelectBoxes($ctrlFormInfoContainer);

            $ctrlFormInfoContainer.empty().html(renderRouteInfoTypeSelector());
            updateTitle(null);
        } else {
            maybeTrace('Route info form not rendered. Resetting initial route info typ...');
            context.initialRouteInfoType = null;
        }
    }

    function showRouteInfoForm($container) {
        if (!editorWindowState.formInfoRendered) {
            maybeTrace('Route info form not renedered. Rendering form...');

            var initialRouteInfoType = context.initialRouteInfoType;
            if (!initialRouteInfoType) {
                maybeTrace('No selected route info type found. Rendering route info type selector...');
                $container.html(renderRouteInfoTypeSelector());
            } else {
                maybeTrace('Route info type found: <' + initialRouteInfoType + '>. Rendering route info form...');
                selectRouteInfoType(initialRouteInfoType, false);
            }

            //Route info form now rendered; mark it as such.
            editorWindowState.formInfoRendered = true;
        }

        toggleRouteTrackMapResetBtn(false);
        if (!currentRouteInfoType) {
            toggleFormInfoResetBtn(false);
        } else {
            toggleFormInfoResetBtn(true, resetRouteInfoForm);
        }
    }

    function getRouteInfoFormValues($container) {
        var $input = $container.find('input,select,textarea');
        var values = {
            type: currentRouteInfoType
        };

        function isMultiCheckbox(inputName) {
            return inputName.indexOf('[]') == inputName.length - 2;
        }

        function getSimpleCheckboxName(inputName) {
            return inputName.substring(0, inputName.length - 2);
        }

        function getSelect2Value($field, isMultiple) {
            var values = [];
            var rawData = $field.select2('data');

            if (!!rawData && !!rawData.length) {
                for (var i = 0; i < rawData.length; i ++) {
                    var opt = rawData[i];
                    if (!!opt.selected) {
                        values.push(opt.id);
                    }
                }
            }

            return isMultiple ? values : values[0];
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
                var isMultiSelect = !!$field.attr('multiple');
                addFormValue(inputName, 
                    getSelect2Value($field, isMultiSelect), 
                    isMultiSelect);
            } else if (tagName == 'textarea') {
                addFormValue(inputName, 
                    $field.val(), 
                    false);
            }
        });

        return values;
    }

    function saveRouteInfo() {
        showProgress(false, abp01MainL10n.lblSavingDataWait);
        $.ajax(getAjaxEditInfoUrl(), {
            type: 'POST',
            dataType: 'json',
            data: getRouteInfoFormValues($ctrlFormInfoContainer)
        }).done(function(data, status, xhr) {
            hideProgress();
            if (data) {
                if (data.success) {
                    toastMessage(true, abp01MainL10n.lblDataSaveOk);
                    setHasRouteInfo(true);
                    refreshEnhancedEditor();
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

    function clearRouteInfo() {
        showProgress(false, abp01MainL10n.lblClearingInfoWait);
        $.ajax(getAjaxClearInfoUrl(), {
            type: 'POST',
            dataType: 'json',
            data: {}
        }).done(function(data, status, xhr) {
            hideProgress();
            if (data) {
                if (data.success) {
                    switchToRouteInfoTypeSelection();
                    toastMessage(true, abp01MainL10n.lblClearInfoOk);
                    setHasRouteInfo(false);
                    refreshEnhancedEditor();
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

    function initRouteTrackForm() {
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
        uploaderErrors.server[UPLOAD_STORE_FAILED] = abp01MainL10n.errServerUploadStoreFailed;
        uploaderErrors.server[UPLOAD_NOT_VALID] = abp01MainL10n.errServerCustomValidationFail;
        uploaderErrors.server[UPLOAD_STORE_INITIALIZATION_FAILED] = abp01MainL10n.errServerUploadStoreInitiationFailed;
        uploaderErrors.server[UPLOAD_INVALID_UPLOAD_PARAMS] = abp01MainL10n.errServerUploadInvalidUploadParams;
        uploaderErrors.server[UPLOAD_DESTINATION_FILE_NOT_FOUND] = abp01MainL10n.errServerUploadDestinationFileNotFound;
        uploaderErrors.server[UPLOAD_DESTINATION_FILE_CORRUPT] = abp01MainL10n.errServerUploadDestinationFileCorrupt;
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
    function resetRouteTrackForm() {
        if (editorWindowState.routeTrackFormRendered) {
            map.destroyMap();
            map = null;

            $ctrlFormMapContainer.empty().html(renderFormMapUnselected());
            $ctrlMapRetryContainer.hide();

            toggleRouteTrackMapResetBtn(false);
            createTrackUploader();
        }
    }

    function showRouteTrackForm($container) {
        if (!editorWindowState.routeTrackFormRendered) {
            editorWindowState.routeTrackFormRendered = true;
            if (!hasRouteTrack()) {
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

        toggleFormInfoResetBtn(false);
        if (!hasRouteTrack()) {
            toggleRouteTrackMapResetBtn(false);
        } else {
            toggleRouteTrackMapResetBtn(true, clearRouteTrack);
        }
    }

    function clearRouteTrack() {
        showProgress(false, abp01MainL10n.lblClearingTrackWait);
        $.ajax(getAjaxClearTrackUrl(), {
            type: 'POST',
            dataType: 'json',
            cache: false
        }).done(function(data, status, xhr) {
            hideProgress();
            if (data && data.success) {
                resetRouteTrackForm();
                toastMessage(true, abp01MainL10n.lblTrackClearOk);
                setHasRouteTrack(false);
                refreshEnhancedEditor();
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

    function isMsEdge() {
        var ua = window.navigator.userAgent
            .toString()
            .toLowerCase();
        return ua.indexOf("edge/") > -1;
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

        window.setTimeout(function() {
            //see https://github.com/moxiecode/plupload/issues/1343
            //The file browser simply doesn't open in Edge browsers
            //  Calling refresh didn't work either and, besides,
            //  we're initializing the uploader AFTER the elements 
            //  are CONSTRUCTED and SHOWN
            //However, simulating manual click DOES work, 
            //  so we're stuck with this for now
            bootstrapPluploadForEdgeIfNeeded();
        });
    }

    function bootstrapPluploadForEdgeIfNeeded() {
        if (isMsEdge()) {
            maybeTrace('Microsoft Edge browser detected. Binding manual file browser open event.');
            $('#abp01-track-selector').on('click', openFileBrowserManually);
        }
    }

    function openFileBrowserManually(e) {
        $('#abp01-form-map-trackSelection')
            .find('input[type=file]')
            .click();

        e.preventDefault();
        e.stopPropagation();

        return false;
    }

    function refreshUploadSelector() {
        if (uploader != null) {
            maybeTrace('Refreshing uploader...');
            uploader.refresh();
        }
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
        setHasRouteTrack(true);
        uploader.disableBrowse(false);

        destroyTrackUploader();
        toggleRouteTrackMapResetBtn(true, clearRouteTrack);
        toastMessage(true, abp01MainL10n.lblTrackUploaded);
        showMap();
        refreshEnhancedEditor();
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
            nonceDownload: $('#abp01-nonce-download').val(),
            postId: window['abp01_postId'] || 0,
            hasRouteTrack: window['abp01_hasTrack'] || 0,
            initialRouteInfoType: window['abp01_tourType'] || null,
            hasRouteInfo: !!window['abp01_tourType'],
            ajaxBaseUrl: window['abp01_ajaxUrl'] || null,
            ajaxLoadTrackAction: window['abp01_ajaxGetTrackAction'] || null,
            ajaxUploadTrackAction: window['abp01_ajaxUploadTrackAction'] || null,
            ajaxEditInfoAction: window['abp01_ajaxEditInfoAction'] || null,
            ajaxClearTrackAction: window['abp01_ajaxClearTrackAction'] || null,
            ajaxClearInfoAction: window['abp01_ajaxClearInfoAction'] || null,
            downloadTrackAction: window['abp01_downloadTrackAction'] || null
        };
    }
    
    function getSettings() {
    	return {
            _env: $.extend({
                WP_DEBUG: false
            }, abp01Settings._env || {}),

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
            timeOut: 30000,
            extendedTimeOut: 30000,
            progressBar: true
        });
    }

    function initEventHandlers() {
        $ctrlResetTechBox.click(function() {
            executeResetAction();
        });

        $ctrlSave.click(function() {
            saveRouteInfo();
        });

        $(document)
            .on('click', 'a[data-action=abp01-openTechBox]', {}, function() {
                var openWithTab = $(this).attr('data-select-tab');
                openEditor(openWithTab);
            })
            .on('click', 'a[data-action=abp01-closeTechBox]', {}, function() {
                $.unblockUI();
            })

            .on('click', '#abp01-quick-remove-info', {}, function() {
                scrollToTop();
                Tipped.hideAll();
                resetRouteInfoForm();
            })
            .on('click', '#abp01-quick-remove-track', {}, function() {
                scrollToTop();
                Tipped.hideAll();
                clearRouteTrack();
            })

            .on('click', 'a[data-action=abp01-typeSelect]', {}, function() {
                var routeInfoType = $(this).attr('data-type');
                selectRouteInfoType(routeInfoType, true);
            });
    }

    function toggleEnhancedEditorStatusItemIcon($item, hasAsset) {
        $item.find('.launcher-icon')
            .removeClass('dashicons-yes-alt')
            .removeClass('dashicons-dismiss')
            .addClass(hasAsset ? 'dashicons-yes-alt' : 'dashicons-dismiss');
    }   

    function toggleEnhancedEditorStatusItemText($item, hasAsset, hasAssetText, doesntHaveAssetText) {
        $item.find('.launch-editor-trigger').attr('data-status-text', hasAsset 
            ? hasAssetText 
            : doesntHaveAssetText);
    }   

    function refreshEnhancedEditor() {
        refreshEnhancedEditorStatusItems();
        refreshEnhancedEditorTooltips();
        refreshEnhancedEditorQuickActions();
    }

    function refreshEnhancedEditorQuickActions() {
        var $quickActionsTrigger = $('#abp01-quick-actions-trigger');
        var $quickActionsTooltip = $('#abp01-quick-actions-tooltip');

        if (context.hasRouteInfo || context.hasRouteTrack) {
            maybeTrace('Has route info or route track. Showing actions trigger...');
            $quickActionsTrigger.show();
        } else {
            $quickActionsTrigger.hide();
            maybeTrace('Does not have route info nor route track. Hiding actions trigger...');
        }

        $quickActionsTooltip
            .html(renderQuickActionsTooltip())
            .hide();
    }

    function refreshEnhancedEditorStatusItems() {
        var $infoItem = $('#abp01-editor-launcher-status-trip-summary-info');
        var $trackItem = $('#abp01-editor-launcher-status-trip-summary-track');
        
        toggleEnhancedEditorStatusItemIcon($infoItem, context.hasRouteInfo);
        toggleEnhancedEditorStatusItemText($infoItem, context.hasRouteInfo, 
            abp01MainL10n.lblStatusTextTripSummaryInfoPresent, 
            abp01MainL10n.lblStatusTextTripSummaryInfoNotPresent);

        toggleEnhancedEditorStatusItemIcon($trackItem, context.hasRouteTrack);
        toggleEnhancedEditorStatusItemText($trackItem, context.hasRouteTrack, 
            abp01MainL10n.lblStatusTextTripSummaryTrackPresent, 
            abp01MainL10n.lblStatusTextTripSummaryTrackNotPresent);
    }

    function addSimpleTooltip(elementId) {
        var selector = '#' + elementId;
        var position = arguments.length == 2 && !!arguments[1]
            ? arguments[1] 
            : 'left';

        Tipped.create(selector, $(selector).attr('data-status-text'), {
            position: position
        });
    }

    function addControllerTooltip(elementId) {
        var selector = '#' + elementId;
        var $element = $(selector);

        if ($element.size() > 0) {
            var controllerSelector = $element.attr('data-controller-selector');
            if (!!controllerSelector) {
                Tipped.create(selector, {
                    inline: controllerSelector,
                    skin: 'light',
                    hideOn: false,
                    hideAfter: 500,
                    showDelay: 100,
                    showOn: 'click'
                });
            }
        }
    }

    function initEnhancedEditorTooltips() {
        $.each({
            'abp01-editor-launcher-status-trip-summary-info a': 'simple', 
            'abp01-editor-launcher-status-trip-summary-track a': 'simple', 
            'abp01-edit-trigger': 'simple',
            'abp01-quick-actions-trigger': 'controller'
            }, function(selector, mode){
                if (mode == 'simple') {
                    addSimpleTooltip(selector);
                } else {
                    addControllerTooltip(selector);
                }
            });
    }

    function cleanupEhancedEditorTooltips() {
        $.each([
            'abp01-editor-launcher-status-trip-summary-info a', 
            'abp01-editor-launcher-status-trip-summary-track a', 
            'abp01-edit-trigger', 
            'abp01-quick-actions-trigger'
            ], function(idx, selector){
                Tipped.remove('#' + selector)
            });
    }

    function refreshEnhancedEditorTooltips() {
        cleanupEhancedEditorTooltips();
        initEnhancedEditorTooltips();
    }

    function initEnhancedEditorLauncher() {
        initEnhancedEditorTooltips();
    }

    function initEditor() {
        initEditorState();
        initToastMessages();
        initEditorControls();
        initEventHandlers();
        initEnhancedEditorLauncher();
    }

    function openEditor() {
        var blockUICss = $.blockUI.defaults.css;

        var openWithTab = arguments.length == 1 && typeof arguments[0] == 'string'
            ? arguments[0]
            : null;

        $.blockUI({
            message: $ctrlEditor,
            css: {
                top: 'calc(50% - ' + blockUICss.height/2 + 'px)',
                left: 'calc(50% - ' + blockUICss.width/2 + 'px)',
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.7)'
            },
            onBlock: function() {
                //Disable window scrolling
                disableWindowScroll();

                //Editor window is now open
                editorWindowState.isOpen = true;

                //First time we open the editor, set everything up
                if (editorWindowState.firstTime) {
                    initTabs();
                    editorWindowState.firstTime = false;
                } else {
                    //Otherwise, if map is not null AND is visible,
                    //  force a refresh
                    if (map != null && $ctrlFormMapContainer.is(':visible')) {
                        map.forceRedraw();
                    }
                }

                //If requested, select a specific tab
                if (!!openWithTab) {
                    $ctrlEditorTabs.easytabs('select', openWithTab);
                }
            },
            onUnblock: function() {
                //Re-enable window scrolling
                enableWindowScroll();
                //Editor window is now closed
                editorWindowState.isOpen = false;
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

    function getDownloadTrackUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.downloadTrackAction)
            .addSearch('abp01_nonce_download', context.nonceDownload)
            .addSearch('abp01_postId', context.postId)
            .toString();
    }

    /**
     * Editor tabs management functions
     * */

    function initTabs() {
        tabHandlers['abp01-form-info'] = showRouteInfoForm;
        tabHandlers['abp01-form-map'] = showRouteTrackForm;

        $ctrlEditorTabs = $('#abp01-editor-content').easytabs({
            animate: false,
            tabActiveClass: 'abp01-tab-active',
            panelActiveClass: 'abp01-tabContentActive',
            defaultTab: '#abp01-tab-info',
            updateHash: false
        });

        $ctrlEditorTabs.bind('easytabs:after', function(e, $clicked, $target, settings) {
            processTabSelected($target);
        });

        processTabSelected($('#abp01-form-info'));
    }

    function processTabSelected($target) {
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

        initSelect2Adapters();
        configureUploaderFilters();
        initEditor();
        initRouteInfoForm();
        initRouteTrackForm();
    });
})(jQuery);