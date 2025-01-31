<?Php

use Yoast\PHPUnitPolyfills\Polyfills\AssertEqualsSpecializations;

/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class RouteTrackPointTests extends WP_UnitTestCase {
    use AssertEqualsSpecializations;

    public function test_canCompute_distanceToPoint() {
        $pointBucharest = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(44.4268, 26.1025));
        $pointRomCenter = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(45.9432, 24.9668));
        $pointAlbaIulia = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(46.0733, 23.5805));

        $this->assertEqualsWithDelta(190.7, $pointBucharest->distanceToPoint($pointRomCenter), 0.1);
        $this->assertEqualsWithDelta(190.7, $pointRomCenter->distanceToPoint($pointBucharest), 0.1);

        $this->assertEqualsWithDelta(269.2, $pointBucharest->distanceToPoint($pointAlbaIulia), 0.1);
        $this->assertEqualsWithDelta(269.2, $pointAlbaIulia->distanceToPoint($pointBucharest), 0.1);

        $this->assertEqualsWithDelta(108, $pointRomCenter->distanceToPoint($pointAlbaIulia), 0.1);
        $this->assertEqualsWithDelta(108, $pointAlbaIulia->distanceToPoint($pointRomCenter), 0.1);
    }

    public function test_canCompute_distanceToLine() {
        $pointBucharest = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(44.4268, 26.1025));       
        $pointAlbaIulia = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(46.0733, 23.5805));
        $pointRomCenter = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(45.9432, 24.9668));

        $this->assertEqualsWithDelta(153.4, $pointBucharest->distanceToLine($pointAlbaIulia, $pointRomCenter), 0.1);

        $pointCurrent = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(53.2611, -0.7972));
        $pointA = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(53.3206, -1.7297));
        $pointB = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(53.1887, 0.1334));

        $this->assertEqualsWithDelta(0.31, $pointCurrent->distanceToLine($pointA, $pointB), 0.1);
    }

    public function test_canComputeBearingToPoint() {
        $pointA = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(52.205, 0.119));
        $pointB = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(48.857, 2.351));

        $this->assertEqualsWithDelta(156.2, $pointA->bearingToPoint($pointB), 0.1);

        $pointBucharest = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(44.4268, 26.1025));
        $pointRomCenter = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(45.9432, 24.9668));
        $pointAlbaIulia = new Abp01_Route_Track_Point(new Abp01_Route_Track_Coordinate(46.0733, 23.5805));

        $this->assertEqualsWithDelta(332.57305556, $pointBucharest->bearingToPoint($pointRomCenter), 0.1);
        $this->assertEqualsWithDelta(313.73083333, $pointBucharest->bearingToPoint($pointAlbaIulia), 0.1);
        $this->assertEqualsWithDelta(278.19333333, $pointRomCenter->bearingToPoint($pointAlbaIulia), 0.1);
    }
}