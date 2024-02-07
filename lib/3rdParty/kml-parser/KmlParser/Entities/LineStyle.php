<?php
declare(strict_types=1);

namespace StepanDalecky\KmlParser\Entities;

class LineStyle extends Entity
{

	public function getColor(): string
	{
		return $this->element->getChild('color')->getValue();
	}
}
