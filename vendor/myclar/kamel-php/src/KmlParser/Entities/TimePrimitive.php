<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {
    use KamelPhp\KmlParser\Helper\KmlDateTimeParser;

	abstract class TimePrimitive extends Entity {
		use KmlDateTimeParser;

		public function getId(): string {
			return $this->element->getAttribute('id');
		}
	
		public function hasId(): bool {
			return $this->element->hasAttribute('id');
		}
	}
}