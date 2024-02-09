<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\KmlParser\EntityTagNames;

class Placemark extends Feature {

	public function getStyle(): Style|null {
		if (!$this->hasStyle()) {
			return null;
		}

		return new Style($this->element->getChild(EntityTagNames::Style));
	}

	public function hasStyle(): bool {
		return $this->element->hasChild(EntityTagNames::Style);
	}

	public function getPoint(): Point|null {
		if (!$this->hasPoint()) {
			return null;
		}

		return new Point($this->element->getChild(EntityTagNames::Point));
	}

	public function hasPoint(): bool {
		return $this->element->hasChild(EntityTagNames::Point);
	}

	public function getExtendedData(): ExtendedData|null {
		if (!$this->hasExtendedData()) {
			return null;
		}

		return new ExtendedData($this->element->getChild(EntityTagNames::ExtendedData));
	}

	public function hasExtendedData(): bool {
		return $this->element->hasChild(EntityTagNames::ExtendedData);
	}

	public function getLineString(): LineString|null {
		if (!$this->hasLineString()) {
			return null;
		}

		return new LineString($this->element->getChild(EntityTagNames::LineString));
	}

	public function hasLineString(): bool {
		return $this->element->hasChild(EntityTagNames::LineString);
	}

	public function getLinearRing(): LinearRing|null {
		if (!$this->hasLinearRing()) {
			return null;
		}

		return new LinearRing($this->element->getChild(EntityTagNames::LinearRing));
	}

	public function hasLinearRing(): bool {
		return $this->element->hasChild(EntityTagNames::LinearRing);
	}

	public function getPolygon(): Polygon|null {
		if (!$this->hasPolygon()) {
			return null;
		}

		return new Polygon($this->element->getChild(EntityTagNames::Polygon));
	}

	public function hasPolygon(): bool {
		return $this->element->hasChild(EntityTagNames::Polygon);
	}

	public function getMultiGeometry(): MultiGeometry|null {
		if (!$this->hasMultiGeometry()) {
			return null;
		}

		return new MultiGeometry($this->element->getChild(EntityTagNames::MultiGeometry));
	}

	public function hasMultiGeometry(): bool {
		return $this->element->hasChild(EntityTagNames::MultiGeometry);
	}
}
