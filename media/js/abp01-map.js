(function($) {
    "use strict";

    $.fn.mapTrack = function(opts) {
        var map = null;
        var $me = this;
        var trackDataUrl = null;
        var mapRedrawTimer = null;

        function destroyMap() {
            if (map != null) {
                map.remove();
                map = null;
                $me = null;
                trackDataUrl = null;
            }
        }

        function renderMap(bounds) {
            var centerLat = (bounds.northEast.lat - bounds.southWest.lat) / 2;
            var centerLng = (bounds.northEast.lng - bounds.southWest.lng) / 2;

            map = L.map($me.attr('id'), {
                center: L.latLng(centerLat, centerLng)
            });

            map.addLayer(L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }));

            map.fitBounds(L.latLngBounds(
                L.latLng(bounds.southWest.lat, bounds.southWest.lng),
                L.latLng(bounds.northEast.lat, bounds.northEast.lng)
            ));
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

        trackDataUrl = opts.trackDataUrl || null;
        if (!trackDataUrl) {
            return null;
        }

        $me = this;
        if (opts.handlePreLoad && $.isFunction(opts.handlePreLoad)) {
            opts.handlePreLoad.apply($me);
        }

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

        return {
            destroyMap: destroyMap,
            forceRedraw: function() {
                map.invalidateSize();
            }
        };
    };
})(jQuery);