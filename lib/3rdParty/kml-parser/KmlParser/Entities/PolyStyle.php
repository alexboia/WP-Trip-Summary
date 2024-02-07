<?php
declare(strict_types=1);

namespace StepanDalecky\KmlParser\Entities;

class PolyStyle extends Entity
{

	public function getFill(): string
	{
		return $this->element->getChild('fill')->getValue();
	}
}
