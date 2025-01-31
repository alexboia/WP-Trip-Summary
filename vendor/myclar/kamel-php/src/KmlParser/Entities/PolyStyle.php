<?php
declare(strict_types=1);

namespace KamelPhp\KmlParser\Entities;

class PolyStyle extends Entity
{

	public function getFill(): string
	{
		return $this->element->getChild('fill')->getValue();
	}
}
