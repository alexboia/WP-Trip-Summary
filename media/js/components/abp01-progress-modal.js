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

	function disableWindowScroll() {
		window.abp01.disableWindowScroll();
	}

	function enableWindowScroll() {
		window.abp01.enableWindowScroll();
	}

	$.fn.abp01ProgressModal = function(spec) {
		var $me = $(this);
		var opts = spec || {};

		var shouldDisableScroll = opts.hasOwnProperty('shouldDisableScroll')
			? !!opts.shouldDisableScroll
			: true;

		function getStyle() {
			var style = $.extend({
				width: 400,
				height: 20,
				border: '0px none',
				background: 'transparent'
			}, opts.style || {});

			return $.extend(style, {
				left: ($me.width() - style.width) / 2,
				top: ($me.height() - style.height) / 2
			});
		}

		function hasHtml() {
			return $('#abp01-progress-container').length == 1;
		}

		function getHtml() {
			return [
				('<div id="abp01-progress-container" ' + 
						'class="abp01-bootstrap abp01-bs-progress-container" ' + 
						'style="display: none;">'),
					'<div id="abp01-progress-label" class="abp01-bs-progress-label"></div>',
					'<div id="abp01-progress-bar-container" class="abp01-bs-progress-bar-container">',
						('<div id="abp01-progress-bar-outer" class="progress" ' + 
								'role="progressbar" ' + 
								'aria-label="WP Trip Summary Progress Bar" ' + 
								'aria-valuenow="100" ' + 
								'aria-valuemin="0" ' + 
								'aria-valuemax="100">'),
							('<div id="abp01-progress-bar-indicator" ' + 
								'class="progress-bar progress-bar-striped progress-bar-animated" ' + 
								'style="width: 100%">'),
							'</div>',
						'</div>',
					'</div>',
				'</div>'
			].join('');
		}

		function show() {
			var text = arguments.length == 1 
				? arguments[0] || '' 
				: '';

			$me.block({
				message: $('#abp01-progress-container'),
				css: getStyle(),
				baseZ: 9999999,
				onBlock: function() {
					$('#abp01-progress-label').html(text);

					if (shouldDisableScroll) {
						disableWindowScroll();
					}

					if (!!opts.onBlock && $.isFunction(opts.onBlock)) {
						opts.onBlock();
					}
				},
				onUnblock: function() {
					$('#abp01-progress-label').html('');

					if (shouldDisableScroll) {
						enableWindowScroll();
					}

					if (!!opts.onUnblock && $.isFunction(opts.onUnblock)) {
						opts.onUnblock();
					}
				}
			});
		}

		function hide() {
			$me.unblock();
		}

		if (!hasHtml()) {
			$('body').append(getHtml());
		}

		return {
			show: show,
			hide: hide
		}
	};
 })(jQuery);