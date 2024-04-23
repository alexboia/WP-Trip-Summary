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

use StepanDalecky\KmlParser\Entities\Coordinate;
use StepanDalecky\KmlParser\Entities\Coordinates;
use StepanDalecky\KmlParser\Entities\Point;
use StepanDalecky\KmlParser\Entities\LineString;
use StepanDalecky\KmlParser\Entities\LinearRing;
use StepanDalecky\KmlParser\Entities\Polygon;
use StepanDalecky\KmlParser\Entities\MultiGeometry;
use StepanDalecky\KmlParser\Processor\Delegate;
use StepanDalecky\KmlParser\Processor\FeatureMetadata;

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Track_DocumentParser_Kml_LibKmlProcessorDelegate implements Delegate {

	/**
	 * @var Abp01_Route_Track_Document
	 */
	private $_document;

	public function __construct(Abp01_Route_Track_Document $document) {
		$this->_document = $document;
	}

    public function begin(): void { 
		return;
	}

    public function error(Exception $exception): bool { 
		return true;
	}

    public function processPoint(Point $point, FeatureMetadata $featureMetadata): void { 
		$coordinate = $this->_convertFromKmlCoordinate($point->getCoordinate());
		$point = $this->_setPointMetadata(new Abp01_Route_Track_Point($coordinate), $featureMetadata);
		$this->_document->addWayPoint($point);
	}

	private function _convertFromKmlCoordinate(Coordinate $kmlCoordinate) {
		$latLngAlt = $kmlCoordinate->getLatLngAlt();
		$coordinate = new Abp01_Route_Track_Coordinate($latLngAlt->getLatitude(), 
			$latLngAlt->getLongitude(), 
			$latLngAlt->getAltitude());
		return $coordinate;
	}

	private function _convertFromKmlCoordinates(Coordinates $kmlCoordinates) {
		$trackLine = new Abp01_Route_Track_Line();
		$latLngAltCollection = $kmlCoordinates->getLatLngAltCollection();
		foreach ($latLngAltCollection as $latLngAlt) {
			$coordinate = new Abp01_Route_Track_Coordinate($latLngAlt->getLatitude(), 
				$latLngAlt->getLongitude(), 
				$latLngAlt->getAltitude());
			$trackLine->addPoint(new Abp01_Route_Track_Point($coordinate));
		}
		
		return $trackLine;
	}

	private function _setPointMetadata(Abp01_Route_Track_Point $point, FeatureMetadata $featureMetadata) {
		if (!empty($featureMetadata->hasName())) {
			$point->setName($featureMetadata->getName());
		}
		if (!empty($featureMetadata->hasDescription())) {
			$point->setDescription($featureMetadata->getDescription());
		}

		return $point;
	}

    public function shouldProcessPointGeometry(): bool { 
		return true;
	}

    public function processLineString(LineString $lineString, FeatureMetadata $featureMetadata): void { 
		$this->_addTrackPartFromLineString($lineString, $featureMetadata);
	}

	private function _addTrackPartFromLineString(LineString $lineString, FeatureMetadata $featureMetadata) {
		$trackLine = $this->_convertFromKmlLineString($lineString);
		if (!$trackLine->isEmpty()) {
			$trackPart = new Abp01_Route_Track_Part($featureMetadata->getName());
			$trackPart->addLine($trackLine);
			$this->_document->addTrackPart($trackPart);
		}
	}

	private function _convertFromKmlLineString(LineString $lineString) {
		return $this->_convertFromKmlCoordinates($lineString->getCoordinates());
	}

    public function shouldProcessLineStringGeometry(): bool { 
		return true;
	}

    public function processLinearRing(LinearRing $linearRing, FeatureMetadata $featureMetadata): void { 
		$this->_addTrackPartFromLinearRing($linearRing, $featureMetadata);
	}

	private function _addTrackPartFromLinearRing(LinearRing $linearRing, FeatureMetadata $featureMetadata) {
		$trackLine = $this->_convertFromKmlLinearRing($linearRing);
		if (!$trackLine->isEmpty()) {
			$trackPart = new Abp01_Route_Track_Part($featureMetadata->getName());
			$trackPart->addLine($trackLine);
			$this->_document->addTrackPart($trackPart);
		}
	}

	private function _convertFromKmlLinearRing(LinearRing $linearRing) {
		return $this->_convertFromKmlCoordinates($linearRing->getCoordinates());
	}

    public function shouldProcessLinearRingGeometry(): bool { 
		return true;
	}

    public function processPolygon(Polygon $polygon, FeatureMetadata $featureMetadata): void { 
		if ($polygon->hasOuterBoundary()) {
			$this->_addTrackPartFromLinearRing($polygon->getOuterBoundary(), $featureMetadata);
		}

		if ($polygon->hasInnerBoundary()) {
			foreach ($polygon->getInnerBoundary() as $innerBoundary) {
				if ($innerBoundary != null) {
					$this->_addTrackPartFromLinearRing($innerBoundary, $featureMetadata);
				}
			}
		}
	}

    public function shouldProcessPolygonGeometry(): bool { 
		return true;
	}

    public function processMultiGeometry(MultiGeometry $multiGeometry, FeatureMetadata $featureMetadata): void { 
		//Multi geometry is not processed directly, 
		//	parts are processed individually, as returned 
		//	by shouldIndividuallyProcessMultiGeometryParts().
		return;
	}

    public function shouldProcessMultiGeometry(): bool { 
		return true;
	}

    public function shouldIndividuallyProcessMultiGeometryParts(): bool { 
		return true;
	}

    public function end(): void { 
		return;
	}

	public function getDocument(): Abp01_Route_Track_Document {
		return $this->_document;
	}
}