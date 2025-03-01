/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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
/// <reference types="bootstrap" />
/// <reference path="../abp01-common.d.ts" />
/// <reference path="./abp01-confirm-dialog-modal.d.ts" />

 (function($){
	"use strict";

	var ACTION_CODE_YES: string = 'yes';
	var ACTION_CODE_NO: string = 'no';

	function disableWindowScroll(): void {
		window.abp01.disableWindowScroll();
	}

	function enableWindowScroll(): void {
		window.abp01.enableWindowScroll();
	}

	function confirmDialogModal(spec?:WpTripSummaryConfirmModalOptions): WpTripSummaryConfirmModal {
		var opts:WpTripSummaryConfirmModalOptions = spec 
			|| { id: null };

		var confirmWnd: bootstrap.Modal = null;
		var chosenAction: string = null;
		var resultCallback: Function = null;
		var userData: any = null;
		var isOpen: boolean = false;

		var confirmWndId: string = (opts.id || 'abp01-confirm-dialog-modal');
		var confirmWndSelector: string = '#' + confirmWndId;
		var confirWndContainerId: string = confirmWndId + '-container';
		var confirmWndContainerSelector: string = '#' + confirWndContainerId;

		function hasHtml(): boolean {
			return $(confirmWndSelector).length == 1;
		}

		function getHtml(): string {
			return [
				'<div id="' + confirWndContainerId + '" class="abp01-bootstrap" style="display: none;">',
				'<div id="' + confirmWndId + '" class="modal fade" tabindex="-1" aria-hidden="true" role="dialog">',
				'<div class="modal-dialog modal-dialog-centered">',
					'<div class="modal-content">',
						'<div class="modal-header">',
							'<h5 class="modal-title">' + window.abp01AdminCommonL10n.lblConfirmTitle + '</h5>',
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
								'class="btn btn-secondary">' + window.abp01AdminCommonL10n.btnNo + '</button>'),
							('<button data-abp01-modal-action="' + ACTION_CODE_YES + '" ' + 
								'type="button" ' + 
								'class="btn btn-primary">' + window.abp01AdminCommonL10n.btnYes + '</button>'),
						'</div>',
					'</div>',
				'</div>',
				'</div>',
				'<div class="modal-backdrop"></div>',
				'</div>'
			].join('');
		}

		function registerEventListeners(): void {
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

		function showBackdrop(): void {
			$(confirmWndContainerSelector)
				.find('.modal-backdrop')
				.fadeIn('slow');
		}

		function hideBackdrop(): void {
			$(confirmWndContainerSelector)
				.find('.modal-backdrop')
				.fadeOut('slow');
		}

		function handleConfirmWindowShown(): void {
			disableWindowScroll();
			isOpen = true;
		}

		function handleConfirmWindowHidden(): void {
			enableWindowScroll();

			if (resultCallback != null) {
				resultCallback(chosenAction === ACTION_CODE_YES, userData);
			}
			
			resultCallback = null;
			chosenAction = null;
			userData = null;
			isOpen = false;
		}

		function dismissConfirmWindowWithActionCode(actionCode: string): void {
			chosenAction = actionCode;
			confirmWnd.hide();
		}

		function show(message: string, callback: Function): void {
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

		function showConfirmWnd(): void {
			initConfirmWnd();
			$(confirmWndContainerSelector).show();
			confirmWnd.show();
		}

		function initConfirmWnd(): void {
			if (confirmWnd === null) {
				confirmWnd = new bootstrap.Modal(confirmWndSelector, {
					keyboard: false,
					backdrop: false,
					focus: true
				});
			}
		}

		function updateResultCallback(callback: Function): void {
			if (!callback || (typeof callback !== 'function')) {
				resultCallback = function() {
					warnNoResultCallbackRegistered();
				};
			} else {
				resultCallback = callback;
			}
		}

		function warnNoResultCallbackRegistered(): void {
			if (!!console && !!console.warn) {
				console.warn(
					'Possible programming error: no callback provided to modal confirm dialog. ' + 
					'Chosen action code: <' + chosenAction + '>.'
				);
			}
		}

		function updateMessage(message: string): void {
			$(confirmWndSelector)
				.find('.abp01-confirm-dialog-message')
				.text(message || window.abp01AdminCommonL10n.lblConfirmQuestion);
		}

		function hide(): void {
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

	window.abp01 = $.extend(window.abp01, {
		confirmDialogModal: confirmDialogModal
	});

	$.abp01ConfirmDialogModal = confirmDialogModal;
})(jQuery);