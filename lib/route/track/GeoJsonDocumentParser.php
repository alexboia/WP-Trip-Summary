<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track_GeoJsonDocumentParser implements Abp01_Route_Track_DocumentParser {
	const GEOJSON_DESERIALIZATION_MAX_OBJECT_DEPTH = 1024;

	const GEOJSON_DESERIALIZATION_OUTPUT_ASSOC_ARRAY = true;

	const GEOJSON_UNSUPPORTED_DOCUMENT_ROOT = 0xFF01;

	const GEOJSON_TYPE_POINT = 'Point';

	const GEOJSON_TYPE_MULTIPOINT = 'MultiPoint';

	const GEOJSON_TYPE_LINESTRING = 'LineString';

	const GEOJSON_TYPE_MULTILINESTRING = 'MultiLineString';

	const GEOJSON_TYPE_POLYGON = 'Polygon';

	const GEOJSON_TYPE_MULTIPOLYGON = 'MultiPolygon';

	const GEOJSON_TYPE_GEOMETRY_COLLECTION = 'GeometryCollection';

	const GEOJSON_TYPE_FEATURE = 'Feature';

	const GEOJSON_TYPE_FEATURE_COLLECTION = 'FeatureCollection';
	
	private $_parseErrors = array();

	public function __construct() {
		if (!self::isSupported()) {
            throw new Exception('The GeoJson parser requirements are not met');
        }
	}

	public static function isSupported() {
		return function_exists('json_decode');
	}

	private function _cleanUtf8Bom($sourceString) {
		if(substr(bin2hex($sourceString), 0, 6) === 'efbbbf') {
			$sourceString = substr($sourceString, 3);
		}
		return $sourceString;
	}

    public function parse($sourceString) { 
		if ($sourceString === null || empty($sourceString)) {
            throw new InvalidArgumentException('Empty GeoJson string');
        }

		$document = null;
		$geoJsonObject = $this->_deserializeGeoJsonSource($sourceString);

		if ($geoJsonObject != null) {
			$geoJsonObjectType = $this->_getGeoJsonObjectType($geoJsonObject);
			switch ($geoJsonObjectType) {
				case self::GEOJSON_TYPE_FEATURE_COLLECTION:
					$document = $this->_parseGeoJsonFeatureCollectionAsDocument($geoJsonObject);
					break;
				case self::GEOJSON_TYPE_GEOMETRY_COLLECTION:
					$document = $this->_parseGeoJsonGeometryCollectionAsDocument($geoJsonObject);
					break;
				case self::GEOJSON_TYPE_FEATURE:
					$document = $this->_parseGeoJsonFeatureAsDocument($geoJsonObject);
					break;
				default:
					if ($this->_isGeoJsonGeometryType($geoJsonObjectType)) {
						$document = $this->_parseGeoJsonGeometryObjectAsDocument($geoJsonObject);
					} else {
						$this->_parseErrors[] = array(
							'code' => self::GEOJSON_UNSUPPORTED_DOCUMENT_ROOT,
							'message' => sprintf('Unsupported geojson document root: <%s>', $geoJsonObjectType),
							'file' => __FILE__,
							'line' => 0
						);
					}
					break;
			}
		} else {
			$this->_parseErrors[] = array(
				'code' => json_last_error(),
				'message' => json_last_error_msg(),
				'file' => __FILE__,
				'line' => 0
			);
		}

		return $document instanceof Abp01_Route_Track_Document 
            ? $document 
            : null;
	}

	private function _deserializeGeoJsonSource($sourceString) {
		$sourceString = trim($sourceString);
		$sourceString = $this->_cleanUtf8Bom($sourceString);

		return json_decode($sourceString, 
			self::GEOJSON_DESERIALIZATION_OUTPUT_ASSOC_ARRAY, 
			self::GEOJSON_DESERIALIZATION_MAX_OBJECT_DEPTH, 
			JSON_BIGINT_AS_STRING);
	}

	private function _isGeoJsonGeometryType($geoJsonObjectType) {
		return in_array($geoJsonObjectType, array(
			self::GEOJSON_TYPE_GEOMETRY_COLLECTION,
			self::GEOJSON_TYPE_LINESTRING,
			self::GEOJSON_TYPE_MULTILINESTRING,
			self::GEOJSON_TYPE_POINT,
			self::GEOJSON_TYPE_MULTIPOINT,
			self::GEOJSON_TYPE_POLYGON,
			self::GEOJSON_TYPE_MULTIPOLYGON
		));
	}

	private function _getGeoJsonObjectType($geoJsonObject) {
		return isset($geoJsonObject['type']) 
			? $geoJsonObject['type'] 
			: null;
	}

	private function _parseGeoJsonFeatureCollectionAsDocument($geoJsonObject) {
		$document = null;
		$features = $this->_getFeatures($geoJsonObject);
		if (!empty($features)) {
			$metadata = $this->_readMetaData($features);
			$document = new Abp01_Route_Track_Document($metadata);
			foreach ($features as $feature) {
				$this->_parseAndCollectFeature($document, $feature);
			}
		} else {
			$document = $this->_createDocumentWithEmptyMetadata();
		}

		return $document;
	}

	private function _getFeatures($geoJsonObject) {
		return isset($geoJsonObject['features']) 
			? $geoJsonObject['features'] 
			: array();
	}

	private function _parseAndCollectFeature(Abp01_Route_Track_Document $document, $feature) {
		$geometry = $this->_getFeatureGeometry($feature);
		if (!empty($geometry)) {
			$this->_parseAndCollectGeometry($document, $geometry);	
		}
	}

	private function _parseAndCollectGeometry(Abp01_Route_Track_Document $document, $geometry) {
		$geometryType = $this->_getGeoJsonObjectType($geometry);
		switch ($geometryType) {
			case self::GEOJSON_TYPE_POINT:
				$document->addWayPoint($this->_readDocumentTrackPointFromGeoJsonPointGeometry($geometry));
				break;
			case self::GEOJSON_TYPE_MULTIPOINT:
				$points = $this->_readDocumentTrackPointsFromGeoJsonMultiPointGeometry($geometry);
				foreach ($points as $point) {
					$document->addWayPoint($point);
				}
				break;
			case self::GEOJSON_TYPE_LINESTRING:
				$trackPart = $this->_readDocumentTrackPartFromGeoJsonLineStringGeometry($geometry);
				if (!$trackPart->isEmpty()) {
					$document->addTrackPart($trackPart);
				}
				break;
			case self::GEOJSON_TYPE_MULTILINESTRING:
				$trackPart = $this->_readDocumentTrackPartFromGeoJsonMultiLineStringGeometry($geometry);
				if (!$trackPart->isEmpty()) {
					$document->addTrackPart($trackPart);
				}
				break;
			case self::GEOJSON_TYPE_POLYGON:
				//polygons are treated as a set of lines (basically, as a multi line string)
				//	so one polygon = one track part
				$trackPart = $this->_readDocumentTrackPartFromGeoJsonPolygonGeometry($geometry);
				if (!$trackPart->isEmpty()) {
					$document->addTrackPart($trackPart);
				}
				break;
			case self::GEOJSON_TYPE_MULTIPOLYGON:
				//polygons are treated as a set of sets of lines (basically, as a set of multi line string)
				//	so one multi-polygon = multiple track parts
				$trackParts = $this->_readDocumentTrackPartsFromGeoJsonMultiPolygonGeometry($geometry);
				foreach ($trackParts as $trackPart) {
					if (!$trackPart->isEmpty()) {
						$document->addTrackPart($trackPart);
					}	
				}
				break;
			case self::GEOJSON_TYPE_GEOMETRY_COLLECTION:
				$this->_parseAndCollectGeometryCollection($document, $geometry);
				break;
		}
	}

	private function _parseAndCollectGeometryCollection(Abp01_Route_Track_Document $document, $geometryCollection) {
		$geometries = $this->_getGeometries($geometryCollection);
		foreach ($geometries as $geometry) {
			$this->_parseAndCollectGeometry($document, $geometry);
		}
	}

	private function _readMetaData(array $features) {
		if (count($features) == 1) {
			$meta = $this->_readMetadataFromFeature($features[0]);
		} else {
			$meta = $this->_createEmptyMetadata();
		}

		return $meta;
	}

	private function _createEmptyMetadata() {
		$meta = new stdClass();
		$meta->name = null;
		$meta->desc = null;
		$meta->keywords = null;
		return $meta;
	}

	private function _readMetadataFromFeature($feature) {
		$meta = $this->_createEmptyMetadata();
		$featureProperties = $this->_getFeatureProperties($feature);

		$meta->name = $this->_scanFeatureAndPropsForAttribute('title', 
			$feature, 
			$featureProperties);

		if (empty($meta->name)) {
			$meta->name = $this->_scanFeatureAndPropsForAttribute('name', 
				$feature, 
				$featureProperties);
		}

		$meta->desc = $this->_scanFeatureAndPropsForAttribute('desc', 
			$feature, 
			$featureProperties);

		if (empty($meta->desc)) {
			$meta->desc = $this->_scanFeatureAndPropsForAttribute('description', 
				$feature, 
				$featureProperties);
		}

		$meta->keywords = $this->_scanFeatureAndPropsForAttribute('keywords', 
			$feature, 
			$featureProperties);

		return $meta;
	}

	private function _scanFeatureAndPropsForAttribute($attribute, $feature, $featureProperties) {
		$value = null;
		if (!empty($featureProperties[$attribute])) {
			$value = $featureProperties[$attribute];
		} else if (!empty($feature[$attribute]) && is_string($feature[$attribute])) {
			$value = $feature[$attribute];
		}
		return $value;
	}

	private function _getFeatureProperties($feature) {
		return isset($feature['properties']) 
			? $feature['properties'] 
			: null;
	}

	private function _getFeatureGeometry($feature) {
		return isset($feature['geometry']) 
			? $feature['geometry'] 
			: null;
	}

	/**
	 * @return Abp01_Route_Track_Point 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPointFromGeoJsonPointGeometry($pointGeometry) {
		$position = $this->_getGeometryCoordinates($pointGeometry);
		return $this->_readDocumentTrackPointFromGeoJsonPosition($position);
	}

	/**
	 * @return Abp01_Route_Track_Point[]
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPointsFromGeoJsonMultiPointGeometry($multiPointGeometry) {
		$points = array();
		$positions = $this->_getGeometryCoordinates($multiPointGeometry);
		
		foreach ($positions as $position) {
			$points[] = $this->_readDocumentTrackPointFromGeoJsonPosition($position);
		}

		return $points;
	}

	/**
	 * @return Abp01_Route_Track_Point 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPointFromGeoJsonPosition(array $position) {
		$documentTrackCoordinate = $this->_readDocumentTrackCoordinateFromGeoJsonPosition($position);
		return new Abp01_Route_Track_Point($documentTrackCoordinate);
	}

	/**
	 * @return Abp01_Route_Track_Coordinate 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackCoordinateFromGeoJsonPosition(array $position) {
		if (count($position) < 2) {
			throw new Abp01_Route_Track_DocumentParserException('Geometry position may not have less than two elements.');
		}

		$longitude = floatval($position[0]);
		$latitude = floatval($position[1]);
		$altitude = isset($position[2]) 
			? floatval($position[2])
			: 0;

		$coordinate = new Abp01_Route_Track_Coordinate($latitude, 
			$longitude, 
			$altitude);

		return $coordinate;
	}

	/**
	 * @return Abp01_Route_Track_Part 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPartFromGeoJsonMultiLineStringGeometry($multiLineStringGeometry) {
		$documentTrackPart = new Abp01_Route_Track_Part();
		$documentTrackLines = $this->_readDocumentTrackLinesFromGeoJsonMultiLineStringGeometry($multiLineStringGeometry);

		foreach ($documentTrackLines as $line) {
			if (!$line->isEmpty()) {
				$documentTrackPart->addLine($line);
			}
		}

		return $documentTrackPart;
	}

	/**
	 * @return Abp01_Route_Track_Part 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPartFromGeoJsonLineStringGeometry($lineStringGeometry) {
		$documentTrackPart = new Abp01_Route_Track_Part();
		$documentTrackLine = $this->_readDocumentTrackLineFromGeoJsonLineStringGeometry($lineStringGeometry);

		if (!$documentTrackLine->isEmpty()) {
			$documentTrackPart->addLine($documentTrackLine);
		}

		return $documentTrackPart;
	}

	/**
	 * @return Abp01_Route_Track_Line[] 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackLinesFromGeoJsonMultiLineStringGeometry($multiLineStringGeometry) {
		$documentTrackLines = array();
		$lineStringsPositions = $this->_getGeometryCoordinates($multiLineStringGeometry);

		foreach ($lineStringsPositions as $lineStringPositions) {
			$documentTrackLines[] = $this->_readDocumentTrackLineFromGeoJsonPositions($lineStringPositions);
		}

		return $documentTrackLines;
	}

	/**
	 * @return Abp01_Route_Track_Line 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackLineFromGeoJsonLineStringGeometry($lineStringGeometry) {
		$positions = $this->_getGeometryCoordinates($lineStringGeometry);
		$documentTrackLine = $this->_readDocumentTrackLineFromGeoJsonPositions($positions);
		return $documentTrackLine;
	}

	/**
	 * @return Abp01_Route_Track_Line 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackLineFromGeoJsonPositions(array $positions) {
		$line = new Abp01_Route_Track_Line();
		foreach ($positions as $position) {
			$line->addPoint($this->_readDocumentTrackPointFromGeoJsonPosition($position));
		}
		return $line;
	}

	/**
	 * @return Abp01_Route_Track_Part[] 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPartsFromGeoJsonMultiPolygonGeometry($multiPolygonGeometry) {
		$parts = array();
		$polygonsPositions = $this->_getGeometryCoordinates($multiPolygonGeometry);

		foreach ($polygonsPositions as $polygonPositions) {
			$part = new Abp01_Route_Track_Part();
			foreach ($polygonPositions as $lineStringPosition) {
				$part->addLine($this->_readDocumentTrackLineFromGeoJsonPositions($lineStringPosition));
			}
			$parts[] = $part;
		}

		return $parts;
	}

	/**
	 * @return Abp01_Route_Track_Part 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackPartFromGeoJsonPolygonGeometry($polygonGeometry) {
		$documentTrackPart = new Abp01_Route_Track_Part();
		$documentTrackLines = $this->_readDocumentTrackLinesFromGeoJsonPolygonGeometry($polygonGeometry);
		
		foreach ($documentTrackLines as $line) {
			if (!$line->isEmpty()) {
				$documentTrackPart->addLine($line);
			}
		}

		return $documentTrackPart;
	}

	/**
	 * @return Abp01_Route_Track_Line[] 
	 * @throws Abp01_Route_Track_DocumentParserException 
	 */
	private function _readDocumentTrackLinesFromGeoJsonPolygonGeometry($polygonGeometry) {
		$lines = array();
		$lineStringsPositions = $this->_getGeometryCoordinates($polygonGeometry);

		foreach ($lineStringsPositions as $lineStringPosition) {
			$lines[] = $this->_readDocumentTrackLineFromGeoJsonPositions($lineStringPosition);
		}

		return $lines;
	}

	private function _getGeometryCoordinates($geometry) {
		return isset($geometry['coordinates']) 
			? $geometry['coordinates'] 
			: array();
	}

	private function _parseGeoJsonGeometryCollectionAsDocument($geoJsonObject) {
		$document = $this->_createDocumentWithEmptyMetadata($geoJsonObject);
		$this->_parseAndCollectGeometryCollection($document, $geoJsonObject);
		return $document;
	}

	private function _createDocumentWithEmptyMetadata() {
		$metadata = $this->_createEmptyMetadata();
		$document = new Abp01_Route_Track_Document($metadata);
		return $document;
	}

	private function _parseGeoJsonFeatureAsDocument($feature) {
		$document = $this->_createDocumentWithEmptyMetadata();
		$this->_parseAndCollectFeature($document, $feature);
		return $document;
	}

	private function _parseGeoJsonGeometryObjectAsDocument($geometry) {
		$document = $this->_createDocumentWithEmptyMetadata();
		$this->_parseAndCollectGeometry($document, $geometry);
		return $document;
	}

	private function _getGeometries($geoJsonObject) {
		return isset($geoJsonObject['geometries']) 
			? $geoJsonObject['geometries'] 
			: array();
	}

    public function hasErrors() { 

	}

    public function getLastErrors() { 

	}
}