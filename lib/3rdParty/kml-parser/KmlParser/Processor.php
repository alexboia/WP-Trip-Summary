<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser {

    use StepanDalecky\KmlParser\Entities\Container;
    use StepanDalecky\KmlParser\Entities\Kml;
    use StepanDalecky\KmlParser\Entities\LineString;
    use StepanDalecky\KmlParser\Entities\Placemark;
    use StepanDalecky\KmlParser\Entities\Point;

	class Processor {
		/**
		 * @var Delegate
		 */
		private $_delegate;

		public function __construct(Delegate $delegate) {
			$this->_delegate = $delegate;
		}

		public function processKml(Kml $kml): void {
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
			if ($placemark->hasPoint()) {
				$point = $placemark->getPoint();
				if ($point != null) {
					$this->_processPoint($point);
				}
			}

			if ($placemark->hasLineString()) {
				$lineString = $placemark->getLineString();
				if ($lineString != null) {
					$this->_processLineString($lineString);
				}
			}
		}

		private function _processPoint(Point $point): void {
			if ($point->hasCoordinate() && $this->_delegate->shouldProcessPointGeometry()) {
				$this->_delegate->processPoint($point);
			}
		}

		private function _processLineString(LineString $lineString): void {
			if ($lineString->hasCoordinates() && $this->_delegate->shouldProcessLineStringGeometry()) {
				$this->_delegate->processLineString($lineString);
			}
		}
	}	
}