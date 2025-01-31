<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities;

class LinearRing extends Geometry {
	public function getCoordinates(): Coordinates|null {
		if (!$this->hasCoordinates()) {
			return null;
		}

		return new Coordinates($this->element->getChild(self::TagCoordinates));
	}

	public function hasCoordinates(): bool {
		return $this->element->hasChild(self::TagCoordinates);
	}
}
