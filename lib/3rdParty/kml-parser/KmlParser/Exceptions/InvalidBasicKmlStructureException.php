<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Exceptions {

    use InvalidArgumentException;

	class InvalidBasicKmlStructureException extends InvalidArgumentException {
		public function __construct() {
			parent::__construct('Root <kml> element has invalid structure.', 110);
		}
	}
}