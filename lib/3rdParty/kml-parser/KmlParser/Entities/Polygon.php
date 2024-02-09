<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\KmlParser\EntityTagNames;

class Polygon extends Geometry {
	public function getOuterBoundary(): LinearRing| null {
		if (!$this->hasOuterBoundary()) {
			return null;
		}

		$linearRingElem = $this->element
			->getChild('outerBoundaryIs')
			->getChild(EntityTagNames::LinearRing);

		return new LinearRing($linearRingElem);
	}

	public function hasOuterBoundary() {
		$hasContainer = $this->element
			->hasChild('outerBoundaryIs');

		if ($hasContainer) {
			return $this->element
				->getChild('outerBoundaryIs')
				->hasChild(EntityTagNames::LinearRing);
		} else {
			return false;
		}
	}

	/**
	 * @return LinearRing[]
	 */
	public function getInnerBoundary(): array {
		if (!$this->hasInnerBoundary()) {
			return array();
		}

		/** @var LinearRing[] $innerBoundaryLinearRings */
		$innerBoundaryLinearRings = array();
		$innerBoundaryElems = $this->element->getChildren('innerBoundaryIs');

		foreach ($innerBoundaryElems as $innerBoundaryElem) {
			if ($innerBoundaryElem->hasChild(EntityTagNames::LinearRing)) {
				$linearRingElem = $innerBoundaryElem->getChild(EntityTagNames::LinearRing);
				$innerBoundaryLinearRings[] = new LinearRing($linearRingElem);
			}
		}

		return $innerBoundaryLinearRings;
	}

	public function hasInnerBoundary() {
		return $this->element->hasChild('innerBoundaryIs');
	}
}
