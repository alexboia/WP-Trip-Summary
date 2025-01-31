<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {
	class Snippet extends Entity {

		public function getMaxLines(int $default = 0): int {
			if (!$this->hasMaxLines()) {
				return $default;
			}

			return intval($this->element->getAttribute('maxLines'));
		}

		public function hasMaxLines(): bool {
			return $this->element->hasAttribute('maxLines');
		}

		public function getText(): string|null {
			return trim(strip_tags($this->element->getValue()));
		}
	}
}