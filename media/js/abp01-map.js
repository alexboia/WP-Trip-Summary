(function($) {
    "use strict";

    $.fn.mapTrack = function(opts) {
        var map = null;
        var $me = this;
        var trackDataUrl = null;
        var mapRedrawTimer = null;
        var mapLoaded = false;
        var mapDestroyed = false;
        var magnifyingGlassControl = null;
        var magnifyingGlassLayer = null;

        //default is to show scale, but not show magnifying glass, because I see no real use for it
        opts = $.extend({
            showFullScreen: false,
            showMagnifyingGlass: false,
            showScale: true
        }, opts);

        /**
         * Check if the plug-ins required for showing the magnifying glass are loaded
         * @return boolean True if they are, False otherwise
         * */
        function isMagnifyingGlassCapabilityLoaded() {
            return !!L.magnifyingGlass && !!L.control.magnifyingGlassButton;
        }

        /**
         * Check if the plug-ins required for showing the fullscreen capability are loaded
         * @return boolean True if they are, False otherwise
         * */
        function isFullScreenCapabilityLoaded() {
            return !!L.control.fullScreen;
        }

        function destroyMap() {
            if (map != null) {
                if (magnifyingGlassLayer) {
                    magnifyingGlassLayer.removeFromMap(map);
                }

                map.remove();
                magnifyingGlassControl = null;
                magnifyingGlassLayer = null;
                map = null;
                $me = null;
                trackDataUrl = null;
                mapLoaded = false;
                mapDestroyed = true;
            }
        }

        function addScaleIndicator(map) {
            L.control.scale({
                position: 'bottomleft',
                updateWhenIdle: true,
                imperial: false,
                metric: true
            }).addTo(map);
        }

        function addMagnifyingGlassCapability(map, tileLayerUrl) {
            magnifyingGlassLayer = L.magnifyingGlass({
                layers: [ L.tileLayer(tileLayerUrl) ],
                zoomOffset: 3
            });

            magnifyingGlassControl = L.control.magnifyingGlassButton(magnifyingGlassLayer, {
                forceSeparateButton: true
            }).addTo(map);
        }

        function renderMap(bounds) {
            var centerLat = (bounds.northEast.lat - bounds.southWest.lat) / 2;
            var centerLng = (bounds.northEast.lng - bounds.southWest.lng) / 2;
            var tileLayerUrl = 'http://{s}.tile.osm.org/{z}/{x}/{y}.png';

            map = L.map($me.attr('id'), {
                center: L.latLng(centerLat, centerLng),
                fullscreenControl: opts.showFullScreen && isFullScreenCapabilityLoaded()
            });

            map.on('fullscreenchange', function() {
                if (magnifyingGlassLayer) {
                    magnifyingGlassLayer.removeFromMap(map);
                }
            });

            map.fitBounds(L.latLngBounds(
                L.latLng(bounds.southWest.lat, bounds.southWest.lng),
                L.latLng(bounds.northEast.lat, bounds.northEast.lng)
            ));

            map.addLayer(L.tileLayer(tileLayerUrl, {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }));

            if (opts.showMagnifyingGlass && isMagnifyingGlassCapabilityLoaded()) {
                addMagnifyingGlassCapability(map, tileLayerUrl);
            }

            if (opts.showScale) {
                addScaleIndicator(map);
            }
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
                    path = L.polyline(path);
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
         * @param onReady The callback to be invoked upon completion
         * @return void
         * */
         function loadTrack(onReady) {
            function onReadyFn(success, bounds, route) {
                if (onReady && $.isFunction(onReady)) {
                    onReady(success, bounds, route);
                }
            }

            $.ajax(trackDataUrl, {
                type: 'GET',
                cache: false,
                dataType: 'json'
            }).done(function(data, status, xhr) {
                if (data && data.success && data.track) {
                    onReady(true, data.track.bounds, data.track);
                } else {
                    onReadyFn(false, null, null);
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
         * @return void
         * */
        function loadMap() {
            if (mapLoaded || mapDestroyed) {
                return;
            }

            if (opts.handlePreLoad && $.isFunction(opts.handlePreLoad)) {
                opts.handlePreLoad.apply($me);
            }

            loadTrack(function(success, bounds, track) {
                try {
                    if (success) {
                        renderMap(bounds);
                        plotRoute(track.route);
                        plotStartAndEnd(track, opts.iconBaseUrl || null);
                    }
                } catch (e) {
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
            if (map == null) {
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
})(jQuery);