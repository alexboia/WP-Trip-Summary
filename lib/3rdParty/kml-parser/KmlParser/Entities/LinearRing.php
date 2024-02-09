<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class LinearRing extends Geometry {
	public function getCoordinates(): Coordinates|null {
		if (!$this->hasCoordinates()) {
			return null;
		}

		return new Coordinates($this->element->getChild('coordinates'));
	}

	public function hasCoordinates(): bool {
		return $this->element->hasChild('coordinates');
	}
}
