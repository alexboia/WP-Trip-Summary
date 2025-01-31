<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {

    use KamelPhp\KmlParser\LatLngAlt;
    use KamelPhp\XmlElement\Element;

	class Coordinate extends Entity {
		/**
		 * @var LatLngAlt
		 */
		private $coords;

		public function __construct(Element $element) {
			parent::__construct($element);
			$this->_parseCoordinates();
		}

		private function _parseCoordinates() {
			$asString = $this->element->getValue();
			if (!empty($asString)) {
				$this->coords = new LatLngAlt($asString);
			} else {
				$this->coords = new LatLngAlt('');
			}
		}

		public function getLatLngAlt(): LatLngAlt {
			return $this->coords;
		}
	}
}