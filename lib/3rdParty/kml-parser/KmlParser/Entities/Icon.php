<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Icon extends Entity
{

	public function getHref(): string
	{
		return $this->element->getChild('href')->getValue();
	}
}
