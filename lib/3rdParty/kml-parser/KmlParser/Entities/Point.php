<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Point extends Geometry {
	public function getCoordinate(): Coordinate|null {
		if (!$this->hasCoordinate()) {
			return null;
		}

		return new Coordinate($this->element->getChild('coordinates'));
	}

	public function hasCoordinate(): bool {
		return $this->element->hasChild('coordinates');
	}
}
