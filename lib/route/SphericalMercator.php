<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

/**
 * Sources:
 * - The OSM article on the Mercator projection: http://wiki.openstreetmap.org/wiki/Mercator
 * - NodeJs Spherical Mercator implementation: https://github.com/mapbox/node-sphericalmercator
 */
class Abp01_Route_SphericalMercator {
    const A = 6378137.0;

    const MAX_EXTENT = 20037508.342789244;

    public function forward($lat, $lng) {
        $x = deg2rad($lng) * self::A;
        $y = log(tan(M_PI_4 + deg2rad($lat) / 2.0)) * self::A;
        return array(
            'mercX' => $x,
            'mercY' => $y
        );
    }

    public function inverse($mercX, $mercY) {
        $lng = rad2deg($mercX / self::A);
        $lat = rad2deg(2.0 * atan(exp($mercY / self::A)) - M_PI_2);
        return array(
            'lat' => $lat,
            'lng' => $lng
        );
    }
}