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

    var ABP01_NUMERIC_STEPPER_MARKER = 'abp01-numeric-stepper';

    $.fn.abp01NumericStepper = function(opts) {
        var $me = this;
        var $stepper = null;
        var initialValue = null;

        //Init and normalize options
        opts = $.extend({
            minValue: 1,
            maxValue: Number.MAX_VALUE,
            increment: 1,
            defaultValue: 1
        }, opts);

        opts.minValue = Math.max(1, opts.minValue);
        opts.increment = Math.max(1, opts.increment);

        function isInitialized() {
            return $me.hasClass(ABP01_NUMERIC_STEPPER_MARKER);
        }

        function setInitialized() {
            $me.addClass(ABP01_NUMERIC_STEPPER_MARKER);
        }

        function setDestroyed() {
            $me.removeClass(ABP01_NUMERIC_STEPPER_MARKER);
        }

        function addMarkup() {
            $stepper = $([
                '<div class="abp01-numeric-stepper-ctrl-group">',
                    '<div class="abp01-numeric-stepper-ctrl ctrl-up">',
                        '<span class="dashicons dashicons-plus"></span>',
                    '</div>',
                    '<div class="abp01-numeric-stepper-ctrl ctrl-down">',
                        '<span class="dashicons dashicons-minus"></span>',
                    '</div>',
                '</div>',
                '<div class="abp01-numeric-stepper-clear"></div>'
            ].join(''));

            $stepper.insertAfter($me);
            initialValue = getCurrentValue(true);
            if (isNaN(initialValue)) {
                initialValue = null;
            }
        }

        function getCurrentValue(raw) {
            var cValue = parseInt($me.val());
            if (!raw && (!cValue || isNaN(cValue))) {
                cValue = initialValue !== null && cValue !== 0
                    ? initialValue 
                    : opts.minValue;
            }
            return cValue;
        }

        function trimValue(newValue) {
            newValue = Math.max(opts.minValue, newValue);
            newValue = Math.min(opts.maxValue, newValue);
            return newValue;
        }

        function adjustValue(amount, silent) {
            var newValue = getCurrentValue(false);
            
            newValue = trimValue(newValue + amount);
            $me.val(newValue);
            
            if (!silent) {
                $me.trigger('change');
            }
        }

        function normalizeValue(silent) {
            $me.val(trimValue(getCurrentValue(false)));
            if (!silent) {
                $me.trigger('change');
            }
        }

        function initListeners() {
            $stepper.on('click', '.ctrl-up', function($e) {
                adjustValue(opts.increment, false);
                $e.preventDefault();
                $e.stopPropagation();
            });

            $stepper.on('click', '.ctrl-down', function($e) {
                adjustValue(-opts.increment, false);
                $e.preventDefault();
                $e.stopPropagation();
            });

            $me.on('keyup', function($e) {
                normalizeValue(false);
                $e.preventDefault();
                $e.stopPropagation();
            });
        }

        function destroyStepper() {
            $markup.remove();
            setDestroyed();
        }

        if (!isInitialized()) {
            addMarkup();
            initListeners();
            setInitialized();
        }       

        return {
            destroy: destroyStepper
        };
    };
})(jQuery);