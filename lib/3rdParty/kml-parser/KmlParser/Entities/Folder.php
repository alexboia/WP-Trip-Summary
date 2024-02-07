<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\XmlElement\Element;

class Folder extends Entity
{

	public function getName(): string
	{
		return $this->element->getChild('name')->getValue();
	}

	public function hasName(): bool
	{
		return $this->element->hasChild('name');
	}

	/**
	 * @return Placemark[]
	 */
	public function getPlacemarks(): array
	{
		return array_map(function (Element $element) {
			return new Placemark($element);
		}, $this->element->getChildren('Placemark'));
	}
}
