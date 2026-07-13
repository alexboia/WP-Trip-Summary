/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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
/// <reference types="jquery" />
/// <reference types="toastr" />
/// <reference path="./abp01-common.d.ts" />
/// <reference path="./components/abp01-progress-modal.d.ts" />
(function ($) {
    "use strict";
    function scrollToTop() {
        $('body,html').scrollTop(0);
    }
    function disableWindowScroll() {
        $('html').addClass('abp01-stop-scrolling');
    }
    function enableWindowScroll() {
        $('html').removeClass('abp01-stop-scrolling');
    }
    function _hasToastr() {
        return !!window['toastr'] && !!window.toastr.options;
    }
    function initToastMessages(target) {
        if (!_hasToastr()) {
            return;
        }
        $.extend(toastr.options, {
            iconClasses: {
                error: 'abp01-toast-error',
                info: 'abp01-toast-info',
                success: 'abp01-toast-success',
                warning: 'abp01-toast-warning'
            },
            target: target,
            positionClass: 'toast-bottom-right',
            timeOut: 30000,
            extendedTimeOut: 30000,
            progressBar: true
        });
    }
    function toastMessage(success, message) {
        if (!_hasToastr()) {
            return;
        }
        var toastrTarget = arguments.length == 3
            ? arguments[2]
            : 'body';
        if (success) {
            toastr.success(message, undefined, {
                target: toastrTarget
            });
        }
        else {
            toastr.error(message, undefined, {
                target: toastrTarget
            });
        }
    }
    function initTooltipsOnPage(container) {
        const $els = $('[data-bs-toggle="tooltip"]');
        $els.each(function () {
            const $me = $(this);
            const el = $me.get(0);
            if (!!el) {
                new bootstrap.Tooltip(el, {
                    container: container,
                    delay: {
                        show: 0,
                        hide: 2500
                    }
                });
            }
        });
    }
    function createBusyToggler(selector, defaultMessage) {
        let progressBar = null;
        return function (show, message = null) {
            if (show) {
                if (progressBar == null) {
                    progressBar = $(selector).abp01ProgressModal({});
                }
                progressBar.show((message || defaultMessage) || 'Please wait...');
            }
            else {
                if (progressBar != null) {
                    progressBar.hide();
                }
            }
        };
    }
    function isNullOrWhiteSpace(value) {
        if (!value) {
            return true;
        }
        if (typeof value !== 'string') {
            value = value.toString();
        }
        if (typeof value === 'string') {
            return value.trim().length === 0;
        }
        return false;
    }
    function kiteTemplate(templateId, data) {
        try {
            if (window.kite) {
                return window.kite(templateId, data);
            }
            else {
                throw new Error('KiteJS is not available.');
            }
        }
        catch (error) {
            console.error('Failed to compile lookup listing template:', error);
            return null;
        }
    }
    $.fn.singleVal = function () {
        var $me = $(this);
        return ($me.val() || '').toString();
    };
    $.fn.singleValNumeric = function (defaultValue = 0) {
        var $me = $(this);
        var strValue = $me.singleVal();
        if (!strValue || strValue.length <= 0) {
            return defaultValue;
        }
        var numericValue = parseInt(strValue);
        if (isNaN(numericValue)) {
            return defaultValue;
        }
        return numericValue;
    };
    $.fn.optionTextByValue = function (value) {
        var $me = $(this);
        var $option = $me.find('option[value="' + value + '"]');
        if ($option.length > 0) {
            return $option.text();
        }
        else {
            return null;
        }
    };
    if (window.abp01 == undefined) {
        window.abp01 = {
            scrollToTop: scrollToTop,
            disableWindowScroll: disableWindowScroll,
            enableWindowScroll: enableWindowScroll,
            initToastMessages: initToastMessages,
            toastMessage: toastMessage,
            initTooltipsOnPage: initTooltipsOnPage,
            createBusyToggler: createBusyToggler,
            isNullOrWhiteSpace: isNullOrWhiteSpace,
            kiteTemplate: kiteTemplate
        };
        window.wpTripSummary = window.abp01;
    }
    $.abp01 = window.abp01;
    $.wpTripSummary = window.wpTripSummary;
})(jQuery);
