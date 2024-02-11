<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Processor {

    use StepanDalecky\KmlParser\Entities\Placemark;

	class FeatureMetadata {
		private $_name;

		private $_description;

		private $_keywords;

		public function __construct($name, $description, $keywords) {
			$this->_name = trim($name);
			$this->_description = trim($description);
			$this->_keywords = trim($keywords);
		}

		public static function fromPlacemark(Placemark $placemark): FeatureMetadata {
			return new self(
				$placemark->hasName() ? $placemark->getName() : '', 
				$placemark->hasDescription() ? $placemark->getDescription() : '', 
				''
			);
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
	}
}