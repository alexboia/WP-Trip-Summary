<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class LabelStyle extends Entity
{

	public function getScale(): string
	{
		return $this->element->getChild('scale')->getValue();
	}
}
