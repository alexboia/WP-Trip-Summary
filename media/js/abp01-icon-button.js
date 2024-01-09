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

(function(L) {
    "use strict";

    var VOID_ICON_CSS_CLASS = 'abp01-none';
    var VOID_TARGET_URL = 'javascript:void(0)';
    
    L.Control.IconButton = L.Control.extend({
        _targetUrl: null,
        _iconCssClass: null,
        _buttonLinkElement: null,

        options: {
            position: 'topleft',
            openInNewWindow: false
        },

        initialize: function(iconCssClass, targetUrl, options) {
            this._iconCssClass = iconCssClass || VOID_ICON_CSS_CLASS;
            this._targetUrl = targetUrl || VOID_TARGET_URL;

            L.Util.setOptions(this, options || {});
        },

        _hasOnClickListener() {
            return !!this.options.onClick && typeof this.options.onClick == 'function';
        },

        _handleButtonClicked(event) {
            var onClick = this.options.onClick;
            onClick(event);
            L.DomEvent.preventDefault(event);
            L.DomEvent.stopPropagation(event);
        },

        onAdd: function(map) {
            var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control abp01-leaflet-icon-button-container');            
            var buttonLink = L.DomUtil.create('a', 'abp01-leaflet-icon-button-link', container);

            buttonLink.href = this._targetUrl;
            if (this._targetUrl != VOID_TARGET_URL && !!this.options.openInNewWindow) {
                buttonLink.target = '_blank';
            }

            //add listener, if specified
            if (this._hasOnClickListener()) {
                L.DomEvent.on(buttonLink, 
                    'click', 
                    this._handleButtonClicked, 
                    this);
            }

            //add icon
            L.DomUtil.create('span', this._iconCssClass, buttonLink);

            //store reference to element
            this._buttonLinkElement = buttonLink;
            return container;
        },

        onRemove: function(map) {
            if (this._hasOnClickListener() 
                && this._buttonLinkElement != null) {
                L.DomEvent.off(this._buttonLinkElement, 
                    'click', 
                    this._handleButtonClicked, 
                    this);
            }
            
            this._buttonLinkElement = null;
        }
    });

    L.control.iconButton = function (iconCssClass, targetUrl, options) {
	    return new L.Control.IconButton(iconCssClass, targetUrl, options);
    };
})(window.abp01Leaflet);