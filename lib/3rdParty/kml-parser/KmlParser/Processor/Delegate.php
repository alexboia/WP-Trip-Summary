<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser {

    use StepanDalecky\KmlParser\Entities\Container;
    use StepanDalecky\KmlParser\Entities\Kml;
    use StepanDalecky\KmlParser\Entities\LineString;
    use StepanDalecky\KmlParser\Entities\Placemark;
    use StepanDalecky\KmlParser\Entities\Point;

	interface Delegate {
		function processPoint(Point $point);

		function shouldProcessPointGeometry(): bool;

		function processLineString(LineString $lineString);

		function shouldProcessLineStringGeometry(): bool;
	}	
}