<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

require_once __DIR__ . '/lib/GpsDocumentFakerDataProvider.php';
require_once __DIR__ . '/lib/GpxDocumentFakerDataProvider.php';
require_once __DIR__ . '/lib/GeoJsonDocumentFakerDataProvider.php';

class GpsDocumentFakerDataProviderTests extends \PHPUnit\Framework\TestCase {
	private $_faker;

	protected function setUp(): void {
		parent::setUp();
		$this->_faker = \Faker\Factory::create();
		$this->_faker->addProvider(new GpsDocumentFakerDataProvider($this->_faker));
		$this->_faker->addProvider(new GpxDocumentFakerDataProvider($this->_faker));
		$this->_faker->addProvider(new GeoJsonDocumentFakerDataProvider($this->_faker));
	}

	protected function tearDown(): void {
		$this->_faker->seed();
		parent::tearDown();
	}

	/**
	 * @dataProvider generationCases
	 */
	public function test_generatedCoordinatesAndBoundsStayWithinArea($seed, $options, $limits) {
		$this->_faker->seed($seed);
		$data = $this->_faker->gpsDocumentData($options);
		$content = $data['content'];
		$documentPoints = array();

		foreach ($content['tracks'] as $track) {
			$trackPoints = array();
			foreach ($track['segments'] as $segment) {
				foreach ($segment['points'] as $point) {
					$this->_assertValidCoordinates($point['lat'], $point['lon'], $limits);
				}

				$this->_assertBoundsAndEndpoints($segment, $segment['points']);
				$trackPoints = array_merge($trackPoints, $segment['points']);
			}

			$this->_assertBoundsAndEndpoints($track, $trackPoints);
			$documentPoints = array_merge($documentPoints, $trackPoints);
		}

		$this->_assertBoundsAndEndpoints($content, $documentPoints);
		$this->assertCount($data['stats']['totalPoints'], $documentPoints);
		
		foreach ($content['waypoints']['waypoints'] as $waypoint) {
			$this->_assertValidCoordinates($waypoint['lat'], $waypoint['lon'], $limits);
		}
	}

	public static function generationCases() {
		$cases = array();
		$areas = array(
			'north-east' => array(0, 85, 0, 180),
			'north-west' => array(0, 85, -180, 0),
			'south-east' => array(-85, 0, 0, 180),
			'south-west' => array(-85, 0, -180, 0)
		);

		foreach ($areas as $area => $limits) {
			$cases[$area . ' default span'] = array(1, array('area' => $area), $limits);
			$cases[$area . ' longitude drift'] = array(1, array_merge(self::_manyPointsOptions(), array('area' => $area)), $limits);
			$cases[$area . ' latitude drift'] = array(4, array_merge(self::_manyPointsOptions(), array('area' => $area)), $limits);
			$cases[$area . ' multiple boundary crossings'] = array(1, array(
				'area' => $area,
				'precision' => 0,
				'points' => array('count' => 200, 'name' => false, 'desc' => false),
				'metadata' => false
			), $limits);
		}

		return $cases;
	}

	private static function _manyPointsOptions() {
		return array(
			'precision' => 3,
			'tracks' => array('count' => 3, 'name' => false),
			'segments' => array('count' => 3),
			'points' => array('count' => 1500, 'name' => false, 'desc' => false),
			'metadata' => false
		);
	}

	private function _assertValidCoordinates($lat, $lng, $limits) {
		$this->assertGreaterThanOrEqual($limits[0], $lat);
		$this->assertLessThanOrEqual($limits[1], $lat);
		$this->assertGreaterThanOrEqual($limits[2], $lng);
		$this->assertLessThanOrEqual($limits[3], $lng);
	}

	private function _assertBoundsAndEndpoints($container, $points) {
		$this->assertSame(min(array_column($points, 'lat')), $container['bounds']['minLat']);
		$this->assertSame(max(array_column($points, 'lat')), $container['bounds']['maxLat']);
		$this->assertSame(min(array_column($points, 'lon')), $container['bounds']['minLng']);
		$this->assertSame(max(array_column($points, 'lon')), $container['bounds']['maxLng']);
		
		foreach (array('lat', 'lon') as $coordinate) {
			$this->assertSame($points[0][$coordinate], 
				$container['from'][$coordinate]);
			$this->assertSame($points[count($points) - 1][$coordinate], 
				$container['to'][$coordinate]);
		}
	}

	/**
	 * @dataProvider documentFormats
	 */
	public function test_serializedCoordinatesStayWithinGeographicLimits($format, $seed) {
		$this->_faker->seed($seed);
		$document = $this->_faker->$format(self::_manyPointsOptions());
		$coordinates = array();

		if ($format === 'gpx') {
			$xml = new DOMDocument();
			$this->assertTrue($xml->loadXML($document['content']['text']));
			
			$xpath = new DOMXPath($xml);
			foreach ($xpath->query('//*[local-name()="trkpt" or local-name()="wpt"]') as $point) {
				if (method_exists($point, 'getAttribute')) {
					$coordinates[] = array(
						(float)$point->getAttribute('lon'), 
						(float)$point->getAttribute('lat')
					);
				}
			}
		} else {
			$json = json_decode($document['content']['text'], 
				true, 
				512, 
				JSON_THROW_ON_ERROR);

			foreach ($json['features'] as $feature) {
				$geometry = $feature['geometry'];
				if ($geometry === null) {
					continue;
				}
				
				if ($geometry['type'] === 'Point') {
					$coordinates[] = $geometry['coordinates'];
				} else {
					foreach ($geometry['coordinates'] as $segment) {
						$coordinates = array_merge($coordinates, 
							$segment);
					}
				}
			}
		}

		$this->assertNotEmpty($coordinates);
		
		foreach ($coordinates as $point) {
			$this->_assertValidCoordinates($point[1], 
				$point[0], 
				array(-90, 90, -180, 180));
		}
	}

	public static function documentFormats() {
		return array(
			'GPX longitude' => array('gpx', 1),
			'GPX latitude' => array('gpx', 4),
			'GeoJSON longitude' => array('geoJson', 1),
			'GeoJSON latitude' => array('geoJson', 4)
		);
	}
}
