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

/**
 * See these resources for some good points about parsing:
 * - http://stackoverflow.com/questions/24500347/gpx-file-parse-with-php-get-extensions-element-values
 * - http://stackoverflow.com/questions/8319556/gpx-parsing-patterns-and-standards
 *
 * See these resources for some info about the GPX format
 * - http://www.rigacci.org/wiki/doku.php/tecnica/gps_cartografia_gis/gpx
 * - http://www.topografix.com/gpx/1/1/#type_wptType
 * - http://wiki.openstreetmap.org/wiki/GPX
 * */

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Route_Track_DocumentParser_Gpx implements Abp01_Route_Track_DocumentParser {
	public function __construct() {
		if (!self::isSupported()) {
			throw new Exception('The GPX parser requirements are not met');
		}
	}

	public static function isSupported() {
		return function_exists('simplexml_load_string') &&
			function_exists('simplexml_load_file');
	}

	public function parse($sourceString) {
		if ($sourceString === null || empty($sourceString)) {
			throw new InvalidArgumentException('Empty GPX string');
		}

		$document = null;
		$prevUseErrors = libxml_use_internal_errors(true);

		$gpx = simplexml_load_string($sourceString, 'SimpleXMLElement');
		if (empty($gpx)) {
			$lastError = libxml_get_last_error();
			libxml_use_internal_errors($prevUseErrors);
			libxml_clear_errors();

			if ($lastError) {
				throw new Abp01_Route_Track_DocumentParser_Exception($lastError->message, 
					Abp01_Route_Track_DocumentParser_ErrorCode::ERROR_CATEGORY_DESERIALIZATION, 
					$lastError->code);
			} else {
				throw new Abp01_Route_Track_DocumentParser_Exception('Unknown deserialization error occurred', 
					Abp01_Route_Track_DocumentParser_ErrorCode::ERROR_CATEGORY_DESERIALIZATION, 
					Abp01_Route_Track_DocumentParser_ErrorCode::ERROR_GPX_UNKNOWN_ERROR);
			}
		}

		$document = $this->_parseGpx($gpx);
		libxml_use_internal_errors($prevUseErrors);

		return $document instanceof Abp01_Route_Track_Document 
			? $document 
			: null;
	}

	private function _parseGpx($gpx) {
		$meta = $this->_readMetaData($gpx);
		$document = new Abp01_Route_Track_Document($meta);

		$this->_parseAndCollectWayPoints($document, $gpx);
		$this->_parseAndCollectTracks($document, $gpx);

		return $document;
	}

	private function _readMetaData($gpx) {
		$node = $gpx->metadata;
		$meta = new stdClass();

		if (!empty($node)) {
			$meta->name = !empty($node->name) ? (string)$node->name : null;
			$meta->desc = !empty($node->desc) ? (string)$node->desc : null;
			$meta->keywords = !empty($node->keywords) ? (string)$node->keywords : null;
		} else {
			$meta->name = null;
			$meta->desc = null;
			$meta->keywords = null;
		}

		return $meta;
	}

	private function _parseAndCollectTracks(Abp01_Route_Track_Document $document, $gpx) {
		if (empty($gpx->trk)) {
			return;
		}
		foreach ($gpx->trk as $trkNode) {
			$trk = $this->_readTrack($trkNode);
			if ($trk instanceof Abp01_Route_Track_Part) {
				$document->addTrackPart($trk);
			}
		}
	}

	private function _readTrack($trkNode) {
		$name = !empty($trkNode->name) 
			? (string)$trkNode->name 
			: null;

		$track = new Abp01_Route_Track_Part($name);

		if (!empty($trkNode->trkseg)) {
			foreach ($trkNode->trkseg as $trgSegNode) {
				$trkSeg = $this->_readTrackSegment($trgSegNode);
				if ($trkSeg) {
					$track->addLine($trkSeg);
				}
			}
		}

		return $track;
	}

	private function _readTrackSegment($trkSegNode) {
		$segment = new Abp01_Route_Track_Line();
		if (!empty($trkSegNode->trkpt)) {
			foreach ($trkSegNode->trkpt as $trkptNode) {
				$trkpt = $this->_readPoint($trkptNode);
				if ($trkpt) {
					$segment->addPoint($trkpt);
				}
			}
		}
		return $segment;
	}

	private function _parseAndCollectWayPoints(Abp01_Route_Track_Document $doc, $gpx) {
		if (empty($gpx->wpt)) {
			return;
		}
		foreach ($gpx->wpt as $wptNode) {
			$wpt = $this->_readPoint($wptNode);
			if ($wpt instanceof Abp01_Route_Track_Point) {
				$doc->addWayPoint($wpt);
			}
		}
	}

	private function _readPoint($wptNode) {
		if (empty($wptNode['lat']) || empty($wptNode['lon'])) {
			return null;
		}

		$lat = floatval((string)$wptNode['lat']);
		$lon = floatval((string)$wptNode['lon']);
		$alt = !empty($wptNode->ele) ? floatval((string)$wptNode->ele) : 0;

		$coordinate = new Abp01_Route_Track_Coordinate($lat, $lon, $alt);
		$point = new Abp01_Route_Track_Point($coordinate);

		if (!empty($wptNode->name)) {
			$point->setName((string)$wptNode->name);
		}
		if (!empty($wptNode->desc)) {
			$point->setDescription((string)$wptNode->desc);
		}

		return $point;
	}

	public function getDefaultMimeType() {
		return 'application/gpx';
	}
}