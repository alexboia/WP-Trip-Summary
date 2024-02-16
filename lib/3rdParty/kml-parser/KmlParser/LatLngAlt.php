<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser {
	class LatLngAlt {
		const MIN_LONGITUDE = -180;

		const MAX_LONGITUDE = 180;

		const MIN_LATITUDE = -90;

		const MAX_LATITUDE = 90;

		const MIN_ALTITUDE = -PHP_FLOAT_MAX;

		const MAX_ALTITUDE = PHP_FLOAT_MAX;

		private $longitude = null;

		private $latitude = null;

		private $altitude = null;

		private $asString = '';

		public function __construct(string $asString) {
			$this->_parseCoordinates(trim($asString));
		}

		private function _parseCoordinates(string $asString): void {
			$this->asString = $asString;
			if (!empty($asString)) {
				$parts = explode(',', $asString, 3);
				if (count($parts) >= 2) {
					$longitude = floatval($parts[0]);
					$latitude = floatval($parts[1]);
					if (isset($parts[2])) {
						$altitude = floatval($parts[2]);
					} else {
						$altitude = self::MIN_ALTITUDE;
					}

					if ($this->isValidLongitude($longitude) 
						&& $this->isValidLatitude($latitude)) {
						$this->longitude = $longitude;
						$this->latitude = $latitude;

						if ($this->isValidAltitude($altitude)) {
							$this->altitude = $altitude;
						}
					}
				}
			}
		}

		private function isValidLongitude($longitude): bool {
			return !is_null($longitude) && $longitude >= self::MIN_LONGITUDE && $longitude <= self::MAX_LONGITUDE;
		}

		private function isValidLatitude($latitude): bool {
			return !is_null($latitude) && $latitude >= self::MIN_LATITUDE && $latitude <= self::MAX_LATITUDE;
		}

		private function isValidAltitude($altitude): bool {
			return !is_null($altitude) && $altitude > self::MIN_ALTITUDE && $altitude < self::MAX_ALTITUDE;
		}

		public function getLatitude(): float|null {
			return $this->hasLatitude() ? $this->latitude : null;
		}

		public function hasLatitude(): bool {
			return $this->isValidLatitude($this->latitude);
		}

		public function getLongitude(): float|null {
			return $this->hasLongitude() ? $this->longitude : null;
		}

		public function hasLongitude(): bool {
			return $this->isValidLongitude($this->longitude);
		}

		public function getAltitude(): float|null {
			return $this->hasAltitude() ? $this->altitude : null;
		}

		public function hasAltitude(): bool {
			return $this->isValidAltitude($this->altitude);
		}

		public function getOriginalString(): string {
			return $this->asString;
		}

		public function isEmpty(): bool {
			return !$this->hasLongitude() && !$this->hasLatitude();
		}
	}
}