/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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
		return !!window['toastr'] && !!toastr.options;
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

		var toastrTarget =arguments.length == 3 
			? arguments[2] 
			: 'body';

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

	if (window.abp01 == undefined) {
		window.abp01 = {};
	}

	window.abp01 = $.extend(window.abp01, {
		scrollToTop: scrollToTop,
		disableWindowScroll: disableWindowScroll,
		enableWindowScroll: enableWindowScroll,
		initToastMessages: initToastMessages,
		toastMessage: toastMessage
	});
})(jQuery);