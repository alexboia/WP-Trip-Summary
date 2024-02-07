<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Style extends Entity
{

	public function getId(): string
	{
		return $this->element->getAttribute('id');
	}

	public function hasId(): bool
	{
		return $this->element->hasAttribute('id');
	}

	public function getIconStyle(): IconStyle
	{
		return new IconStyle($this->element->getChild('IconStyle'));
	}

	public function hasIconStyle(): bool
	{
		return $this->element->hasChild('IconStyle');
	}

	public function getLabelStyle(): LabelStyle
	{
		return new LabelStyle($this->element->getChild('LabelStyle'));
	}

	public function hasLabelStyle(): bool
	{
		return $this->element->hasChild('LabelStyle');
	}

	public function getLineStyle(): LineStyle
	{
		return new LineStyle($this->element->getChild('LineStyle'));
	}

	public function hasLineStyle(): bool
	{
		return $this->element->hasChild('LineStyle');
	}

	public function getPolyStyle(): PolyStyle
	{
		return new PolyStyle($this->element->getChild('PolyStyle'));
	}

	public function hasPolyStyle(): bool
	{
		return $this->element->hasChild('PolyStyle');
	}
}
