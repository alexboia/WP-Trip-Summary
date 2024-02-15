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

	$.fn.abp01AlertInline = function(spec) {
		var $container = $(this);
		var opts = spec || {};

		var isDismissible = !!opts.dismissible;

		function hasHtml() {
			return $container.find('.alert ').length > 0;
		}

		function getHtml() {
			var classes = ['alert'];

			if (isDismissible) {
				classes.push('alert-dismissible');
			}

			return [
				'<div class="' + classes.join(' ') + '" role="alert" style="display: none;">',
					(isDismissible 
						? '<button type="button" ' + 
							'class="btn-close" ' + 
							'data-bs-dismiss="alert" ' + 
							'aria-label="Close"></button>' 
						: ''),
				'</div>'
			].join('');
		}

		function show(message, type, animate = true) {
			var $alert = $container.find('.alert');
			$alert.removeClass('alert-primary')
				.removeClass('alert-secondary')
				.removeClass('alert-success')
				.removeClass('alert-danger')
				.removeClass('alert-warning')
				.removeClass('alert-info')
				.removeClass('alert-dark');

			$alert.addClass('alert-' + type);	
			$alert.html(message);

			if (animate) {
				$alert.fadeIn('fast');
			} else {
				$alert.show();
			}			
		}

		function hide(animate = true) {
			var $alert = $container.find('.alert');
			if (animate) {
				$alert.fadeOut('fast', function() {
					$alert.html('');
				});			
			} else {
				$alert.hide().html('');
			}			
		}

		if (!hasHtml()) {
			$container.append(getHtml());
		}

		return {
			show: show,
			hide: hide,

			primary: function(message, animate = true) {
				show(message, 'primary', animate);
			},
			secondary: function(message, animate = true) {
				show(message, 'secondary', animate);
			},
			success: function(message, animate = true) {
				show(message, 'success', animate);
			},
			danger: function(message, animate = true) {
				show(message, 'danger', animate);
			},
			warning: function(message, animate = true) {
				show(message, 'warning', animate);
			},
			info: function(message, animate = true) {
				show(message, 'info', animate);
			},
			light: function(message, animate = true) {
				show(message, 'light', animate);
			},
			dark: function(message, animate = true) {
				show(message, 'dark', animate);
			}
		}
	}
})(jQuery);