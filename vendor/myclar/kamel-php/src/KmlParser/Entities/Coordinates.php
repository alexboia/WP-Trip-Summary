<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {

    use KamelPhp\KmlParser\LatLngAltCollection;
    use KamelPhp\XmlElement\Element;

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