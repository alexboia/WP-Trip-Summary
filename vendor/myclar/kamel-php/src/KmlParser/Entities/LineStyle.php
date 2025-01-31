<?php
declare(strict_types=1);

namespace KamelPhp\KmlParser\Entities;

class LineStyle extends Entity {
	public function getColor(): string {
		return $this->element->getChild('color')->getValue();
	}
}
