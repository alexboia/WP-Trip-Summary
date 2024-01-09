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

	var progressBar = null;
	var screenshotGallery = null;

	var $ctlLanguageSelector = null;
	var $ctlHelpContentsContainer = null;
	var $ctlHelpLoadResult = null;

	var context = null;

	function getContext() {
		return {
			ajaxBaseUrl: window['abp01_ajaxUrl'] || null,
			getHelpNonce: window['abp01_getHelpNonce'] || null,
			ajaxGetHelpAction: window['abp01_getHelpAction'] || null
		};
	}

	function getHelpForLocaleUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxGetHelpAction)
			.addSearch('abp01_help_nonce', context.getHelpNonce)
			.toString();
	}

	function toggleBusy(show) {
		if (show) {
			if (progressBar == null) {
				progressBar = $('#tpl-abp01-progress-container').progressOverlay({
					$target: $('#wpwrap'),
					message: abp01HelpL10n.msgWorking
				});
			}
		} else {
			if (progressBar != null) {
				progressBar.destroy();
				progressBar = null;
			}
		}
	}

	function refreshHelpContentsForLocale(locale) {
		toggleBusy(true);
		$ctlHelpLoadResult.abp01OperationMessage('hide');
		$.ajax(getHelpForLocaleUrl(), {
			cache: false,
			dataType: 'json',
			type: 'GET',
			data: {
				help_locale: locale
			}
		}).done(function(data) {
			toggleBusy(false);
			if (data && data.success && !!data.htmlHelpContents) {
				refreshHelp(data.htmlHelpContents);
			} else {
				$ctlHelpLoadResult.abp01OperationMessage('error', 
					data.message || abp01HelpL10n.errLoadHelpContentsGeneric);
			}
		}).fail(function() {
			toggleBusy(false);
			$ctlHelpLoadResult.abp01OperationMessage('error', 
				abp01HelpL10n.errLoadHelpContentsFailNetwork);
		});
	}

	function refreshHelp(htmlHelpContents) {
		destroyScreenshotGallery();
		$ctlHelpContentsContainer.html(htmlHelpContents);
		initScreenshotGallery();
	}

	function initContext() {
		context = getContext();
	}

	function initControls() {
		$ctlLanguageSelector = $('#abp01-help-contents-lang');
		$ctlHelpContentsContainer = $('#abp01-help-contents-body');
		$ctlHelpLoadResult = $('#abp01-help-load-result');
	}

	function initListeners() {
		$ctlLanguageSelector.change(function() {
			var locale = $ctlLanguageSelector.val();
			refreshHelpContentsForLocale(locale);
		});
	}

	function initBlockUIDefaultStyles() {
		$.blockUI.defaults.css = {
			width: '100%',
			height: '100%'
		};
	}

	function initScreenshotGallery() {
		screenshotGallery = $('.abp01-help-image-slideshow').abp01HelpImageGallery();
	}

	function destroyScreenshotGallery() {
		screenshotGallery.destroy();
		screenshotGallery = null;
	}

	$(document).ready(function() {
		initContext();
		initControls();
		initListeners();
		initBlockUIDefaultStyles();
		initScreenshotGallery();
	});
})(jQuery);