<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\XmlElement\Element;

class StyleMap extends Entity
{

	public function getId(): string
	{
		return $this->element->getAttribute('id');
	}

	public function hasId(): bool
	{
		return $this->element->hasAttribute('id');
	}

	/**
	 * @return Pair[]
	 */
	public function getPairs(): array
	{
		return array_map(function (Element $element) {
			return new Pair($element);
		}, $this->element->getChildren('Pair'));
	}
}
