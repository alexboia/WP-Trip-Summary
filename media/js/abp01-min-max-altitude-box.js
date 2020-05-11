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

(function(L){
    "use strict";

    L.Control.MinMaxAltitudeBox = L.Control.extend({
        _data: null,
        _labels: null,

        options: {
            position: 'topright'
        },

        _formatAltitudeDisplay(altInfo) {
            return altInfo.value + ' ' + altInfo.unit;
        },

        initialize: function(data, labels, options) {
            if (data == null) {
                throw new Error('Data is required!');
            }

            if (labels == null) {
                throw new Error('Labels are required');
            }
            
            this._data = data;
            this._labels = labels;

            L.Util.setOptions(this, options || {});
        },

        onAdd: function(map) {
            var container = L.DomUtil.create('div', 'abp01-min-max-altitude-box');
            var minAltLine = L.DomUtil.create('div', 'abp01-min-max-altitude-box-line', 
                container);
            var spacerLine = L.DomUtil.create('div', 'abp01-min-max-altitude-box-line-spacer', 
                container);
            var maxAltLine = L.DomUtil.create('div', 'abp01-min-max-altitude-box-line', 
                container);

            var minAltLabel = L.DomUtil.create('span', 'abp01-min-max-altitude-lbl', 
                minAltLine);
            var minAltValue = L.DomUtil.create('span', 'abp01-min-max-altitude-val', 
                minAltLine);

            var maxAltLabel = L.DomUtil.create('span', 'abp01-min-max-altitude-lbl', 
                maxAltLine);
            var maxAltValue = L.DomUtil.create('span', 'abp01-min-max-altitude-val', 
                maxAltLine);

            minAltLabel.innerHTML = this._labels.minAltitude || 'Minimum altitude:';
            maxAltLabel.innerHTML = this._labels.maxAltitude || 'Maximum altitude:';

            minAltValue.innerHTML = this._formatAltitudeDisplay(this._data.minAltitude);
            maxAltValue.innerHTML = this._formatAltitudeDisplay(this._data.maxAltitude);

            L.DomEvent.disableClickPropagation(container);
            return container;
        },

        onRemove: function(map) {
            return;
        }
    });

    L.control.minMaxAltitudeBox = function(data, options) {
        return new L.Control.MinMaxAltitudeBox(data, options);
    };
})(window.abp01Leaflet);