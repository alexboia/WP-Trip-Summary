<?php
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

class GeoJsonDocumentFakerDataProvider extends \Faker\Provider\Base {
	public function __construct($generator) {
		parent::__construct($generator);
	}

	public function geoJson($opts = array()) {
		$opts = $this->_disableUnsupportedOpts($opts);
		$documentData = $this->generator->gpsDocumentData($opts);
		return $this->_generateGeoJson($documentData, 
			$opts);
	}

	private function _disableUnsupportedOpts($opts) {
		if (isset($opts['tracks'])) {
			if (!is_array($opts['tracks'])) {
				$opts['tracks'] = array();
			}
			
			$opts['tracks']['name'] = false;
		}

		if (isset($opts['metadata'])) {
			if (!is_array($opts['metadata'])) {
				$opts['metadata'] = array();
			}

			$opts['metadata']['author'] = false;
			$opts['metadata']['copyright'] = false;
			$opts['metadata']['link'] = false;
			$opts['metadata']['time'] = false;
			$opts['metadata']['bounds'] = false;
		}

		return $opts;
	}

	private function _generateGeoJson(array $documentData, array $geoJsonOpts) {
		$defaults = $this->_getDefaults();
		$renderOpts = $this->_extractRenderOpts($geoJsonOpts, $defaults);
		$renderedDocument = $this->_renderDocument($documentData, $renderOpts);

		$retVal = array(
			'content' => &$renderedDocument,
			'data' => null
		);

		if ($renderOpts['addData']) {
			$retVal['data'] = &$documentData;
		}

		return $retVal;
	}

	private function _extractRenderOpts($gpxOpts, $defaults) {
		$mergedOpts = array();
		foreach ($defaults as $key => $defaultValue) {
			if (isset($gpxOpts[$key])) {
				$mergedOpts[$key] = $gpxOpts[$key];
			} else {
				$mergedOpts[$key] = $defaultValue;
			}
		}
		return $mergedOpts;
	}

	public function randomizedGeoJson($overrideOpts = array()) {
		$opts = array_merge($this->_getRandomizedDefaults(), $overrideOpts);
		$opts = $this->_disableUnsupportedOpts($opts);

		$documentData = $this->generator->randomizedGpsDocumentData($opts);
		return $this->_generateGeoJson($documentData, 
			$overrideOpts);
	}

	public function _renderDocument(&$documentData, $renderOpts) {
		$content = null;
		$contentNoPretty = null;
		$geojsonDocument = $this->_convertToGeoJsonData($documentData, $renderOpts);

		if ($renderOpts['prettify']) {
			if (isset($renderOpts['addNoPretty']) && $renderOpts['addNoPretty'])  {
				$contentNoPretty = json_encode($geojsonDocument);
			}
			$content = json_encode($geojsonDocument, JSON_PRETTY_PRINT);
		} else {
			$content = json_encode($geojsonDocument);
		}

		return array(
			'text' => &$content,
			'textNoPretty' => &$contentNoPretty
		);
	}

	private function _convertToGeoJsonData(&$documentData, $renderOpts) {
		$rootFeatures = array();

		$geojsonMetadata = $this->_constructMetadata($documentData['metadata']);
		$rootFeatures[] = $geojsonMetadata;

		$geojsonWaypoints = $this->_constructWaypoints($documentData['content'], $renderOpts['precision']);
		$rootFeatures = array_merge($rootFeatures, $geojsonWaypoints);

		$geojsonTracks = $this->_constructTracks($documentData['content'], $renderOpts['precision']);
		$rootFeatures = array_merge($rootFeatures, $geojsonTracks);

		return array(
			'type' => 'FeatureCollection',
			'features' => $rootFeatures
		);
	}

	private function _constructMetadata(&$metadataInfo) {
		$name = !empty($metadataInfo['name']) 
			? $metadataInfo['name'] 
			: null;
		$description = !empty($metadataInfo['desc']) 
			? $metadataInfo['desc'] 
			: null;
		$keywords = !empty($metadataInfo['keywords']) 
			? $metadataInfo['keywords'] 
			: null;

		return array(
			'type' => 'Feature',
			'geometry' => null,
			'properties' => $this->_constructProperties($name, 
				$description, 
				$keywords)
		);
	}

	private function _constructProperties($name, $description, $keywords) {
		return array(
			'name' => $name,
			'desc' => $description,
			'keywords' => $keywords
		);
	}

	private function _constructWaypoints(&$contentInfo, $precision) {
		$geojsonWaypoints = array();
		$waypointsInfo = $contentInfo['waypoints'];
		
		foreach ($waypointsInfo['waypoints'] as $wptInfo) {
			$geojsonWaypoints[] = $this->_constructWaypoint($wptInfo, 
				$precision);
		}

		return $geojsonWaypoints;
	}

	private function _constructWaypoint(&$wptInfo, $precision) {
		$name = !empty($wptInfo['name']) 
			? $wptInfo['name'] 
			: null;
		$description = !empty($wptInfo['desc']) 
			? $wptInfo['desc'] 
			: null;

		return array(
			'type' => 'Feature',
			'properties' => $this->_constructProperties($name, 
				$description, 
				null),
			'geometry' => $this->_constructPoint($wptInfo, 
				$precision)
		);
	}

	private function _constructPoint(&$pointInfo, $precision) {
		return array(
			'type' => 'Point',
			'coordinates' => $this->_constructCoordinates($pointInfo, $precision)
		);
	}

	private function _constructCoordinates(&$pointInfo, $precision) {
		$lat = (float)number_format($pointInfo['lat'], $precision, '.', '');
		$lon = (float)number_format($pointInfo['lon'], $precision, '.', '');

		if (!empty($pointInfo['ele'])) {
			$altitude = (float)number_format($pointInfo['ele'], $precision, '.', '');
		} else {
			$altitude = null;
		}

		$coordinates = array(
			$lon,
			$lat
		);

		if ($altitude !== null) {
			$coordinates[] = $altitude;
		}

		return $coordinates;
	}

	private function _constructTracks(&$contentInfo, $precision) {
		$tracks = array();
		$tracksInfo = $contentInfo['tracks'];

		foreach ($tracksInfo as $trackInfo) {
			$segments = array();
			$name = !empty($trackInfo['name']) 
				? $trackInfo['name'] 
				: null;
	
			foreach ($trackInfo['segments'] as $segmentInfo) {
				$segments[] = $this->_constructSegment($segmentInfo, $precision);
			}
	
			$tracks[] = array(
				'type' => 'Feature',
				'properties' => $this->_constructProperties($name, null, null),
				'geometry' => array(
					'type' => 'MultiLineString',
					'coordinates' => $segments
				)
			);
		}

		return $tracks;
	}

	private function _constructSegment(&$segmentInfo, $precision) {
		return $this->_constructLineString($segmentInfo['points'], 
			$precision);
	}

	private function _constructLineString(&$points, $precision) {
		$coordinates = array();
		foreach ($points as $pointInfo) {
			$coordinates[] = $this->_constructCoordinates($pointInfo, 
				$precision);
		}

		return $coordinates;
	}

	private function _getRandomizedDefaults() {
		$precision = $this->generator->numberBetween(3, 6);
		return array(
			'precision'=> $precision,
			'prettify' => true,
            'addNoPretty' => false,
            'addData' => true
		);
	}

	private function _getDefaults() {
		return array(
			//precision of the calculations: 
            //  how many decimals
            'precision'=> 4,

			//whether to format the XML in a clean an readable way 
            //  (indendented and with new lines)
            'prettify' => true,

            //if prettify, then set this to true to also include 
            //  in the return result the not-prettified XML text
            'addNoPretty' => false,

            //whether to include the data source used to generate the XML document
            //  in the return result or not
            'addData' => true
		);
	}
}