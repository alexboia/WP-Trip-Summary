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

(function(L) {
    "use strict";

    L.Control.AltitudeProfile = L.Control.extend({
        _profileData: null,
        _profileInfo: null,

        _chartContainer: null,
        _canvas: null,

        _map: null,
        _chart: null,
        _currentPointMarker: null,
        _buttonElement: null,

        _currentHoverTimer: null,

        options: {
            position: 'topleft',
            iconBaseUrl: null,
            //Color used to plot the chart line
            chartLineColor: '#003ac1',
            //Background color of the char tooltip
            tooltipBackgroundColor: 'rgba(81,81,81,0.80)',
            //How much time has to pass since 
            //  the last chart hover event before 
            //  the map is adjusted to move the highlighted 
            //  marker into vie
            hoverPanTimeout: 75,
            //Labels used when constructing the chart tooltip
            labels: {
                altitude: 'Altitude:',
                distance: 'Distance:'
            }
        },

        _removeCurrentHighlightedProfilePoint: function() {
            if (this._currentPointMarker != null) {
                this._map.removeControl(this._currentPointMarker);
                this._currentPointMarker = null;
            }
        },

        _cancelHoverTimer: function() {
            if (this._currentHoverTimer !== null) {
                window.clearTimeout(this._currentHoverTimer);
                this._currentHoverTimer = null;
            }
        },

        _highlightProfilePoint: function(point) {
            var me = this;
            var icon = L.icon({
                iconUrl: this.options.iconBaseUrl + '/blip.png',
                iconSize: [24, 28],
                iconAnchor: [12, 28]
            });

            var markerCoord = L.latLng(point.coord.lat, 
                point.coord.lng);

            if (this._currentPointMarker != null) {
                this._map.removeControl(this._currentPointMarker);
            }

            //See if the marker is within the current bounds of the map
            //  If not, start a timer to move the marker into view.
            //  We're using a timer because, if the user 
            //  shuflles rapidly between chart positions
            //  we're going to have a lot of useless pans
            if (!this._map.getBounds().contains(markerCoord)) {
                //First, cancel out the existing timer
                this._cancelHoverTimer();
    
                //Start a new one
                this._currentHoverTimer = window.setTimeout(function() {
                    me._currentHoverTimer = null;
                    me._map.panTo(markerCoord);
                }, this.options.hoverPanTimeout);
            }

            //Add the point anyway
            this._currentPointMarker = L.marker(markerCoord, {
                icon: icon
            }).addTo(this._map);
        },

        _getProfileDataSource: function() {
            var labels = [];
            var values = [];

            for (var i = 0; i < this._profileData.profile.length; i ++) {
                var profileDataItem = this._profileData.profile[i];
                labels.push(Math.round(profileDataItem.displayDistance) + ' ' + this._profileData.distanceUnit);
                values.push(profileDataItem.displayAlt);
            }

            return {
                labels: labels,
                values: values
            };
        },

        _tryGetActivePoint: function() {
            var point = null;
            if (!!this._chart.tooltip
                && this._chart.tooltip._active
                && this._chart.tooltip._active[0]) {
                var tooltip = this._chart.tooltip._active[0];
                if (tooltip.hasOwnProperty('_index') && tooltip._index !== null) {
                    point = this._profileData.profile[tooltip._index];
                }
            }
            return point;
        },

        _determineYAxisStepSize: function() {
            var deltaAlt = Math.abs(this._profileInfo.maxAltitude.value
                - this._profileInfo.minAltitude.value);

            //If beween the minimum altitude and maximum altitude
            //  the difference is greater than 1000 units
            //  use a step size of 100
            if (deltaAlt >= 1000) {
                return 100;
            } 
            //If between 500 and 1000, use a step size of 70
            if (deltaAlt > 500) {
                return 70;
            }
            //If between 100 and 500, use a step size of 50
            if (deltaAlt > 100) {
                return 50;
            }
            
            //If below or at most 100, use a step size of 10
            return 10;
        },

        _createProfileChart: function(context) {
            var me = this;
            var dataSource = this._getProfileDataSource();
            me._chart = new Chart(context, {
                type: 'line',

                data: {
                    labels: dataSource.labels,
                    datasets: [{
                        borderColor: me.options.chartLineColor,
                        data: dataSource.values
                    }]
                },

                options: {
                    legend: {
                        display: false
                    },

                    scales: {
                        yAxes: [{
                           ticks: {
                              stepSize: me._determineYAxisStepSize()
                           }
                        }]
                    },

                    onHover: function(event, data) {
                        var point = me._tryGetActivePoint();
                        if (point) {
                            me._highlightProfilePoint(point);
                        }
                    },

                    tooltips: {
                        position: 'nearest',
                        intersect: false,
                        displayColors: false,
                        backgroundColor: me.options.tooltipBackgroundColor,
                        callbacks: {
                            beforeLabel: function() {
                                return '';
                            },
                            label: function(item, data) {
                                return me.options.labels.altitude + ' ' + item.yLabel;
                            },
                            beforeTitle: function(item, data) {
                                return me.options.labels.distance + ' ';
                            },
                            afterLabel: function(item, data) {
                                return me._profileData.heightUnit;
                            }
                        }
                    },

                    hover: {
                        animationDuration: 0
                    }
                }
            });
        },

        _showProfileChart: function() {
            this._canvas = L.DomUtil.create('canvas', 'abp01-techbox-altitude-profile', 
                this._chartContainer);

            var context = this._canvas
                .getContext('2d');

            this._createProfileChart(context);

            L.DomEvent.on(this._chartContainer, 
                'mouseleave', 
                this._handleChartAreaLeave, 
                this);
        },
        
        _hideProfileChart: function() {
            if (this._chart != null) {
                //Remove the currently highlighted point from the map
                this._removeCurrentHighlightedProfilePoint();

                //Unbind event listeners
                L.DomEvent.off(this._chartContainer, 
                    'mouseleave', 
                    this._handleChartAreaLeave, 
                    this);

                //Destroy chart
                this._chart.destroy();
                this._chart = null;

                //Empty chart container
                L.DomUtil.empty(this._chartContainer);
            }
        },

        _handleChartAreaLeave: function() {
            //When the mouse pointer leaves the chart area, 
            //  remove the map marker that highlights 
            //  the current point on the profile
            //  and cancel the hover timer, so that 
            //  the map won't needlessly move
            this._cancelHoverTimer();
            this._removeCurrentHighlightedProfilePoint();
        },

        _createButton: function() {
            var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control abp01-leaflet-icon-button-container');            
            var buttonLink = L.DomUtil.create('a', 'abp01-leaflet-icon-button-link', container);

            L.DomEvent.on(buttonLink, 
                'click', 
                this._handleButtonClicked, 
                this);

            //Add icon
            L.DomUtil.create('span', 
                'dashicons dashicons-chart-area abp01-map-altitude-profile-btn', 
                buttonLink);

            //store reference to element
            buttonLink.href = 'javascript:void(0);';
            this._buttonElement = buttonLink;
            return container;
        },

        _handleButtonClicked: function(event) {   
            //Toggle chart on or off
            if (this._chart == null) {
                this._showProfileChart();
            } else {
                this._hideProfileChart();
            }

            L.DomEvent.preventDefault(event);
            L.DomEvent.stopPropagation(event);
        },

        initialize: function(chartContainer, profileData, profileInfo, options) {
            if (chartContainer == null) {
                throw new Error('Container is required');
            }

            if (profileData == null) {
                throw new Error('Profile data is required');
            }

            if (profileInfo == null) {
                throw new Error('Profile info is required');
            }

            this._chartContainer = L.DomUtil.get(chartContainer);
            this._profileData = profileData;
            this._profileInfo = profileInfo;

            L.Util.setOptions(this, options || {});
        },

        onAdd: function(map) {
            this._map = map;
            return this._createButton();
        },

        onRemove: function(map) {
            //Unbind toggler event listeners
            L.DomEvent.off(this._buttonElement, 
                'click', 
                this._handleButtonClicked, 
                this);

            //Destroy chart if shown
            if (this._chart != null) {
                this._hideProfileChart();
            }

            //Cleanup the rest of stuff
            this._map = null;
            this._buttonElement = null;
        }
    });

    L.control.altitudeProfile = function(container, data, labels, options) {
        return new L.Control.AltitudeProfile(container, data, labels, options);
    };
})(window.abp01Leaflet);