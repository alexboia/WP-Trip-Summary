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

    L.Control.ReCenterMap = L.Control.extend({
        _map: null,
        _bounds: null,
        _buttonElement: null,

        options: {
            position: 'topright'
        },

        _createButton: function() {
            var buttonInfo = L.DomUtil.createMapButton({
                handleButtonClicked: this._handleButtonClicked,
                eventContext: this,
                iconCssClass: 'dashicons-fullscreen-exit-alt',
                additionalCssClass: 'abp01-map-recenter-btn'
            });

            this._buttonElement = buttonInfo.buttonElement;
            return buttonInfo.container;
        },

        _handleButtonClicked: function(event) {
            L.DomEvent.preventDefault(event);
            L.DomEvent.stopPropagation(event);

            if (this._map != null) {
                this._map.fitBounds(L.latLngBounds(
                    L.latLng(this._bounds.southWest.lat, this._bounds.southWest.lng),
                    L.latLng(this._bounds.northEast.lat, this._bounds.northEast.lng)
                ));
            }
        },

        initialize: function(bounds, options) {
            if (bounds == null) {
                throw new Error('Center is required');
            }

            this._bounds = bounds;
            L.Util.setOptions(this, options || {});
        },

        onAdd: function(map) {
            this._map = map;
            return this._createButton();
        },

        onRemove: function(map) {
            if (this._buttonElement != null) {
                L.DomEvent.off(this._buttonElement, 
                    'click', 
                    this._handleButtonClicked, 
                    this);
            }
            this._buttonElement = null;
        }
    });

    L.control.reCenterMap = function(bounds, options) {
        return new L.Control.ReCenterMap(bounds, options);
    };
})(window.abp01Leaflet);