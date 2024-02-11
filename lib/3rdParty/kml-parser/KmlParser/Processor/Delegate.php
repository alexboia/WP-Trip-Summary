<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Processor {

	use Exception;
	use StepanDalecky\KmlParser\Entities\LinearRing;
	use StepanDalecky\KmlParser\Entities\LineString;
	use StepanDalecky\KmlParser\Entities\MultiGeometry;
	use StepanDalecky\KmlParser\Entities\Point;
	use StepanDalecky\KmlParser\Entities\Polygon;

	interface Delegate {
		function begin(): void;

		function error(Exception $exception): bool;

		function processPoint(Point $point, FeatureMetadata $featureMetadata): void;

		function shouldProcessPointGeometry(): bool;

		function processLineString(LineString $lineString, FeatureMetadata $featureMetadata): void;

		function shouldProcessLineStringGeometry(): bool;

		function processLinearRing(LinearRing $linearRing, FeatureMetadata $featureMetadata): void;

		function shouldProcessLinearRingGeometry(): bool;

		function processPolygon(Polygon $polygon, FeatureMetadata $featureMetadata): void;

		function shouldProcessPolygonGeometry(): bool;

		function processMultiGeometry(MultiGeometry $multiGeometry, FeatureMetadata $featureMetadata): void;

		function shouldProcessMultiGeometry(): bool;

		function shouldIndividuallyProcessMultiGeometryParts(): bool;

		function end(): void;
	}	
}