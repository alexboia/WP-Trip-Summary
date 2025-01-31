<?php
declare(strict_types=1);

namespace KamelPhp\KmlParser\Entities;

class SimpleField extends Entity
{

	public function getName(): string
	{
		return $this->element->getAttribute('name');
	}

	public function getType(): string
	{
		return $this->element->getAttribute('type');
	}
}
