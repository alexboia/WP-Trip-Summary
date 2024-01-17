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

	function openAddLogEntryForm(onShow) {
		$.blockUI({
			message: $('#abp01-tripSummaryLog-formContainer'),
			css: {
				width: '640px',
				height: '480px',
				top: 'calc(50% - 240px)',
				left: 'calc(50% - 320px)',
				padding: '10px',
				borderRadius: '5px',
				backgroundColor: '#fff',
				boxShadow: '0 5px 15px rgba(0, 0, 0, 0.7)'
			},
			onBlock: function() {
				disableWindowScroll();
				if (!!onShow && $.isFunction(onShow)) {
					onShow();
				}
			},
			onUnblock: function() {
				enableWindowScroll();
			}
		});
	}

	function closeAddLogEntryForm() {
		$.unblockUI();
	}

	function initBlockUIDefaultStyles() {
		$.blockUI.defaults.css = {
			width: '100%',
			height: '100%'
		};
	}

	function initEvents() {
		$(document).on('click', 
			'#abp01-addTripSummary-logEntry', 
			openAddLogEntryForm);

		$(document).on('click', '.abp01-close-tripSummaryLog-form', 
			closeAddLogEntryForm);
		$(document).on('click', '#abp01-cancel-logEntry', 
			closeAddLogEntryForm);
	}

	function initForm() {
		$('#abp01-log-date').datepicker({
            dateFormat : 'yy-mm-dd'
        });

		$('#abp01-log-time').abp01NumericStepper({
			minValue: 1,
			maxValue: 1000,
			defaultValue: 1
		})
	}

	$(document).ready(function() {
		initBlockUIDefaultStyles();
		initForm();
		initEvents();
	});
})(jQuery);