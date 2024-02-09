<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\KmlParser\EntityTagNames;
use StepanDalecky\XmlElement\Element;

/**
 * A basic <kml> element contains 0 or 1 Feature and 0 or 1 NetworkLinkControl
 */
class Kml extends Entity {
	public function __construct(Element $element) {
		parent::__construct($element);
	}

	public function getFolder(): Folder {
		return new Folder($this->element->getChild(EntityTagNames::Folder));
	}

	public function hasFolder(): bool {
		return $this->element->hasChild(EntityTagNames::Folder);
	}

	public function getDocument(): Document {
		return new Document($this->element->getChild(EntityTagNames::Document));
	}

	public function hasDocument(): bool {
		return $this->element->hasChild(EntityTagNames::Document);
	}
}
