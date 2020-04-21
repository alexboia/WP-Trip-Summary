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

    //A small progress bar controller which 
    //  displays a progress bar + progress message over a target element
    //  using jQuery blockUI and NProgress

    $.fn.progressOverlay = function(spec) {
        var fsm = null;
        var $content = null;
        var $progressParent = null;
        var $progressLabel = null;

        if (!this.size()) {
            return;
        }

        $content = kite('#' + this.attr('id'));
        if ($content && $.isFunction($content)) {
            $content = $($content());
        }

        spec = $.extend({
            $target: $('body'),
            determinate: false,
            progress: false,
            message: null,
            onRemove: null
        }, spec || {});

        function getTarget() {
            return spec.$target;
        }

        function getOnRemoveHandler() {
            return spec.onRemove;
        }

        function setOnRemoveHandler(handler) {
            spec.onRemove = handler;
        }

        function isDeterminate() {
            return spec.determinate == true;
        }

        function setDeterminate() {
            spec.determinate = true;
        }

        function setIndeterminate() {
            spec.determinate = false;
        }

        function getInitialMessage() {
            return spec.message;
        }

        function getInitialProgress() {
            return isDeterminate() ? parseInt(spec.progress) : false;
        }

        function getProgressParentId() {
            return getProgressParent().attr('id');
        }

        function getProgressParent() {
            if ($progressParent == null) {
                $progressParent = $content.find('div[data-role=progressParent]');
            }
            return $progressParent;
        }

        function getProgressLabel() {
            if ($progressLabel == null) {
                $progressLabel = $content.find('div[data-role=progressLabel]');
            }
            return $progressLabel;
        }

        function getStyle() {
            return $.extend({
                top: 280,
                width: 400,
                height: 20
            }, spec.style || {});
        }

        function getCenterY() {
            return spec.centerY || false;
        }

        fsm = new machina.Fsm({
            states: {
                closed: {
                    _onEnter: function() {
                        if (this.priorState == 'determinate' || this.priorState == 'indeterminate') {
                            this._closeProgressDialog();
                        }
                    }
                },

                determinate: {
                    _onEnter: function() {
                        setDeterminate();
                        if (this.priorState == 'closed') {
                            this._openProgressDialog();
                        } else if (this.priorState == 'indeterminate') {
                            this._reconfigureAndDisplayProgressBar();
                        }
                    },

                    updateProgress: function(e) {
                        this._verifyStateBeforeProgressUpdate(e);
                        NProgress.set(e.progress);
                        getProgressLabel().text(e.message);
                    }
                },

                indeterminate: {
                    _onEnter: function() {
                        setIndeterminate();
                        if (this.priorState == 'closed') {
                            this._openProgressDialog();
                        } else if (this.priorState == 'determinate') {
                            this._reconfigureAndDisplayProgressBar();
                        }
                    },

                    updateProgress: function(e) {
                        this._verifyStateBeforeProgressUpdate(e);
                        getProgressLabel().text(e.message);
                    }
                }
            },

            _configureProgressBar: function() {
                NProgress.configure({
                    showSpinner: false,
                    parent: '#' + getProgressParentId(),
                    trickle: !isDeterminate(),
                    progress: 0.01
                });
            },

            _openProgressDialog: function() {
                var me = this;
                window.setTimeout(function() {
                    getTarget().block({
                        centerY: getCenterY(),
                        message: $content,
                        css: getStyle(),
                        baseZ: 9999999,
                        onBlock: function() {
                            me._displayProgressBar();
                        }
                    });
                }, 0);
            },

            _closeProgressDialog: function() {
                var me = this;
                NProgress.done(function() {
                    me._cleanupProgressDialog();
                    var onRemoveHandler = getOnRemoveHandler();
                    if (onRemoveHandler != null && $.isFunction(onRemoveHandler)) {
                        onRemoveHandler();
                    }
                });
            },

            _cleanupProgressDialog: function() {
                //If NProgress has indeed completed, 
                //  go ahead and clean-up
                //Otherwise, queue the cleanup delegate again
                getProgressLabel().text('');
                getTarget().unblock();
            },

            _displayProgressBar: function() {
                var initialMessage = getInitialMessage();
                var initialProgress = getInitialProgress();

                if (initialMessage) {
                    getProgressLabel().text(initialMessage);
                }

                this._configureProgressBar();
                if (!isDeterminate()) {
                    NProgress.start();
                } else {
                    if (initialProgress) {
                        NProgress.set(initialProgress);
                    } else {
                        NProgress.set(0);
                    }
                }
            },

            _verifyStateBeforeProgressUpdate: function(config) {
                if (isDeterminate() && config.progress === false) {
                    $.extend(spec, config);
                    fsm.transition('indeterminate');
                } else if (!isDeterminate() && config.progress !== false) {
                    $.extend(spec, config);
                    fsm.transition('determinate');
                }
            },

            _reconfigureAndDisplayProgressBar: function() {
                NProgress.set(1.0);
                this._displayProgressBar();
            },

            initialState: 'closed'
        });

        if (isDeterminate()) {
            fsm.transition('determinate');
        } else {
            fsm.transition('indeterminate');
        }

        return {
            update: function(config) {
                config = $.extend({
                    progress: false,
                    message: ''
                }, config || {});

                fsm.handle('updateProgress', config);
            },

            destroy: function() {
                if (arguments.length == 1 && $.isFunction(arguments[0])) {
                    var onRemove = arguments[0];
                    var oldOnRemove = getOnRemoveHandler();
                    setOnRemoveHandler(function() {
                        onRemove();
                        setOnRemoveHandler(oldOnRemove);
                    });
                }

                fsm.transition('closed');
                $progressParent = null;
                $progressLabel = null;
            }
        };
    };
})(jQuery);