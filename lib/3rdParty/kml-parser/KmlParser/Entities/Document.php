<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\XmlElement\Element;

class Document extends Entity
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
		return $this->element->getChild('name')->getValue();
	}

	public function hasName(): bool
	{
		return $this->element->hasChild('name');
	}

	public function getDescription(): string
	{
		return $this->element->getChild('description')->getValue();
	}

	public function hasDescription(): bool
	{
		return $this->element->hasChild('description');
	}

	/**
	 * @return Style[]
	 */
	public function getStyles(): array
	{
		return array_map(function (Element $element) {
			return new Style($element);
		}, $this->element->getChildren('Style'));
	}

	public function getStyleMap(): StyleMap
	{
		return new StyleMap($this->element->getChild('StyleMap'));
	}

	public function hasStyleMap(): bool
	{
		return $this->element->hasChild('StyleMap');
	}

	public function getSchema(): Schema
	{
		return new Schema($this->element->getChild('Schema'));
	}

	public function hasSchema(): bool
	{
		return $this->element->hasChild('Schema');
	}

	/**
	 * @return Folder[]
	 */
	public function getFolders(): array
	{
		return array_map(function (Element $element) {
			return new Folder($element);
		}, $this->element->getChildren('Folder'));
	}
}
