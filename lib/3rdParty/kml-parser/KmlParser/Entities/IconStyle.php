<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class IconStyle extends Entity
{

	public function getColor(): string
	{
		return $this->element->getChild('color')->getValue();
	}

	public function getScale(): string
	{
		return $this->element->getChild('scale')->getValue();
	}

	public function getIcon(): Icon
	{
		return new Icon($this->element->getChild('Icon'));
	}

	public function getHotSpot(): HotSpot
	{
		return new HotSpot($this->element->getChild('hotSpot'));
	}
}
