<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities {
	use StepanDalecky\XmlElement\Element;

	class Geometry extends Entity {
		const TagCoordinates = 'coordinates';

		public function __construct(Element $element) {
			parent::__construct($element);
		}
	
		public function getId(): string {
			return $this->element->getAttribute('id');
		}
	
		public function hasId(): bool {
			return $this->element->hasAttribute('id');
		}
	}
}
