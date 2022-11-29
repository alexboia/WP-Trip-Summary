<?php
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

class GpsDocumentFakerDataProvider extends \Faker\Provider\Base {
	private $_acceptableError = 0.1;

	public function __construct($generator, $acceptableError = 0.1) {
		parent::__construct($generator);
		$this->_acceptableError = $acceptableError;
	}

	public function gpsDocumentData($opts = array()) {
		if (isset($opts['metadata']) && $opts['metadata'] === false) {
			$opts['metadata'] = array(
				'name' => false,
				'desc' => false,
				'keywords' => false,
				'author' => false,
				'copyright' => false,
				'link' => false,
				'time' => false,
				'bounds' => false
			);
		}

		$defaults = $this->_getDefaults();
		$dataOpts = $this->_mergeOptsWithDefaults($opts, $defaults);

		$gpsDocumentData = $this->_generateDocumentData($dataOpts);
		return $gpsDocumentData;
	}

	private function _mergeOptsWithDefaults($opts, $defaults) {
		$mergedOpts = $opts;
		foreach ($defaults as $key => $value) {
			if (isset($mergedOpts[$key])) {
				if (is_array($defaults[$key])) {
					$mergedOpts[$key] = array_merge($defaults[$key], $mergedOpts[$key]);
				}
			} else {
				$mergedOpts[$key] = $value;
			}
		}
		return $mergedOpts;
	}

	public function randomizedGpsDocumentData($overrideOpts = array()) {
		$opts = array_merge($this->_getRandomizedDefaults(), $overrideOpts);
		return $this->gpsDocumentData($opts);
	}

	private function _generateDocumentData($options) {
		$from = $this->_generateStartLatLng($options['area'], 
			$options['span'], 
			$options['elevation']['generate'] 
				? $options['elevation']['base'] 
				: 0,
			$options['precision']);

		$metaArgs = $options['metadata'];
		$metadata = $this->_generateMetaData($metaArgs);

		$documentArgs = array(
			'from' => $from,
			'spanLat' => $options['span'],
			'spanLng' => $options['span'],
			'spanAlt' => $options['elevation']['generate'] 
				? $options['elevation']['span'] 
				: 0,
			'countTracks' => $options['tracks']['count'],
			'withTrackName' => $options['tracks']['name'],

			'countSegments' => $options['segments']['count'],

			'countPoints' => $options['points']['count'],
			'withPointName' => $options['points']['name'],
			'withPointDesc' => $options['points']['desc'],

			'countWaypointsRatio' => $options['waypoints'] 
				? 0.1 
				: 0,

			'precision' => $options['precision']
		);

		$content = $this->_generateDocumentContent($documentArgs);

		return array(
			'metadata' => $metadata,
			'content' => $content,
			'stats' => array(
				'totalTracks' => $documentArgs['countTracks'],
				'totalSegments' => $documentArgs['countTracks'] 
					* $documentArgs['countSegments'],
				'totalPoints' => $documentArgs['countTracks'] 
					* $documentArgs['countSegments'] 
					* $documentArgs['countPoints']
			)
		);
	}

	private function _generateMetaData($args) {
		return array(
			'name' => $args['name'] 
				? $this->generator->numerify('GPS Data Document ####') 
				: null,
			'desc' => $args['desc'] 
				? $this->generator->sentence($this->generator->numberBetween(3, 10)) 
				: null,
			'keywords' => $args['keywords'] 
				? join(',', $this->generator->words($this->generator->numberBetween(1, 10))) 
				: null,
			'author' => $args['author']
				? array(
					'name' => $this->generator->name,
					'email' => $this->generator->email,
					'link' => array(
						'text' => $this->generator->url,
						'type' => 'text/html'
					)
				)
				: null,
			'copyright' => $args['copyright']
				? array(
					'author' => $this->generator->company,
					'year' => $this->generator->numberBetween(1990, 2100),
					'license' => $this->generator->url
				)
				: null,
			'link' => $args['link']
				? array(
					'text' => $this->generator->url,
					'type' => 'text/html'
				)
				: null,
			'time' => $args['time']
				? $this->generator->dateTime->format('Ymd\THis\Z')
				: null,
			'bounds' => null
		);
	}

	private function _generateDocumentContent($args) {
		$tracks = array();
		$from = $args['from']; 
		$withTrackName = $args['withTrackName'];
		$withPointName = $args['withPointName'];
		$withPointDesc = $args['withPointDesc'];
		$spanLat = $args['spanLat']; 
		$spanLng = $args['spanLng']; 
		$spanAlt = $args['spanAlt']; 
		$countTracks = $args['countTracks']; 
		$countPoints = $args['countPoints'];
		$countSegments = $args['countSegments'];
		$countWaypointsRatio = $args['countWaypointsRatio'];
		$precision = $args['precision'];

		$deltaGenerationArgs = $this->_getDeltaLatLngAltGenerationArgs($spanLat, 
			$spanLng, 
			$spanAlt, 
			$countTracks, 
			$precision);

		$trackSpanLat = $deltaGenerationArgs['unscaledDeltaLat'];
		$trackSpanLng = $deltaGenerationArgs['unscaledDeltaLng'];
		$trackSpanAlt = $deltaGenerationArgs['unscaledDeltaAlt'];

		$minLat = PHP_INT_MAX;
		$minLng = PHP_INT_MAX;
		$minAlt = PHP_INT_MAX;
		$maxLat = ~PHP_INT_MAX;
		$maxLng = ~PHP_INT_MAX;
		$maxAlt = ~PHP_INT_MAX;

		$currentTo = $from;

		for ($i = 0; $i < $countTracks; $i ++) {
			$newDelta = $this->_generateDeltaLatLngAlt($deltaGenerationArgs);
			$newTrackArgs = array(
				'from' => $currentTo,
				'withTrackName' => $withTrackName,
				'withPointName' => $withPointName,
				'withPointDesc' => $withPointDesc,
				'spanLat' => round($trackSpanLat + $newDelta['newDeltaLat'], $precision),
				'spanLng' => round($trackSpanLng + $newDelta['newDeltaLng'], $precision),
				'spanAlt' => $trackSpanAlt !== 0 
					? round($trackSpanAlt + $newDelta['newDeltaAlt'], $precision) 
					: 0,
				'precision' => $precision,
				'countPoints' => $countPoints,
				'countSegments' => $countSegments
			);

			$newTrack = $this->_generateTrack($newTrackArgs);

			$minLat = min($minLat, $newTrack['bounds']['minLat']);
			$minLng = min($minLng, $newTrack['bounds']['minLng']);
			$maxLat = max($maxLat, $newTrack['bounds']['maxLat']);
			$maxLng = max($maxLng, $newTrack['bounds']['maxLng']);
			$minAlt = min($minAlt, $newTrack['bounds']['minAlt']);
			$maxAlt = max($maxAlt, $newTrack['bounds']['maxAlt']);

			$currentTo = $newTrack['to'];
			$tracks[] = $newTrack;
		}

		$waypoints = $this->_pickRandomWaypoints(array(
			'tracks' => $tracks,
			'countWaypointsRatio' => $countWaypointsRatio,
			'withPointName' => $withPointName,
			'withPointDesc' => $withPointDesc
		));

		return array(
			'waypoints' => $waypoints,
			'bounds' => compact('minLat', 
				'minLng', 
				'maxLat', 
				'maxLng', 
				'minAlt', 
				'maxAlt'),
			'tracks' => $tracks,
			'from' => $from,
			'to' => $currentTo
		);
	}

	private function _pickRandomWaypoints($args) {
		$waypoints = array();
		$tracks = $args['tracks'];
		$countWaypointsRatio = $args['countWaypointsRatio'];
		$withPointName = $args['withPointName'];
		$withPointDesc = $args['withPointDesc'];

		$nameGenerator = $this->_getNameGenerator($withPointName, 'Waypoint');
		$descGenerator = $this->_getDescGenerator($withPointDesc, 3);

		foreach ($tracks as $track) {
			foreach ($track['segments'] as $segment) {
				$countPoints = count($segment['points']);
				$intervalSize = floor($countPoints * $countWaypointsRatio);

				$interval = array(0, $intervalSize);
				while ($interval[0] < $interval[1] &&  $interval[1] <= $countPoints - 1) {
					$selectedIndex = $this->generator->numberBetween($interval[0], $interval[1]);

					$waypoint = $segment['points'][$selectedIndex];
					$waypoints[] = array_merge($waypoint, array(
						'name' => $nameGenerator(),
						'desc' => $descGenerator()
					));

					$interval[0] = min($interval[1] + 1, $countPoints - 1);
					$interval[1] = min($interval[1] + $intervalSize, $countPoints - 1);
				}
			}
		}

		return array(
			'from' => !empty($waypoints) 
				? $waypoints[0] 
				: null,
			'to' => !empty($waypoints) 
				? $waypoints[count($waypoints) - 1] 
				: null,
			'waypoints' => $waypoints
		);
	}

	private function _generateTrack($args) {
		$segments = array();
		$from = $args['from']; 
		$withTrackName = $args['withTrackName'];
		$withPointName = $args['withPointName'];
		$withPointDesc = $args['withPointDesc'];
		$spanLat = $args['spanLat']; 
		$spanLng = $args['spanLng']; 
		$spanAlt = $args['spanAlt']; 
		$countSegments = $args['countSegments']; 
		$countPoints = $args['countPoints'];
		$precision = $args['precision'];

		$deltaGenerationArgs = $this->_getDeltaLatLngAltGenerationArgs($spanLat, 
			$spanLng, 
			$spanAlt, 
			$countSegments, 
			$precision);

		$segmentSpanLat = $deltaGenerationArgs['unscaledDeltaLat'];
		$segmentSpanLng = $deltaGenerationArgs['unscaledDeltaLng'];
		$segmentSpanAlt = $deltaGenerationArgs['unscaledDeltaAlt'];

		$currentTo = $from;
		$name = $withTrackName 
			? $this->generator->numerify('GPX Track ####')
			: 0;

		$minLat = PHP_INT_MAX;
		$minLng = PHP_INT_MAX;
		$minAlt = PHP_INT_MAX;
		$maxLat = ~PHP_INT_MAX;
		$maxLng = ~PHP_INT_MAX;
		$maxAlt = ~PHP_INT_MAX;

		for ($i = 0; $i < $countSegments; $i ++) {
			$newDelta = $this->_generateDeltaLatLngAlt($deltaGenerationArgs);
			$newSegmentArgs = array(
				'from' => $currentTo,
				'withPointName' => $withPointName,
				'withPointDesc' => $withPointDesc,
				'spanLat' => round($segmentSpanLat + $newDelta['newDeltaLat'], $precision),
				'spanLng' => round($segmentSpanLng + $newDelta['newDeltaLng'], $precision),
				'spanAlt' => $segmentSpanAlt !== 0 
					? round($segmentSpanAlt + $newDelta['newDeltaAlt'], $precision) 
					: 0,
				'precision' => $precision,
				'countPoints' => $countPoints
			);

			$newSegment = $this->_generateSegment($newSegmentArgs);

			$minLat = min($minLat, $newSegment['bounds']['minLat']);
			$minLng = min($minLng, $newSegment['bounds']['minLng']);
			$maxLat = max($maxLat, $newSegment['bounds']['maxLat']);
			$maxLng = max($maxLng, $newSegment['bounds']['maxLng']);
			$minAlt = min($minAlt, $newSegment['bounds']['minAlt']);
			$maxAlt = max($maxAlt, $newSegment['bounds']['maxAlt']);

			$currentTo = $newSegment['to'];
			$segments[] = $newSegment;
		}

		return array(
			'name' => $name,
			'segments' => $segments,
			'bounds' => compact('minLat', 
				'minLng', 
				'maxLat', 
				'maxLng', 
				'minAlt', 
				'maxAlt'),
			'from' => $from,
			'to' => $currentTo
		);
	}

	private function _generateSegment($args) {
		$points = array();
		$from = $args['from']; 
		$withPointName = $args['withPointName'];
		$withPointDesc = $args['withPointDesc'];
		$spanLat = $args['spanLat']; 
		$spanLng = $args['spanLng']; 
		$spanAlt = $args['spanAlt']; 
		$countPoints = $args['countPoints']; 
		$precision = $args['precision'];

		$deltaGenerationArgs = $this->_getDeltaLatLngAltGenerationArgs($spanLat, 
			$spanLng, 
			$spanAlt, 
			$countPoints, 
			$precision);

		$pointSpanLat = $deltaGenerationArgs['unscaledDeltaLat'];
		$pointSpanLng = $deltaGenerationArgs['unscaledDeltaLng'];
		$pointSpanAlt = $deltaGenerationArgs['unscaledDeltaAlt'];

		$nameGenerator = $this->_getNameGenerator($withPointName, 'Point');
		$descGenerator = $this->_getDescGenerator($withPointDesc, 3);

		$currentTo = array_merge($from, array(
			'name' => $nameGenerator(),
			'desc' => $descGenerator()
		));

		$minLat = $currentTo['lat'];
		$minLng = $currentTo['lon'];
		$maxLat = $currentTo['lat'];
		$maxLng = $currentTo['lon'];
		$minAlt = $currentTo['ele'];
		$maxAlt = $currentTo['ele'];

		$points[] = $currentTo;

		for ($i = 0; $i < $countPoints - 1; $i ++) {
			$newDelta = $this->_generateDeltaLatLngAlt($deltaGenerationArgs, true);

			$generatedLat = round($currentTo['lat'] + $pointSpanLat + $newDelta['newDeltaLat'], 
				$precision);
			$generatedLng = round($currentTo['lon'] + $pointSpanLng + $newDelta['newDeltaLng'], 
				$precision);
			$generatedAlt = $pointSpanAlt !== 0 && $newDelta['newDeltaAlt'] !== 0 
				? round($currentTo['ele'] + $pointSpanAlt + $newDelta['newDeltaAlt'], 
					$precision)
				: 0;

			$currentTo = array(
				'lat' => $generatedLat,
				'lon' => $generatedLng,
				'ele' => $generatedAlt,
				'name' => $nameGenerator(),
				'desc' => $descGenerator()
			);

			$minLat = min($minLat, $currentTo['lat']);
			$minLng = min($minLng, $currentTo['lon']);
			$maxLat = max($maxLat, $currentTo['lat']);
			$maxLng = max($maxLng, $currentTo['lon']);
			$minAlt = min($minAlt, $currentTo['ele']);
			$maxAlt = max($maxAlt, $currentTo['ele']);

			$points[] = $currentTo;
		}

		return array(
			'points' => $points,
			'bounds' => compact('minLat', 
				'minLng', 
				'maxLat', 
				'maxLng', 
				'minAlt', 
				'maxAlt'),
			'from' => $from,
			'to' => $currentTo
		);
	}

	private function _generateDeltaLatLngAlt($args, $allowNegative = false) {
		extract($args);
		$newDeltaLat = round($this->generator->numberBetween($minDeltaLat, $maxDeltaLat) / $factor, 
			$precision);

		$newDeltaLng = round($this->generator->numberBetween($minDeltaLng, $maxDeltaLng) / $factor, 
			$precision);

		$newDeltaAlt = $minDeltaAlt !== 0  && $maxDeltaAlt !== 0
			? round($this->generator->numberBetween($minDeltaAlt, $maxDeltaAlt) / $factor, 
				$precision)
			: 0;

		$signumLat = $allowNegative && $this->generator->boolean() ? -1 : 1;
		$signumLng = $allowNegative && $this->generator->boolean() ? -1 : 1;
		$signumAlt = $allowNegative && $this->generator->boolean() ? -1 : 1;

		return array(
			'newDeltaLat' => $signumLat * $newDeltaLat,
			'newDeltaLng' => $signumLng * $newDeltaLng,
			'newDeltaAlt' => $signumAlt * $newDeltaAlt
		);
	}

	private function _getDeltaLatLngAltGenerationArgs($spanLat, $spanLng, $spanAlt, $count, $precision) {
		$factor = pow(10, $precision);
		$min = round(1 / $factor, $precision);

		$unscaledDeltaLat = max($min, round(($spanLat / $count), $precision));
		$unscaledDeltaLng = max($min, round(($spanLng / $count), $precision));
		$unscaledDeltaAlt = $spanAlt !== 0 
			? max($min, round(($spanAlt / $count), $precision))
			: 0;

		$maxDeltaLat = round($unscaledDeltaLat * $factor);
		$maxDeltaLng = round($unscaledDeltaLng * $factor);
		$maxDeltaAlt = $unscaledDeltaAlt !== 0 
			? round($unscaledDeltaAlt * $factor)
			: 0;

		return array(
			'factor' => $factor,
			'minDeltaValue' => $min,
			'precision' => $precision,
			
			'maxDeltaLat' => $maxDeltaLat,
			'minDeltaLat' => round($maxDeltaLat * (1 - $this->_acceptableError), $precision),
			'unscaledDeltaLat' => $unscaledDeltaLat,
			
			'maxDeltaLng' => $maxDeltaLng,
			'minDeltaLng' => round($maxDeltaLng * (1 - $this->_acceptableError), $precision),
			'unscaledDeltaLng' => $unscaledDeltaLng,
			
			'maxDeltaAlt' => $maxDeltaAlt,
			'minDeltaAlt' => $maxDeltaAlt !== 0
				? round($maxDeltaAlt * (1 - $this->_acceptableError), $precision)
				: 0,
			'unscaledDeltaAlt' => $unscaledDeltaAlt
		);
	}

	private function _generateStartLatLng($area, $span, $alt, $precision) {
		$areaBounds = $this->_getAreaBounds($area, $span);
		return array(
			'lat' => $this->generator->randomFloat($precision, 
				$areaBounds['minLat'], 
				$areaBounds['maxLat']),
			'lon' => $this->generator->randomFloat($precision, 
				$areaBounds['minLng'], 
				$areaBounds['maxLng']),
			'ele' => $alt,
			'name' => null,
			'desc' => null
		);
	}

	private function _getAreaBounds($area, $span = 0) {
		$allBounds = $this->_getDefinedBounds();
		$areaBounds = $allBounds[$area];

		$span = $span * $this->_acceptableError;

		if (is_numeric($span)) {
			$areaBounds = array(
				'minLat' => $areaBounds['minLat'] + $span, 
				'maxLat' => $areaBounds['maxLat'] - $span,
				'minLng' => $areaBounds['minLng'] + $span, 
				'maxLng' => $areaBounds['maxLng'] - $span
			);
		}

		return $areaBounds;
	}

	private function _getRandomizedDefaults() {
		$supportedAreas = $this->_getSupportedAreas();
		$precision = $this->generator->numberBetween(3, 6);
		$generateElevation = $this->generator->boolean();

		return array(
			'span' => $this->generator->randomFloat($precision, 0.2, 1.5),
			'precision'=> $precision,
			
			'tracks' => array(
				'count' => $this->generator->numberBetween(1, 3),
				'name' => $this->generator->boolean(),
			),

			'segments' => array(
				'count' => $this->generator->numberBetween(1, 3)
			),

			'points' => array(
				'count' => $this->generator->numberBetween(500, 1500),
				'name' => $this->generator->boolean(),
				'desc' => $this->generator->boolean()
			),

			'metadata' => array(
				'name' => $this->generator->boolean(),
				'desc' => $this->generator->boolean(),
				'keywords' => $this->generator->boolean(),
				'author' => $this->generator->boolean(),
				'copyright' => $this->generator->boolean(),
				'link' => $this->generator->boolean(),
				'time' => $this->generator->boolean(),
				'bounds' => $this->generator->boolean()
			),
			'waypoints' => $this->generator->boolean(),
			
			'elevation' => array(
				'generate' => $generateElevation,
				'base' => $generateElevation
					? $this->generator->numberBetween(50, 1500)
					: 0,
				'span' => $generateElevation 
					? $this->generator->numberBetween(50, 5000) 
					: 0
			),

			'area' => $this->generator->randomElement($supportedAreas)
		);
	}

	private function _getDefaults() {
		return array(
			//how many deggrees should the track span 
			//  (latitude and longitude)
			'span' => 1,

			//precision of the calculations: 
			//  how many decimals
			'precision' => 4,
			
			//controls track element generation
			'tracks' => array(
				//how many track elements
				'count' => 1,
				//whether to generate track name or not
				'name' => true,
			),

			//controls track segment elements generation
			'segments' => array(
				//how many track segments for each track
				'count' => 3
			),

			//controls points generation
			'points' => array(
				//how many points for each track segment
				'count' => 100,
				//whether to generate name for the points or not
				'name' => true,
				//whether to generate description for the points or not
				'desc' => true
			),

			'metadata' => array(
				//whether to generate document meta name or not
				'name' => true,
				//whether to generate document meta description or not
				'desc' => true,
				//whether to generate document meta keywords or not
				'keywords' => true,
				//whether to generate document meta author or not
				'author' => true,
				//whether to generate document meta copyright or not
				'copyright' => true,
				//whether to generate document meta link or not
				'link' => true,
				//whether to generate document meta generation time or not
				'time' => true,
				//whether to generate document meta bounds or not
				//  (ignored for now)
				'bounds' => true
			),

			//whether to generate waypoints or not.
			//  If set to true, 10% of points will be selected 
			//  and added as waypoints from each generated segment
			'waypoints' => true,
			
			'elevation' => array(
				//whether to generate elevation data or not
				'generate' => true,
				//base elevation to start with
				'base' => 150,
				//maximum of elevation to generate
				'span' => 1500
			),

			//which area of the globe to use as a playground
			'area' => 'north-east'
		);
	}

	private function _getDefinedBounds() {
		return array(
			'north-east' => array(
				'minLat' => 0, 
				'maxLat' => 85,
				'minLng' => 0, 
				'maxLng' => 180
			),
			'north-west' => array(
				'minLat' => 0, 
				'maxLat' => 85,
				'minLng' => -180, 
				'maxLng' => 0
			),
			'south-east' => array(
				'minLat' => -85, 
				'maxLat' => 0,
				'minLng' => 0, 
				'maxLng' => 180
			),
			'south-west' => array(
				'minLat' => -85, 
				'maxLat' => 0,
				'minLng' => -180, 
				'maxLng' => 0
			)
		);
	}

	private function _getNameGenerator($withName, $prefix) {
		return $withName 
			? function() use ($prefix) { return $this->generator->numerify(sprintf('%s ######', $prefix)); }
			: function() { return null; };
	}

	private function _getDescGenerator($withDesc, $nSentences) {
		return $withDesc
			? function() use ($nSentences) { return $this->generator->sentence($nSentences); }
			: function() { return null; };
	}

	private function _getSupportedAreas() {
		return array_keys($this->_getDefinedBounds());
	}
}