<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser {

    use ArrayAccess;
    use Countable;
    use Iterator;

	class LatLngAltCollection implements ArrayAccess, Iterator, Countable {
		private $coords = array();

		private $asString;

		private $position = 0;

		public function __construct(string $asString) {
			$this->_parseCoordinatesCollection($asString);
		}

		public function count(): int { 
			return count($this->coords);
		}

		public static function empty(): LatLngAltCollection {
			return new self('');
		}

		private function _parseCoordinatesCollection(string $asString): void {
			$this->asString = $asString;
			if (!empty($asString)) {
				$parts = preg_split('/\\s/', $asString, -1, PREG_SPLIT_NO_EMPTY);
				foreach ($parts as $part) {
					if (empty($part)) {
						continue;
					}

					$latLngAlt = new LatLngAlt($part);
					if ($latLngAlt->hasLatitude() && $latLngAlt->hasLongitude()) {
						$this->coords[] = $latLngAlt;
					}
				}
			}
		}

		/**
		 * @return LatLngAlt
		 */
		public function current(): mixed { 
			return $this->coords[$this->position];
		}

		public function next(): void { 
			$this->position += 1;
		}

		public function key(): mixed { 
			return $this->position;
		}

		public function valid(): bool { 
			return isset($this->coords[$this->position]);
		}

		public function rewind(): void { 
			$this->position = 0;
		}

		public function offsetExists(mixed $offset): bool { 
			return isset($this->coords[$this->_prepOffset($offset)]);
		}

		private function _prepOffset(mixed $offset): int {
			return max(0, intval($offset));
		}

		/**
		 * @return LatLngAlt
		 */
		public function offsetGet(mixed $offset): mixed { 
			if ($this->offsetExists($offset)) {
				return $this->coords[$this->_prepOffset($offset)];
			} else {
				return null;
			}
		}

		public function offsetSet(mixed $offset, mixed $value): void { 
			if ($value instanceof LatLngAlt) {
				$this->coords[$this->_prepOffset($offset)] = $value;
			}
		}

		public function offsetUnset(mixed $offset): void { 
			if ($this->offsetExists($offset)) {
				unset($this->coords[$this->_prepOffset($offset)]);
			}
		}

		/**
		 * @return LatLngAlt[]
		 */
		public function getCoords(): array {
			return $this->coords;
		}

		public function getOriginalString(): string {
			return $this->asString;
		}
	}
}