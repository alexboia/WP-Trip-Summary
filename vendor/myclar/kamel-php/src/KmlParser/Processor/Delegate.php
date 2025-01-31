<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Processor {

	use Exception;
	use KamelPhp\KmlParser\Entities\LinearRing;
	use KamelPhp\KmlParser\Entities\LineString;
	use KamelPhp\KmlParser\Entities\MultiGeometry;
	use KamelPhp\KmlParser\Entities\Point;
	use KamelPhp\KmlParser\Entities\Polygon;

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