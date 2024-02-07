<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Point extends Entity
{

	public function getCoordinates(): string
	{
		return $this->element->getChild('coordinates')->getValue();
	}
}
