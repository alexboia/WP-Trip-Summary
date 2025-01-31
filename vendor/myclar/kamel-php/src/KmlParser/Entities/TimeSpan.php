<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {
    use DateTime;

	class TimeSpan extends TimePrimitive {

		public function getBegin(): string|null {
			if (!$this->hasBegin()) {
				return null;
			}

			return $this->element->getChild('begin')->getValue();
		}

		public function getBeginAsDateTime($format = null): DateTime|null {
			if ($this->hasBegin()) {
				$beginString = $this->getBegin();
				return $this->_parseKmlDate($beginString, $format, null);
			} else {
				return null;
			}
		}

		public function hasBegin(): bool {
			return $this->element->hasChild('begin');
		}

		public function getEnd(): string|null {
			if (!$this->hasEnd()) {
				return null;
			}
			
			return $this->element->getChild('end')->getValue();
		}

		public function getEndAsDateTime($format = null): DateTime {
			if ($this->hasEnd()) {
				$endString = $this->getEnd();
				return $this->_parseKmlDate($endString, $format, null);
			} else {
				return null;
			}
		}

		public function hasEnd(): bool {
			return $this->element->hasChild('end');
		}
	}
}