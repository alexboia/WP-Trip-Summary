<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Exceptions {

    use InvalidArgumentException;

	class InvalidKmlRootElementException extends InvalidArgumentException {
		public function __construct($actualElement) {
			parent::__construct('Expected <kml> root element but found <' . $actualElement . '>', 100);
		}
	}
}