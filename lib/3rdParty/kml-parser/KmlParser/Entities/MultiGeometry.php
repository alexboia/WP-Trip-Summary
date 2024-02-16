<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities {

    use StepanDalecky\KmlParser\EntityTagNames;
    use StepanDalecky\XmlElement\Element;

	class MultiGeometry extends Geometry {
		public function __construct(Element $element) {
			parent::__construct($element);
		}

		/**
		 * @return Point[]
		 */
		public function getPoints(): array {
			if (!$this->hasPoints()) {
				return array();
			}

			return array_map(function (Element $element) {
				return new Point($element);
			}, $this->element->getChildren(EntityTagNames::Point));
		}

		public function hasPoints(): bool {
			return $this->element->hasChildren(EntityTagNames::Point);
		}

		/**
		 * @return LineString[]
		 */
		public function getLineStrings(): array {
			if (!$this->hasLineStrings()) {
				return array();
			}

			return array_map(function (Element $element) {
				return new LineString($element);
			}, $this->element->getChildren(EntityTagNames::LineString));
		}
	
		public function hasLineStrings(): bool {
			return $this->element->hasChildren(EntityTagNames::LineString);
		}

		/**
		 * @return LinearRing[]
		 */
		public function getLinearRings(): array {
			if (!$this->hasLinearRings()) {
				return array();
			}

			return array_map(function (Element $element) {
				return new LinearRing($element);
			}, $this->element->getChildren(EntityTagNames::LinearRing));
		}

		public function hasLinearRings(): bool {
			return $this->element->hasChildren(EntityTagNames::LinearRing);
		}

		/**
		 * @return Polygon[]
		 */
		public function getPolygons(): array {
			if (!$this->hasPolygons()) {
				return array();
			}

			return array_map(function (Element $element) {
				return new Polygon($element);
			}, $this->element->getChildren(EntityTagNames::Polygon));
		}

		public function hasPolygons(): bool {
			return $this->element->hasChildren(EntityTagNames::Polygon);
		}

		/**
		 * @return MultiGeometry[]
		 */
		public function getMultiGeometries(): array {
			if (!$this->hasMultiGeometries()) {
				return array();
			}

			return array_map(function (Element $element) {
				return new MultiGeometry($element);
			}, $this->element->getChildren(EntityTagNames::MultiGeometry));
		}

		public function hasMultiGeometries(): bool {
			return $this->element->hasChildren(EntityTagNames::MultiGeometry);
		}
	}
}
