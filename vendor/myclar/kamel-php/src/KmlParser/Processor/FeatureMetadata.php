<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Processor {

    use KamelPhp\KmlParser\Entities\Placemark;
    use KamelPhp\KmlParser\Entities\TimeSpan;
    use KamelPhp\KmlParser\Entities\TimeStamp;

	class FeatureMetadata {
		private $_name;

		private $_description;

		private $_keywords;

		/**
		 * @var TimeSpan|null
		 */
		private $_timeSpan;

		/**
		 * @var TimeStamp|null
		 */
		private $_timeStamp;

		public function __construct($name, $description, $keywords) {
			$this->_name = trim($name);
			$this->_description = trim($description);
			$this->_keywords = trim($keywords);
		}

		public static function fromPlacemark(Placemark $placemark): FeatureMetadata {
			$metadata = new self(
				$placemark->hasName() ? $placemark->getName() : '', 
				$placemark->hasDescription() ? $placemark->getDescription() : '', 
				''
			);

			if ($placemark->hasTimeSpan()) {
				$metadata->_timeSpan = $placemark->getTimeSpan();
			}

			if ($placemark->hasTimeStamp()) {
				$metadata->_timeStamp = $placemark->getTimeStamp();
			}

			return $metadata;
		}

		public function getName(): string|null {
			return $this->_name;
		}

		public function hasName(): bool {
			return !empty($this->_name);
		}

		public function getDescription(): string|null {
			return $this->_description;
		}

		public function hasDescription(): bool {
			return !empty($this->_description);
		}

		public function getKeywords(): string|null {
			return $this->_keywords;
		}

		public function hasKeywords(): bool {
			return !empty($this->_keywords);
		}

		public function getTimeSpan(): TimeSpan|null {
			return $this->_timeSpan;
		}

		public function hasTimeSpan(): bool {
			return $this->_timeSpan != null;
		}

		public function getTimeStamp(): TimeStamp|null {
			return $this->_timeStamp;
		}

		public function hasTimeStamp(): bool {
			return $this->_timeStamp != null;
		}
	}
}