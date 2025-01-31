<?php
declare(strict_types=1);

namespace KamelPhp\KmlParser;

use KamelPhp\KmlParser\Entities\Entity;
use KamelPhp\KmlParser\Entities\Kml;
use KamelPhp\KmlParser\Exceptions\InvalidKmlRootElementException;
use KamelPhp\XmlElement\Element;

class Parser extends Entity {
	public function getKml(): Kml {
		return new Kml($this->element);
	}

	public static function fromFile(string $file): self {
		return self::fromString(file_get_contents($file));
	}

	public static function fromString(string $string): self {
		$element = new Element(new \SimpleXMLElement($string));
		if ($element->is('kml')) {
			return new self($element);
		} else {
			throw new InvalidKmlRootElementException($element->getName());
		}
	}
}
