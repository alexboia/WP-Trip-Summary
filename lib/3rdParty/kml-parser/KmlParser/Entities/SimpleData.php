<?php
declare(strict_types=1);

namespace StepanDalecky\KmlParser\Entities;

class SimpleData extends Entity
{

	public function getName(): string
	{
		return $this->element->getAttribute('name');
	}

	public function getValue(): string
	{
		return $this->element->getValue();
	}
}
