<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

class Kml extends Entity
{

	public function getDocument(): Document
	{
		return new Document($this->element->getChild('Document'));
	}
}
