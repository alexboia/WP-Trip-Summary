<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Point extends Geometry {
	public function getCoordinates(): Coordinate|null {
		if (!$this->hasCoordinates()) {
			return null;
		}

		return new Coordinate($this->element->getChild('coordinates'));
	}

	public function hasCoordinates(): bool {
		return $this->element->hasChild('coordinates');
	}
}
