<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities;

class Icon extends Entity
{

	public function getHref(): string
	{
		return $this->element->getChild('href')->getValue();
	}
}
