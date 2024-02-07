<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class HotSpot extends Entity
{

	public function getX(): string
	{
		return $this->element->getAttribute('x');
	}

	public function getXunits(): string
	{
		return $this->element->getAttribute('xunits');
	}

	public function getY(): string
	{
		return $this->element->getAttribute('y');
	}

	public function getYunits(): string
	{
		return $this->element->getAttribute('yunits');
	}
}
