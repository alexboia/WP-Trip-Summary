<?php

use Yoast\PHPUnitPolyfills\Polyfills\AssertEqualsSpecializations;

/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class RouteSphericalMercator extends WP_UnitTestCase {
    use AssertEqualsSpecializations;

    public function test_forwardProjection_specificCoords() {
        $merc = $this->_getProjSphericalMercator();

        $merc_45_45 = $merc->forward(45, 45);
        $this->assertEqualsWithDelta(5009377.09, $merc_45_45['mercX'],  0.05);
        $this->assertEqualsWithDelta(5621521.49, $merc_45_45['mercY'], 0.05);

        $merc_0_0 = $merc->forward(0, 0);
        $this->assertEqualsWithDelta(0.00, $merc_0_0['mercX'], 0.05);
        $this->assertEqualsWithDelta(0.00, $merc_0_0['mercY'], 0.05);

        $merc_85_180 = $merc->forward(85, 180);
        $this->assertEqualsWithDelta(20037508.34, $merc_85_180['mercX'], 0.05);
        $this->assertEqualsWithDelta(19971868.88, $merc_85_180['mercY'], 0.05);

        $merc_85_180_neg = $merc->forward(-85, -180);
        $this->assertEqualsWithDelta(-20037508.34, $merc_85_180_neg['mercX'], 0.05);
        $this->assertEqualsWithDelta(-19971868.88, $merc_85_180_neg['mercY'], 0.05);

        $merc_bucharest = $merc->forward(44.426165, 26.1023329);
        $this->assertEqualsWithDelta(2905698.41, $merc_bucharest['mercX'], 0.05);
        $this->assertEqualsWithDelta(5531630.80, $merc_bucharest['mercY'], 0.05);

        $merc_gw = $merc->forward(51.4825766, -0.0076589);
        $this->assertEqualsWithDelta(-852.58, $merc_gw['mercX'], 0.05);
        $this->assertEqualsWithDelta(6707103.99, $merc_gw['mercY'], 0.05);
    }

    public function test_reverseProjection_specificCoords() {
        $merc = $this->_getProjSphericalMercator();

        $inv_45_45 = $merc->inverse(5009377.09, 5621521.49);
        $this->assertEqualsWithDelta(45, $inv_45_45['lat'],  0.01);
        $this->assertEqualsWithDelta(45, $inv_45_45['lng'],  0.01);

        $inv_0_0 = $merc->inverse(0.00, 0.00);
        $this->assertEqualsWithDelta(0.00, $inv_0_0['lat'], 0.01);
        $this->assertEqualsWithDelta(0.00, $inv_0_0['lng'], 0.01);

        $inv_85_180 = $merc->inverse(20037508.34, 19971868.88);
        $this->assertEqualsWithDelta(85, $inv_85_180['lat'], 0.05);
        $this->assertEqualsWithDelta(180, $inv_85_180['lng'], 0.05);

        $inv_85_180_neg = $merc->inverse(-20037508.34, -19971868.88);
        $this->assertEqualsWithDelta(-85, $inv_85_180_neg['lat'], 0.05);
        $this->assertEqualsWithDelta(-180, $inv_85_180_neg['lng'], 0.05);

        $inv_bucharest = $merc->inverse(2905698.41, 5531630.80);
        $this->assertEqualsWithDelta(44.426165, $inv_bucharest['lat'], 0.05);
        $this->assertEqualsWithDelta(26.1023329, $inv_bucharest['lng'], 0.05);

        $inv_gw = $merc->inverse(-852.58, 6707103.99);
        $this->assertEqualsWithDelta(51.4825766, $inv_gw['lat'], 0.05);
        $this->assertEqualsWithDelta(-0.0076589, $inv_gw['lng'], 0.05);
    }

    public function test_inverseMatchesForward() {
        $merc = $this->_getProjSphericalMercator();
        
        for ($lng = -180; $lng <= 180; $lng++) {
            for ($lat = -85; $lat <= 85; $lat ++) {
                $mercVal = $merc->forward($lat, $lng);
                $invVal = $merc->inverse($mercVal['mercX'], $mercVal['mercY']);
                $this->assertEqualsWithDelta($lng, $invVal['lng'], 0.05);
                $this->assertEqualsWithDelta($lat, $invVal['lat'], 0.05);
            }
        }
    }

    private function _getProjSphericalMercator() {
        return new Abp01_Route_SphericalMercator();
    }
}