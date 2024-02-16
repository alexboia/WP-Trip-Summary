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

(function($){
	"use strict";

	var ACTION_CODE_YES = 'yes';
	var ACTION_CODE_NO = 'no';

	function disableWindowScroll() {
		window.abp01.disableWindowScroll();
	}

	function enableWindowScroll() {
		window.abp01.enableWindowScroll();
	}

	function confirmDialogModal(spec = {}) {
		var opts = spec || {};

		var confirmWnd = null;
		var chosenAction = null;
		var resultCallback = null;
		var userData = null;
		var isOpen = false;

		var confirmWndId = (opts.id || 'abp01-confirm-dialog-modal');
		var confirmWndSelector = '#' + confirmWndId;
		var confirWndContainerId = confirmWndId + '-container';
		var confirmWndContainerSelector = '#' + confirWndContainerId;

		function hasHtml() {
			return $(confirmWndSelector).length == 1;
		}

		function getHtml() {
			return [
				'<div id="' + confirWndContainerId + '" class="abp01-bootstrap" style="display: none;">',
				'<div id="' + confirmWndId + '" class="modal fade" tabindex="-1" aria-hidden="true" role="dialog">',
				'<div class="modal-dialog modal-dialog-centered">',
					'<div class="modal-content">',
						'<div class="modal-header">',
							'<h5 class="modal-title">' + abp01AdminCommonL10n.lblConfirmTitle + '</h5>',
							('<button data-abp01-modal-action="' + ACTION_CODE_NO + '" ' + 
								'type="button" ' + 
								'class="btn-close" ' + 
								'aria-label="Close"></button>'),
						'</div>',
						'<div class="modal-body">',
							'<p class="abp01-confirm-dialog-message"></p>',
						'</div>',
						'<div class="modal-footer">',
							('<button data-abp01-modal-action="' + ACTION_CODE_NO + '" ' + 
								'type="button" ' + 
								'class="btn btn-secondary">' + abp01AdminCommonL10n.btnNo + '</button>'),
							('<button data-abp01-modal-action="' + ACTION_CODE_YES + '" ' + 
								'type="button" ' + 
								'class="btn btn-primary">' + abp01AdminCommonL10n.btnYes + '</button>'),
						'</div>',
					'</div>',
				'</div>',
				'</div>',
				'<div class="modal-backdrop"></div>',
				'</div>'
			].join('');
		}

		function registerEventListeners() {
			$(confirmWndSelector).on('show.bs.modal', function() {
				showBackdrop();
			});

			$(confirmWndSelector).on('shown.bs.modal', function() {
				handleConfirmWindowShown();
			});

			$(confirmWndSelector).on('hide.bs.modal', function() {
				hideBackdrop();
			});

			$(confirmWndSelector).on('hidden.bs.modal', function() {
				handleConfirmWindowHidden();
			});

			$(confirmWndSelector).on('click', '[data-abp01-modal-action="' + ACTION_CODE_YES + '"]', 
				function() {
					dismissConfirmWindowWithActionCode(ACTION_CODE_YES);
				});

			$(confirmWndSelector).on('click', '[data-abp01-modal-action="' + ACTION_CODE_NO +  '"]', 
				function() {
					dismissConfirmWindowWithActionCode(ACTION_CODE_NO);
				});
		}

		function showBackdrop() {
			$(confirmWndContainerSelector)
				.find('.modal-backdrop')
				.fadeIn('slow');
		}

		function hideBackdrop() {
			$(confirmWndContainerSelector)
				.find('.modal-backdrop')
				.fadeOut('slow');
		}

		function handleConfirmWindowShown() {
			disableWindowScroll();
			isOpen = true;
		}

		function handleConfirmWindowHidden() {
			enableWindowScroll();

			if (resultCallback != null) {
				resultCallback(chosenAction === ACTION_CODE_YES, userData);
			}
			
			resultCallback = null;
			chosenAction = null;
			userData = null;
			isOpen = false;
		}

		function dismissConfirmWindowWithActionCode(actionCode) {
			chosenAction = actionCode;
			confirmWnd.hide();
		}

		function show(message, callback) {
			if (isOpen) {
				return;
			}

			chosenAction = null;
			userData = arguments.length == 3 
				? arguments[2] 
				: null;

			updateResultCallback(callback);
			updateMessage(message);

			showConfirmWnd();
		}

		function showConfirmWnd() {
			initConfirmWnd();
			$(confirmWndContainerSelector).show();
			confirmWnd.show();
		}

		function initConfirmWnd() {
			if (confirmWnd === null) {
				confirmWnd = new bootstrap.Modal(confirmWndSelector, {
					keyboard: false,
					backdrop: false,
					focus: true
				});
			}
		}

		function updateResultCallback(callback) {
			if (!callback || (typeof callback !== 'function')) {
				resultCallback = function() {
					warnNoResultCallbackRegistered();
				};
			} else {
				resultCallback = callback;
			}
		}

		function warnNoResultCallbackRegistered() {
			if (!!console && !!console.warn) {
				console.warn(
					'Possible programming error: no callback provided to modal confirm dialog. ' + 
					'Chosen action code: <' + chosenAction + '>.'
				);
			}
		}

		function updateMessage(message) {
			$(confirmWndSelector)
				.find('.abp01-confirm-dialog-message')
				.text(message || abp01AdminCommonL10n.lblConfirmQuestion);
		}

		function hide() {
			if (confirmWnd === null || !isOpen) {
				return;
			}

			if (chosenAction === null) {
				chosenAction = ACTION_CODE_NO;
			}

			confirmWnd.hide();
		}

		if (!hasHtml()) {
			$('body').append(getHtml());
			registerEventListeners();
		}

		return {
			show: show,
			hide: hide
		}
	};

	if (window.abp01 == undefined) {
		window.abp01 = {};
	}

	window.abp01 = $.extend(window.abp01, {
		confirmDialogModal: confirmDialogModal
	});

	$.abp01ConfirmDialogModal = confirmDialogModal;
})(jQuery);