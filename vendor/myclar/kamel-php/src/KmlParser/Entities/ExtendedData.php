<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities;

class ExtendedData extends Entity
{

	public function getSchemaData(): SchemaData
	{
		return new SchemaData($this->element->getChild('SchemaData'));
	}

	public function hasSchemaData(): bool
	{
		return $this->element->hasChild('SchemaData');
	}
}
