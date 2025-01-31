<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities;

class LabelStyle extends Entity
{

	public function getScale(): string
	{
		return $this->element->getChild('scale')->getValue();
	}
}
