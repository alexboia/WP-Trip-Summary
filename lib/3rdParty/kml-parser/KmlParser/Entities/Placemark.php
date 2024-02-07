<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Placemark extends Entity
{

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

	public function getStyleUrl(): string
	{
		return $this->element->getChild('styleUrl')->getValue();
	}

	public function hasStyleUrl(): bool
	{
		return $this->element->hasChild('styleUrl');
	}

	public function getStyle(): Style
	{
		return new Style($this->element->getChild('Style'));
	}

	public function hasStyle(): bool
	{
		return $this->element->hasChild('Style');
	}

	public function getPoint(): Point
	{
		return new Point($this->element->getChild('Point'));
	}

	public function hasPoint(): bool
	{
		return $this->element->hasChild('Point');
	}

	public function getExtendedData(): ExtendedData
	{
		return new ExtendedData($this->element->getChild('ExtendedData'));
	}

	public function hasExtendedData(): bool
	{
		return $this->element->hasChild('ExtendedData');
	}
}
