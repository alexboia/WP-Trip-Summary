<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Pair extends Entity
{

	public function getKey(): string
	{
		return $this->element->getChild('key')->getValue();
	}

	public function getStyleUrl(): string
	{
		return $this->element->getChild('styleUrl')->getValue();
	}
}
