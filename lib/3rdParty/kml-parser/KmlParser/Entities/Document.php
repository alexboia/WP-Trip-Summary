<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\KmlParser\EntityTagNames;
use StepanDalecky\XmlElement\Element;

class Document extends Container {
	/**
	 * @return Style[]
	 */
	public function getStyles(): array {
		if (!$this->hasStyles()) {
			return array();
		}

		return array_map(function (Element $element) {
			return new Style($element);
		}, $this->element->getChildren(EntityTagNames::Style));
	}

	public function hasStyles(): bool {
		return $this->element->hasChild(EntityTagNames::Style);
	}

	public function getStyleMap(): StyleMap {
		return new StyleMap($this->element->getChild(EntityTagNames::StyleMap));
	}

	public function hasStyleMap(): bool {
		return $this->element->hasChild(EntityTagNames::StyleMap);
	}

	public function getSchema(): Schema {
		return new Schema($this->element->getChild(EntityTagNames::Schema));
	}

	public function hasSchema(): bool {
		return $this->element->hasChild(EntityTagNames::Schema);
	}
}
