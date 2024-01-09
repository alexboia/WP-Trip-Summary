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

(function($, L) {
    "use strict";

    $.fn.mapTrack = function(opts) {
        //basic map state stuff
        var map = null;
        var mapLoaded = false;
        var mapDestroyed = false;

        //jquery variables stuff
        var $me = this;

        //data store
        var trackProfile = null;
        var trackInfo = null;
        var trackDataUrl = null;

        //timers
        var mapRedrawTimer = null;

        //map controls handles
        var magnifyingGlassControl = null;
        var magnifyingGlassLayer = null;
        var minMaxAltitudeBoxControl = null;
        var minMaxAltitudeBoxTogglerControl = null;
        var altitudeProfileControl = null;
        var scaleIndicatorControl = null;
        var trackDownloadControl = null;
        var recenterMapControl = null;

        //default is to show scale, but not show magnifying glass, 
        //  because I see no real use for it
        opts = $.extend({
            showFullScreen: false,
            showMagnifyingGlass: false,
            trackDownloadUrl: null,
            showScale: true,
            showMinMaxAltitude: false,
            showAltitudeProfile: false,
            tileLayer: null,
            trackLineColour: '#0033ff',
            trackLineWeight: 3,
            labels: {
                minAltitude: null,
                maxAltitude: null
            }
        }, opts);
        
        //tile layer is mandatory, so we will exit with error if not provided
        if (!opts.tileLayer || !opts.tileLayer.url) {
        	throw new Error('No valid tile layer configuration provided: missing tile layer url');
        }

        //if tile layer url has api key marker, 
        //  check that an api key has been provided
        //  (and throw error if none found);
        //  if an api key is required and has been provided
        //      replace the marker in the url template
        if (opts.tileLayer.url.indexOf('{apiKey}') >= 0) {
            if (!opts.tileLayer.apiKey) {
                throw new Error('No valid tile layer configuration provided: tile layer url requires api key, but no api key provided');
            } else {
                opts.tileLayer.url = opts.tileLayer.url.replace('{apiKey}', opts.tileLayer.apiKey);
            }
        }

        /**
         * Check if the plug-ins required for showing the magnifying glass are loaded
         * 
         * @return {boolean} True if they are, False otherwise
         * */
        function isMagnifyingGlassCapabilityLoaded() {
            return !!L.magnifyingGlass && !!L.control.magnifyingGlassButton;
        }

        /**
         * Check if the plug-ins required for showing the fullscreen capability are loaded
         * 
         * @return {boolean} True if they are, False otherwise
         * */
        function isFullScreenCapabilityLoaded() {
            return !!L.control.fullscreen;
        }

        /**
         * Check if the plug-ins required for showing 
         *  the required min-max altitude box are loaded
         * 
         * @return {boolean} True if they are, False otherwise
         */
        function isMinMaxAltitudeBoxCapabilityLoaded() {
            return !!L.Control.MinMaxAltitudeBox && !!L.control.minMaxAltitudeBox;
        }

        /**
         * Check if the plug-ins required for showing
         *  the altitude profile chart are loaded
         * 
         * @return {boolean} True if they are, False otherwise
         */
        function isAltitudeProfileCapabilityLoaded() {
            return !!L.Control.AltitudeProfile && !!L.control.altitudeProfile;
        }

        function destroyMap() {
            if (map) {
                if (scaleIndicatorControl) {
                    map.removeControl(scaleIndicatorControl);
                }

                if (magnifyingGlassControl) {
                    map.removeControl(magnifyingGlassControl);
                }

                if (magnifyingGlassLayer) {
                    magnifyingGlassLayer.removeFrom(map);
                }

                if (minMaxAltitudeBoxTogglerControl != null) {
                    map.removeControl(minMaxAltitudeBoxTogglerControl);
                }

                if (minMaxAltitudeBoxControl != null) {
                    map.removeControl(minMaxAltitudeBoxControl);
                }

                if (altitudeProfileControl != null) {
                    map.removeControl(altitudeProfileControl);
                }

                if (recenterMapControl != null) {
                    map.removeControl(recenterMapControl);
                }

                map.remove();
                
                scaleIndicatorControl = null;
                magnifyingGlassControl = null;
                magnifyingGlassLayer = null;
                minMaxAltitudeBoxTogglerControl = null;
                minMaxAltitudeBoxControl = null;
                altitudeProfileControl = null;
                recenterMapControl = null;
                
                map = null;
                $me = null;

                trackDataUrl = null;
                trackProfile = null;
                trackInfo = null;

                mapLoaded = false;
                mapDestroyed = true;
            }
        }

        /**
         * Adds the map scale indicator control
         * 
         * @param {L.Map} map The map to which the feature will be added
         * @return {L.Control.Scale} The newly registered scale control
         */
        function addScaleIndicator(map) {
            scaleIndicatorControl = L.control.scale({
                position: 'bottomleft',
                updateWhenIdle: true,
                imperial: false,
                metric: true
            });

            scaleIndicatorControl.addTo(map);
            return scaleIndicatorControl;
        }

        /**
         * Adds the magnifying glass feature, comprised of:
         * - the magnifying glass map layer;
         * - the magnifying glass button.
         * 
         * @param {L.Map} map The map to which this feature will be added
         * @return {L.Control.MagnifyingGlassButton}
         * */
        function addMagnifyingGlassCapability(map) {
            //create magnifying glass layer
            magnifyingGlassLayer = L.magnifyingGlass({
                layers: [ L.tileLayer(opts.tileLayer.url) ],
                zoomOffset: 3
            });

            //add the control to map, using the newly created layer
            magnifyingGlassControl = L.control.magnifyingGlassButton(magnifyingGlassLayer, {
                forceSeparateButton: true
            });

            magnifyingGlassControl.addTo(map);
            return magnifyingGlassControl;
        }

        /**
         * Adds the track download button to the given map, with the given URL
         * 
         * @param {L.Map} map The map to which the button will be added
         * @return {L.Control.IconButton} The registered track download button
         * */
        function addTrackDownloadCapability(map) {
            trackDownloadControl = L.control.iconButton('dashicons dashicons-download abp01-track-download-link', 
                opts.trackDownloadUrl);

            trackDownloadControl.addTo(map);
            return trackDownloadControl;
        }

        /**
         * Adds the overlay box that displays the minimum and maximum altitude
         * 
         * @param {L.Map} map The map to which the min-max altitude box should be added
         * @return {L.Control.MinMaxAltitudeBox} The registered control
         */
        function addMinMaxAltitudeBox(map) {
            minMaxAltitudeBoxControl = L.control.minMaxAltitudeBox(trackInfo, {
                minAltitude: opts.labels.minAltitude,
                maxAltitude: opts.labels.maxAltitude
            });

            if (recenterMapControl != null) {
                map.removeControl(recenterMapControl);
            }

            minMaxAltitudeBoxControl.addTo(map);

            if (recenterMapControl != null) {
                recenterMapControl.addTo(map);
            }

            return minMaxAltitudeBoxControl;
        }

        /**
         * Adds the button which toggles the min-max altitude box on or off
         * 
         * @param {L.Map} map The map to which the min-max altitude box toggler should be added
         * @return {L.Control.IconButton} The registered control
         */
        function addMinMaxAltitudeBoxToggler(map) {
            minMaxAltitudeBoxTogglerControl = L.control.iconButton('dashicons dashicons-sort abp01-track-minmaxalt-btn', null, {
                onClick: function(event) {
                    if (!minMaxAltitudeBoxControl) {
                        minMaxAltitudeBoxControl = addMinMaxAltitudeBox(map);
                    } else {
                        map.removeControl(minMaxAltitudeBoxControl);
                        minMaxAltitudeBoxControl = null;
                    }
                }
            });

            minMaxAltitudeBoxTogglerControl.addTo(map);
            return minMaxAltitudeBoxTogglerControl;
        }

        /**
         * Adds the altitude profile control to the map. 
         * It's represented as a button, that belongs to the map, 
         *  which toggles an altitude profile chart in a container 
         *  outside the map area.
         * 
         * @param {L.Map} map The map to which the altitude profile control should be added
         * @return {L.Control.AltitudeProfile} The registered control
         */
        function addAltitudeProfile(map) {
            altitudeProfileControl = L.control.altitudeProfile('abp01-altitude-profile-container', 
                trackProfile, 
                trackInfo, {
                    iconBaseUrl: opts.iconBaseUrl,
                    chartLineColor: opts.trackLineColour,
                    hoverPanTimeout: 100,
                    labels: {
                        altitude: opts.labels.altitude,
                        distance: opts.labels.distance
                    }
                });

            altitudeProfileControl.addTo(map);
            return altitudeProfileControl;
        }
        
        function addReCenterMapControl(map, bounds) {
            recenterMapControl = L.control.reCenterMap(bounds);
            recenterMapControl.addTo(map);
            return recenterMapControl;
        }

        /**
         * Render the tile layer attribution from th given options:
         * - tileLayer.attributionTxt - the label of the attribution text;
         * - tileLayer.attributionUrl - URL to point to (maybe author home page or further credits page)
         * 
         * @param {Object} opts The options used to initialize the plug-in
         * @return {String} The rendered attribution text
         *  */
        function getTileLayerAttribution(opts) {
        	var tileLayerAttribution = null;
        	var tileLayerAttributionTxt = opts.tileLayer.attributionTxt || opts.tileLayer.attributionUrl;            
            
            //only display attribution if there is a label (which is comprised of either the configured text or URL)
            if (tileLayerAttributionTxt) {
            	//if we have an URL, then display as an HTML link
            	if (opts.tileLayer.attributionUrl) {
            		tileLayerAttribution = '<a href="' + opts.tileLayer.attributionUrl + '" target="_blank">&copy; ' + tileLayerAttributionTxt + '</a>';
            	} else {
            		//otherwise, just display it as plain text
            		tileLayerAttribution = '&copy; ' + tileLayerAttributionTxt;
            	}
            }
            
            return tileLayerAttribution;
        }

        function renderMap(bounds) {
            var centerLat = bounds.northWest.lat 
                + (bounds.southWest.lat - bounds.northWest.lat) / 2;
            var centerLng = bounds.northWest.lng 
                + (bounds.northEast.lng - bounds.northWest.lng) / 2;

            var tileLayerUrl = opts.tileLayer.url;

            map = L.map($me.attr('id'), {
                center: L.latLng(centerLat, centerLng),
                fullscreenControl: opts.showFullScreen && isFullScreenCapabilityLoaded()
            });

            map.on('fullscreenchange', function() {
                if (magnifyingGlassLayer) {
                    magnifyingGlassLayer.removeFrom(map);
                }
            });

            //configure the map view to the bounds of the track
            map.fitBounds(L.latLngBounds(
                L.latLng(bounds.southWest.lat, bounds.southWest.lng),
                L.latLng(bounds.northEast.lat, bounds.northEast.lng)
            ));

            map.addLayer(L.tileLayer(tileLayerUrl, {
                attribution: getTileLayerAttribution(opts)
            }));

            //check if we should add the track download url
            if (opts.trackDownloadUrl) {
                addTrackDownloadCapability(map);
            }

            //check if we should add the magnifying glass
            if (opts.showMagnifyingGlass && isMagnifyingGlassCapabilityLoaded()) {
                addMagnifyingGlassCapability(map);
            }

            //check if we should add min/max altitude display controls
            if (opts.showMinMaxAltitude && isAltitudeProfileCapabilityLoaded) {
                addMinMaxAltitudeBoxToggler(map);
            }

            //check if we should add altitude profile display controls
            if (opts.showAltitudeProfile && isMinMaxAltitudeBoxCapabilityLoaded()) {
                addAltitudeProfile(map);
            }

            //check if we should add the map scale
            if (opts.showScale) {
                addScaleIndicator(map);
            }

            //add re-center map control
            addReCenterMapControl(map, bounds);
        }

        function plotRoute(route) {
            if (!route.parts || !route.parts.length) {
                return;
            }
            $.each(route.parts, function(idx, val) {
                if (!val.lines || !val.lines.length) {
                    return;
                }
                $.each(val.lines, function(lineIdx, lineVal) {
                    var path = [];
                    if (!lineVal.trackPoints || !lineVal.trackPoints.length) {
                        return;
                    }
                    $.each(lineVal.trackPoints, function(pointIdx, pointVal) {
                        var coord = pointVal.coordinate;
                        path.push(L.latLng(coord.lat, coord.lng));
                    });
                    path = L.polyline(path, {
                        color: opts.trackLineColour,
                        weight: opts.trackLineWeight
                    });
                    path.addTo(map);
                });
            });
        }

        function plotStartAndEnd(track, iconBaseUrl) {
            var iconStart, iconEnd;
            var start = track.start || null;
            var end = track.end || null;

            if (!start || !end) {
                return;
            }

            if (iconBaseUrl) {
                iconStart = L.icon({
                    iconUrl: iconBaseUrl + '/map/direction_up.png',
                    iconSize: [32, 37],
                    iconAnchor: [16, 37]
                });
                iconEnd = L.icon({
                    iconUrl: iconBaseUrl + '/map/direction_down.png',
                    iconSize: [32, 37],
                    iconAnchor: [16, 37]
                });
            } else {
                iconStart = new L.Icon.Default();
                iconEnd = new L.Icon.Default();
            }

            L.marker(L.latLng(start.coordinate.lat, start.coordinate.lng), {
                icon: iconStart
            }).addTo(map);

            L.marker(L.latLng(end.coordinate.lat, end.coordinate.lng), {
                icon: iconEnd
            }).addTo(map);
        }

        /**
         * Loads the track data from the configured URL and reports the result to the given callback as:
         * - success - whether or not the operation was completed successfully;
         * - bounds - the overall bounds of the loaded track;
         * - track - the actual track vector data.
         *
         * @param {Function} onReady The callback to be invoked upon completion
         * @return {void}
         * */
         function loadTrack(onReady) {
            function onReadyFn(success, bounds, route, profile, info) {
                if (onReady && $.isFunction(onReady)) {
                    onReady(success, bounds, route, profile, info);
                }
            }

            $.ajax(trackDataUrl, {
                type: 'GET',
                cache: false,
                dataType: 'json'
            }).done(function(data, status, xhr) {
                if (data && data.success && data.track) {
                    onReadyFn(true, 
                        data.track.bounds, 
                        data.track, 
                        data.profile || {}, 
                        data.info || {});
                } else {
                    onReadyFn(false, null, null, null, null);
                }
            }).fail(function(xhr, status, error) {
                onReadyFn(false, null, null);
            });
        }

        /**
         * Loads the map. This is a multi step process:
         * 1. Renders the map and centers it within the given bounds
         * 2. Renders the track on the map
         * 3. Renders the start and end track markers
         *
         * The loading process will not be carried out if the map has already been loaded
         * or if the map has been destroyed.
         *
         * It invokes the following handlers, if configured:
         *
         * - handlePreLoad, before loading the track data
         * - handleLoad, after all of the above steps have been completed
         *
         * @return {void}
         * */
        function loadMap() {
            if (mapLoaded || mapDestroyed) {
                return;
            }

            if (opts.handlePreLoad && $.isFunction(opts.handlePreLoad)) {
                opts.handlePreLoad.apply($me);
            }

            trackInfo = null;
            trackProfile = null;

            loadTrack(function(success, bounds, track, profile, info) {
                try {
                    if (success) {
                        trackInfo = info;
                        trackProfile = profile;

                        renderMap(bounds);
                        plotRoute(track.route);
                        plotStartAndEnd(track, opts.iconBaseUrl || null);
                    }
                } catch (e) {
                    console.log(e);
                    success = false;
                }

                if (opts.handleLoad && $.isFunction(opts.handleLoad)) {
                    opts.handleLoad.apply($me, [success]);
                }
            });
        }

        //track data URL is mandatory, so we must exit if it has not been defined
        trackDataUrl = opts.trackDataUrl || null;
        if (!trackDataUrl) {
            return null;
        }

        //watch for window resize events - map needs to be redrawn
        //when certain user actions get combined with window resize - have no freackin' clue why
        $(window).resize(function() {
            if (!map) {
                mapRedrawTimer = null;
                return;
            }
            if (mapRedrawTimer !== null) {
                clearTimeout(mapRedrawTimer);
            } else {
                mapRedrawTimer = setTimeout(function() {
                    map.invalidateSize();
                }, 250);
            }
        });

        //invoke map loading
        loadMap();

        //return public API
        return {
            loadMap: loadMap,
            destroyMap: destroyMap,
            forceRedraw: function() {
                map.invalidateSize();
            }
        };
    };
})(jQuery, window.abp01Leaflet);