<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {
    use DateTime;

	class TimeStamp extends TimePrimitive {
		public function getWhen(): string|null {
			if (!$this->hasWhen()) {
				return null;
			}

			return $this->element->getChild('when')->getValue();
		}

		public function getWhenAsDateTime($format = null): DateTime|null {
			if ($this->hasWhen()) {
				$whenString = $this->getWhen();
				return $this->_parseKmlDate($whenString, $format, null);
			} else {
				return null;
			}
		}

		public function hasWhen(): bool {
			return $this->element->hasChild('when');
		}
	}
}