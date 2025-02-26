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
/// <reference path="./abp01-modal.d.ts" />

(function($) {
	"use strict";

	$.fn.abp01Modal = function(spec:WpTripSummaryModalOptions): WpTripSummaryModal {
		var $me: JQuery = $(this);

		var myId: string = $me.get(0).id;
		var mySelector: string = `#${myId}`;

		var trigger: string = spec.trigger || null;
		var $wrap: JQuery = null;
		var bsModal: bootstrap.Modal = null;

		function _wrap(): void {
			var wrapId: string = `${myId}-__wrap`;

			$me.wrap(`<div id="${wrapId}" class="abp01-bootstrap" style="display: none;"></div>`);
			$wrap = $(`#${wrapId}`);

			$wrap.append('<div class="modal-backdrop"></div>');
		}

		function _watchTrigger(): void {
			if (!!trigger) {
				$(document).on('click', trigger, _show);
			}			
		}

		function _listen(): void {
			$me.on('show.bs.modal', function() {
				_showBackdrop();
			});
			$me.on('hide.bs.modal', function() {
				_hideBackdrop();
			});
		}

		function _showBackdrop(): void {
			$wrap
				.find('.modal-backdrop')
				.fadeIn('slow');
		}

		function _hideBackdrop(): void {
			$wrap
				.find('.modal-backdrop')
				.fadeOut('slow');
		}

		function _show(): void {
			_init();
			$wrap.show();
			bsModal.show();
		}

		function _init(): void {
			if (bsModal == null) {
				bsModal = new bootstrap.Modal(mySelector, {
					keyboard: false,
					backdrop: false,
					focus: true
				});
			}
		}

		function _hide(): void {
			if (bsModal != null) {
				bsModal.hide();
			}
		}

		_wrap();
		_listen();
		_watchTrigger();

		return {
			show: _show,
			hide: _hide
		};
	};
})(jQuery);