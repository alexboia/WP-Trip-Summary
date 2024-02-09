<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities {

    use StepanDalecky\KmlParser\LatLngAltCollection;
    use StepanDalecky\XmlElement\Element;

	class Coordinates extends Entity {
		/**
		 * @var LatLngAltCollection
		 */
		private $coordsCollection;

		public function __construct(Element $element) {
			parent::__construct($element);
			$this->_parseCoordinates();
		}

		private function _parseCoordinates() {
			$asString = $this->element->getValue();
			if (!empty($asString)) {
				$this->coordsCollection = new LatLngAltCollection($asString);
			} else {
				$this->coordsCollection = LatLngAltCollection::empty();
			}
		}

		public function getLatLngAltCollection(): LatLngAltCollection {
			return $this->coordsCollection;
		}
	}
}