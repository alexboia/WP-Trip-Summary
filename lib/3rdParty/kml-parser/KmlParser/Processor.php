<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser {

    use Exception;
    use StepanDalecky\KmlParser\Entities\Container;
    use StepanDalecky\KmlParser\Entities\Kml;
    use StepanDalecky\KmlParser\Entities\LinearRing;
    use StepanDalecky\KmlParser\Entities\LineString;
    use StepanDalecky\KmlParser\Entities\MultiGeometry;
    use StepanDalecky\KmlParser\Entities\Placemark;
    use StepanDalecky\KmlParser\Entities\Point;
    use StepanDalecky\KmlParser\Entities\Polygon;
    use StepanDalecky\KmlParser\Processor\Delegate;
    use StepanDalecky\KmlParser\Processor\FeatureMetadata;

	class Processor {
		/**
		 * @var Delegate
		 */
		private $_delegate;

		public function __construct(Delegate $delegate) {
			$this->_delegate = $delegate;
		}

		public function processKmlString(string $sourceString): void {
			try {
				$parser = Parser::fromString($sourceString);
				$kml = $parser->getKml();
				$this->_processKml($kml);
			} catch (Exception $exc) {
				$shouldThrow = $this->_delegate->error($exc);
				if ($shouldThrow) {
					throw $exc;
				}
			} finally {
				$this->_delegate->end();
			}
		}

		private function _processKml(Kml $kml): void {
			$this->_delegate->begin();
			if ($kml->hasFolder()) {
				$this->_processContainer($kml->getFolder());
			} else if ($kml->hasDocument()) {
				$this->_processContainer($kml->getDocument());
			}
		}

		private function _processContainer(Container $container) {
			if ($container->hasFolders()) {
				foreach ($container->getFolders() as $folder) {
					$this->_processContainer($folder);
				}
			}

			if ($container->hasDocuments()) {
				foreach ($container->getDocuments() as $document) {
					$this->_processContainer($document);
				}
			}

			if ($container->hasPlacemarks()) {
				foreach ($container->getPlacemarks() as $placemark) {
					$this->_processPlacemark($placemark);
				}
			}
		}

		private function _processPlacemark(Placemark $placemark): void {
			$metadata = FeatureMetadata::fromPlacemark($placemark);

			if ($this->_shouldProcessPointGeometry() && $placemark->hasPoint()) {
				$point = $placemark->getPoint();
				if ($point != null) {
					$this->_processPoint($point, $metadata);
				}
			}

			if ($this->_shouldProcessLineStringGeometry() && $placemark->hasLineString()) {
				$lineString = $placemark->getLineString();
				if ($lineString != null) {
					$this->_processLineString($lineString, $metadata);
				}
			}

			if ($this->_shouldProcessLinearRingGeometry() && $placemark->hasLinearRing()) {
				$linearRing = $placemark->getLinearRing();
				if ($linearRing != null) {
					$this->_processLinearRing($linearRing, $metadata);
				}
			}

			if ($this->_shouldProcessPolygonGeometry() && $placemark->hasPolygon()) {
				$polygon = $placemark->getPolygon();
				if ($polygon != null) {
					$this->_processPolygon($polygon, $metadata);
				}
			}

			if ($this->_shouldProcessMultiGeometry() && $placemark->hasMultiGeometry()) {
				$multiGeometry = $placemark->getMultiGeometry();
				if ($multiGeometry != null) {
					$this->_processMultiGeometry($multiGeometry, $metadata);
				}
			}
		}

		private function _shouldProcessPointGeometry() {
			return $this->_delegate->shouldProcessPointGeometry();
		}

		private function _processPoint(Point $point, FeatureMetadata $featureMetadata): void {
			if ($point->hasCoordinate()) {
				$this->_delegate->processPoint($point, $featureMetadata);
			}
		}

		private function _shouldProcessLineStringGeometry() {
			return $this->_delegate->shouldProcessLineStringGeometry();
		}

		private function _processLineString(LineString $lineString, FeatureMetadata $featureMetadata): void {
			if ($lineString->hasCoordinates()) {
				$this->_delegate->processLineString($lineString, $featureMetadata);
			}
		}

		private function _shouldProcessLinearRingGeometry() {
			return $this->_delegate->shouldProcessLinearRingGeometry();
		}

		private function _processLinearRing(LinearRing $linearRing, FeatureMetadata $featureMetadata): void {
			if ($linearRing->hasCoordinates()) {
				$this->_delegate->processLinearRing($linearRing, $featureMetadata);
			}
		}

		private function _shouldProcessPolygonGeometry(): bool {
			return $this->_delegate->shouldProcessPolygonGeometry();
		}

		private function _processPolygon(Polygon $polygon, FeatureMetadata $featureMetadata): void {
			if ($polygon->hasOuterBoundary()) {
				$this->_delegate->processPolygon($polygon, $featureMetadata);
			}
		}

		private function _shouldProcessMultiGeometry(): bool {
			return $this->_delegate->shouldProcessMultiGeometry();
		}

		private function _processMultiGeometry(MultiGeometry $multiGeometry, FeatureMetadata $featureMetadata): void {
			if ($this->_delegate->shouldIndividuallyProcessMultiGeometryParts()) {
				if ($this->_shouldProcessPointGeometry() && $multiGeometry->hasPoints()) {
					foreach ($multiGeometry->getPoints() as $mgPoint) {
						$this->_processPoint($mgPoint, $featureMetadata);
					}
				}

				if ($this->_shouldProcessLineStringGeometry() && $multiGeometry->hasLineStrings()) {
					foreach ($multiGeometry->getLineStrings() as $mgLineString) {
						$this->_processLineString($mgLineString, $featureMetadata);
					}
				}

				if ($this->_shouldProcessLinearRingGeometry() && $multiGeometry->hasLinearRings()) {
					foreach ($multiGeometry->getLinearRings() as $mgLinearRing) {
						$this->_processLinearRing($mgLinearRing, $featureMetadata);
					}
				}

				if ($this->_shouldProcessPolygonGeometry() && $multiGeometry->hasPolygons()) {
					foreach ($multiGeometry->getPolygons() as $mgPolygon) {
						$this->_processPolygon($mgPolygon, $featureMetadata);
					}
				}

				if ($multiGeometry->hasMultiGeometries()) {
					foreach ($multiGeometry->getMultiGeometries() as $mgMultiGeometry) {
						$this->_processMultiGeometry($mgMultiGeometry, $featureMetadata);
					}
				}
			} else {
				$this->_delegate->processMultiGeometry($multiGeometry, $featureMetadata);
			}
		}
	}	
}