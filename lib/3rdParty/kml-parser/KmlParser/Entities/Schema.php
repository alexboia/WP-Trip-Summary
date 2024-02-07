<?php
declare(strict_types=1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\XmlElement\Element;

class Schema extends Entity
{

	public function getId(): string
	{
		return $this->element->getAttribute('id');
	}

	public function hasId(): bool
	{
		return $this->element->hasAttribute('id');
	}

	public function getName(): string
	{
		return $this->element->getAttribute('name');
	}

	/**
	 * @return SimpleField[]
	 */
	public function getSimpleFields(): array
	{
		return array_map(function (Element $element) {
			return new SimpleField($element);
		}, $this->element->getChildren('SimpleField'));
	}
}
